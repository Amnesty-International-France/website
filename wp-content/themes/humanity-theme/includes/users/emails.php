<?php

/**
 * Override the support contact address in the WordPress "password changed"
 * notification email.
 *
 * WordPress core (wp-includes/user.php) replaces ###ADMIN_EMAIL### with
 * get_option('admin_email') *after* the password_change_email filter runs.
 * We pre-replace the placeholder here so the core str_replace becomes a no-op
 * and our address is always used, regardless of the admin_email option value.
 */
add_filter(
    'password_change_email',
    static function (array $email_args): array {
        $email_args['message'] = str_replace(
            '###ADMIN_EMAIL###',
            'smd@amnesty.fr',
            $email_args['message']
        );

        return $email_args;
    }
);
