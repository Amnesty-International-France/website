<?php

/**
 * Back-office users list (AIF-443): account creation date, verified address
 * of subscribers, and a "Non vérifiés (N)" view listing unverified subscribers.
 */

const AIF_USERS_LIST_UNVERIFIED_ARG = 'aif_unverified';

/**
 * Creation date of an account, in Paris time. WordPress stores user_registered in UTC.
 */
function aif_users_list_format_registered(string $user_registered): string
{
    if ($user_registered === '' || str_starts_with($user_registered, '0000-00-00')) {
        return '';
    }

    $date = date_create_immutable($user_registered, new DateTimeZone('UTC'));
    if (!$date) {
        return '';
    }

    return $date->setTimezone(new DateTimeZone('Europe/Paris'))->format('d/m/Y H:i');
}

/**
 * "Oui" (with the verification date when known), "Non", or empty for other roles.
 */
function aif_users_list_verified_label(WP_User $user): string
{
    if (!in_array('subscriber', (array) $user->roles, true)) {
        return '';
    }

    if (!get_email_is_verified($user->ID)) {
        return 'Non';
    }

    $verified_at = (int) get_user_meta($user->ID, 'email-verified-at', true);
    if ($verified_at <= 0) {
        return 'Oui';
    }

    $date = (new DateTimeImmutable('@' . $verified_at))->setTimezone(new DateTimeZone('Europe/Paris'));

    return 'Oui, le ' . $date->format('d/m/Y H:i');
}

function aif_users_list_columns(array $columns): array
{
    $columns['aif_registered'] = 'Date de création';
    $columns['aif_email_verified'] = 'Adresse vérifiée';

    return $columns;
}
add_filter('manage_users_columns', 'aif_users_list_columns');

function aif_users_list_column_value($output, $column_name, $user_id)
{
    if ($column_name !== 'aif_registered' && $column_name !== 'aif_email_verified') {
        return $output;
    }

    $user = get_userdata((int) $user_id);
    if (!$user) {
        return $output;
    }

    if ($column_name === 'aif_registered') {
        return esc_html(aif_users_list_format_registered((string) $user->user_registered));
    }

    return esc_html(aif_users_list_verified_label($user));
}
add_filter('manage_users_custom_column', 'aif_users_list_column_value', 10, 3);

/**
 * Restrict a user query flagged with aif_unverified to subscribers whose current
 * address has no proof: same rule as get_email_is_verified() (legacy "1" or the
 * proven address, compared without case).
 */
function aif_users_list_restrict_unverified(WP_User_Query $query): void
{
    if (!$query->get(AIF_USERS_LIST_UNVERIFIED_ARG)) {
        return;
    }

    global $wpdb;

    $query->query_from .= " LEFT JOIN {$wpdb->usermeta} AS aif_ev"
        . " ON (aif_ev.user_id = {$wpdb->users}.ID AND aif_ev.meta_key = 'email-verfied')";
    $query->query_where .= " AND (aif_ev.meta_value IS NULL OR (aif_ev.meta_value <> '1'"
        . " AND LOWER(aif_ev.meta_value) <> LOWER({$wpdb->users}.user_email)))";
}
add_action('pre_user_query', 'aif_users_list_restrict_unverified');

function aif_users_list_count_unverified(): int
{
    $query = new WP_User_Query([
        'role' => 'subscriber',
        'fields' => 'ID',
        'number' => 1,
        'count_total' => true,
        AIF_USERS_LIST_UNVERIFIED_ARG => true,
    ]);

    return (int) $query->get_total();
}

function aif_users_list_is_unverified_view(): bool
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only list filter.
    return isset($_GET[AIF_USERS_LIST_UNVERIFIED_ARG]) && $_GET[AIF_USERS_LIST_UNVERIFIED_ARG] === '1';
}

function aif_users_list_views(array $views): array
{
    $current = aif_users_list_is_unverified_view();
    if ($current) {
        foreach ($views as $key => $view) {
            $views[$key] = str_replace(['class="current" aria-current="page"', 'class="current"'], '', $view);
        }
    }

    $views['aif_unverified'] = sprintf(
        '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
        esc_url(add_query_arg(AIF_USERS_LIST_UNVERIFIED_ARG, '1', admin_url('users.php'))),
        $current ? ' class="current" aria-current="page"' : '',
        esc_html('Non vérifiés'),
        aif_users_list_count_unverified()
    );

    return $views;
}
add_filter('views_users', 'aif_users_list_views');

/**
 * The users screen of the back-office lists the unverified subscribers on that view.
 */
function aif_users_list_filter_unverified(WP_User_Query $query): void
{
    if (!is_admin() || !aif_users_list_is_unverified_view()) {
        return;
    }

    global $pagenow;
    if ($pagenow !== 'users.php' || $query->get('fields') === 'ID') {
        return;
    }

    $query->set('role', 'subscriber');
    $query->set(AIF_USERS_LIST_UNVERIFIED_ARG, true);
}
add_action('pre_get_users', 'aif_users_list_filter_unverified');
