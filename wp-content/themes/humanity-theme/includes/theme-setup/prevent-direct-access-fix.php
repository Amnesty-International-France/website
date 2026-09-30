<?php

declare(strict_types=1);
/**
 * Workaround for the Prevent Direct Access plugin (`prevent-direct-access`).
 *
 * On every page load the plugin strictly compares the `updated_htaccess_success` option
 * against `true`, but the option is stored as `'1'`. The rewrite rules are therefore
 * regenerated on every request, which is most noticeable in the admin.
 *
 * Remove once the plugin stops comparing the option strictly.
 * Upstream reference: https://plugins.trac.wordpress.org/browser/prevent-direct-access/tags/2.8.9.1/prevent-direct-access.php#L293
 *
 * @package Amnesty\ThemeSetup
 */
add_filter('option_updated_htaccess_success', static fn ($value) => wp_validate_boolean($value));
