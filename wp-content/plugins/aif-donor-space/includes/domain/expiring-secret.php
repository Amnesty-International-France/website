<?php

/*
 * Single-use secrets e-mailed to a user (verification code, set-password
 * token): only a hash is stored, with an expiry, under "<meta_key>_hash"
 * and "<meta_key>_expires". The hash includes the account's current e-mail,
 * so a proof sent to one address cannot verify a later address.
 */

function aif_hash_expiring_secret($user_id, $secret)
{
    $user = get_userdata($user_id);

    if (!$user) {
        return false;
    }

    return hash('sha256', strtolower($user->user_email) . "\0" . $secret);
}

function aif_store_expiring_secret($user_id, $meta_key, $secret, $ttl)
{
    $hash = aif_hash_expiring_secret($user_id, (string) $secret);

    if (false === $hash) {
        aif_delete_expiring_secret($user_id, $meta_key);

        return;
    }

    // Legacy plaintext value, without expiry.
    delete_user_meta($user_id, $meta_key);
    update_user_meta($user_id, $meta_key . '_hash', $hash);
    update_user_meta($user_id, $meta_key . '_expires', time() + $ttl);
}

function aif_is_expiring_secret_valid($user_id, $meta_key, $secret)
{
    if (!is_string($secret) || $secret === '') {
        return false;
    }

    $stored_hash = get_user_meta($user_id, $meta_key . '_hash', true);
    $expires = (int) get_user_meta($user_id, $meta_key . '_expires', true);

    if (!is_string($stored_hash) || $stored_hash === '' || $expires < time()) {
        return false;
    }

    $current_hash = aif_hash_expiring_secret($user_id, $secret);

    return false !== $current_hash && hash_equals($stored_hash, $current_hash);
}

function aif_delete_expiring_secret($user_id, $meta_key)
{
    delete_user_meta($user_id, $meta_key);
    delete_user_meta($user_id, $meta_key . '_hash');
    delete_user_meta($user_id, $meta_key . '_expires');
}
