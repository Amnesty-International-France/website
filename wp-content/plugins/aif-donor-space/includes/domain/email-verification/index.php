<?php

/*
 * E-mail ownership is what ties a WordPress account to a Salesforce contact
 * (the donor space looks the contact up by user_email), so an unverified
 * subscriber must not be able to log in or keep a session.
 */

/**
 * Only self-registered donor accounts (subscribers) go through e-mail
 * verification; staff accounts are created and vouched for by admins.
 */
function aif_is_subscriber(WP_User $user)
{
    return in_array('subscriber', (array) $user->roles, true);
}

function aif_is_unverified_subscriber(WP_User $user)
{
    return aif_is_subscriber($user) && !get_email_is_verified($user->ID);
}

/**
 * Runs after core's username/password checks (priority 20), so $user is
 * already resolved; earlier errors are passed through untouched.
 */
function aif_reject_unverified_subscriber_login($user)
{
    if ($user instanceof WP_User && aif_is_unverified_subscriber($user)) {
        return new WP_Error(
            'aif_email_not_verified',
            'Votre adresse e-mail n’est pas encore vérifiée.'
        );
    }

    return $user;
}

add_filter('authenticate', 'aif_reject_unverified_subscriber_login', 30);

function aif_destroy_user_sessions($user_id)
{
    WP_Session_Tokens::get_instance($user_id)->destroy_all();
}

/**
 * Covers sessions opened before verification was enforced, on every route
 * (not only /mon-espace/).
 */
function aif_log_out_unverified_subscriber()
{
    if (!is_user_logged_in()) {
        return;
    }

    $user = wp_get_current_user();

    if (!aif_is_unverified_subscriber($user)) {
        return;
    }

    aif_destroy_user_sessions($user->ID);
    wp_clear_auth_cookie();
    wp_set_current_user(0);
}

add_action('init', 'aif_log_out_unverified_subscriber');

/**
 * Application passwords bypass both the authenticate filter and the init
 * hook (REST resolves its user later), and verification does not revoke
 * them: one created before the address was proven would outlive it.
 * Donor accounts never need the REST API, so subscribers get none.
 */
function aif_disable_application_passwords_for_subscribers($available, $user)
{
    if ($user instanceof WP_User && aif_is_subscriber($user)) {
        return false;
    }

    return $available;
}

add_filter('wp_is_application_passwords_available_for_user', 'aif_disable_application_passwords_for_subscribers', 10, 2);

/**
 * Every proof applies to one address only. Invalidate proofs issued before
 * any user_email change, including changes through REST /wp/v2/users/me.
 */
function aif_reset_email_verification_on_email_change($user_id, $old_user_data)
{
    $user = get_userdata($user_id);

    // wp_insert_user() slashes the old address before firing profile_update;
    // get_userdata() returns the new address without those slashes.
    if (!$user || 0 === strcasecmp($user->user_email, wp_unslash($old_user_data->user_email))) {
        return;
    }

    delete_user_meta($user_id, 'email-verfied');
    delete_2fa_code($user_id);
    delete_email_token($user_id);
    delete_user_meta($user_id, 'aif_native_password_reset_email');

    // Core reset links also survive an e-mail change unless their key is
    // cleared. This update fires profile_update again with the same address.
    if (!empty($user->user_activation_key)) {
        wp_update_user(['ID' => $user_id, 'user_activation_key' => '']);
    }

    if (aif_is_subscriber($user)) {
        aif_destroy_user_sessions($user_id);
    }
}

add_action('profile_update', 'aif_reset_email_verification_on_email_change', 10, 2);

/**
 * Remember the address to which WordPress sends its own reset link. Links
 * created before this check existed cannot silently verify an address.
 */
function aif_record_native_password_reset_email($user_login)
{
    $user = get_user_by('login', $user_login);

    if ($user && aif_is_subscriber($user)) {
        update_user_meta($user->ID, 'aif_native_password_reset_email', strtolower($user->user_email));
    }
}

add_action('retrieve_password_key', 'aif_record_native_password_reset_email');

function aif_verify_email_after_native_password_reset(WP_User $user)
{
    if (!aif_is_subscriber($user)) {
        return;
    }

    $sent_to = get_user_meta($user->ID, 'aif_native_password_reset_email', true);
    delete_user_meta($user->ID, 'aif_native_password_reset_email');

    // Use the latest stored address, rather than the possibly stale WP_User
    // instance passed into reset_password().
    $current_user = get_userdata($user->ID);

    if (!is_string($sent_to) || $sent_to === '' || !$current_user || 0 !== strcasecmp($sent_to, $current_user->user_email)) {
        return;
    }

    aif_destroy_user_sessions($user->ID);
    aif_verify_email_on_password_reset($current_user);
}

add_action('after_password_reset', 'aif_verify_email_after_native_password_reset');

/**
 * Sign-up never sets a password the requester knows: the account gets a
 * random one, and whoever proves ownership of the address chooses it.
 * An unverified account holding the address is reused, so a third party
 * signing up first cannot lock the real owner out.
 */
function aif_prepare_account_for_verification($email, $first_name, $last_name)
{
    $existing = get_user_by('email', $email);

    if ($existing) {
        if (!aif_is_subscriber($existing) || get_email_is_verified($existing->ID)) {
            return new WP_Error('aif_account_exists', 'Un compte vérifié existe déjà pour cette adresse.');
        }

        return $existing->ID;
    }

    return wp_insert_user([
        'user_login' => $email,
        'user_email' => $email,
        'user_pass' => wp_generate_password(32, true, true),
        'first_name' => $first_name,
        'last_name' => $last_name,
        'nickname' => trim($first_name . ' ' . $last_name),
        'role' => 'subscriber',
    ]);
}

/**
 * The account lookup by e-mail is case- and accent-insensitive in MySQL, so
 * the address a visitor typed can differ from the one the account holds:
 * the code only ever goes to the latter.
 */
function aif_send_verification_code($user_id)
{
    $user = get_userdata($user_id);

    if (!$user) {
        return false;
    }

    $code = generate_2fa_code();
    store_2fa_code($user_id, $code);

    return send_2fa_code($user->user_email, $code);
}

/**
 * The reset link is e-mailed to the stored address, so a valid reset token
 * proves ownership as much as a verification code does.
 */
function aif_verify_email_on_password_reset(WP_User $user)
{
    if (!aif_is_subscriber($user)) {
        return;
    }

    store_email_is_verified($user->ID);
    reset_login_attempts($user->ID);
    delete_2fa_code($user->ID);
}

/**
 * The account may have been created by someone else with a password they
 * know: replace it and drop every session before handing the set-password
 * step to the address owner.
 */
function aif_complete_email_verification($user_id)
{
    store_email_is_verified($user_id);
    reset_login_attempts($user_id);
    delete_2fa_code($user_id);
    wp_set_password(wp_generate_password(32, true, true), $user_id);
    aif_destroy_user_sessions($user_id);

    $token = aif_generate_random_hash();
    store_email_token($user_id, $token);

    return $token;
}

function aif_can_access_donor_space(WP_User $user)
{
    return aif_is_subscriber($user) && get_email_is_verified($user->ID);
}
