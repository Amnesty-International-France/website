<?php

declare(strict_types=1);

/**
 * Input checks of the "Changez leur histoire" (CLH) page settings declared in
 * includes/post-types/clh.php.
 */

require_once dirname(__DIR__) . '/post-types/clh.php';
require_once __DIR__ . '/helpers.php';

if (!function_exists('amnesty_clh_validate_end_date')) {
    /**
     * Rejects an end date earlier than the start date posted with it.
     *
     * The dates are only posted while "Temps fort" is on: ACF does not submit
     * hidden conditional fields.
     *
     * @param bool|string $valid true, or the error of an earlier check
     * @param mixed $value the posted end date, 'Y-m-d H:i:s'
     *
     * @return bool|string true, or the error to show next to the field
     */
    function amnesty_clh_validate_end_date(bool|string $valid, mixed $value): bool|string
    {
        if ($valid !== true || !is_string($value)) {
            return $valid;
        }

        // Runs inside ACF's validation of the posted form, which verifies the nonce.
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $start_date = isset($_POST['acf'][AMNESTY_CLH_START_DATE_FIELD_KEY]) && is_string($_POST['acf'][AMNESTY_CLH_START_DATE_FIELD_KEY])
            ? sanitize_text_field(wp_unslash($_POST['acf'][AMNESTY_CLH_START_DATE_FIELD_KEY]))
            : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        $end_timestamp = strtotime($value);
        $start_timestamp = strtotime($start_date);

        if ($end_timestamp === false || $start_timestamp === false) {
            return $valid;
        }

        if ($end_timestamp < $start_timestamp) {
            return __('La date de fin ne peut pas précéder la date de début.', 'amnesty');
        }

        return true;
    }
}

if (!function_exists('amnesty_clh_validate_integer_between')) {
    /**
     * Rejects a value that is not a whole number written with digits only,
     * or that falls outside the given bounds. An empty value is accepted.
     *
     * @param bool|string $valid true, or the error of an earlier check
     * @param mixed $value the posted value
     * @param int $min the lowest accepted value
     * @param int $max the highest accepted value
     *
     * @return bool|string true, or the error to show next to the field
     */
    function amnesty_clh_validate_integer_between(bool|string $valid, mixed $value, int $min, int $max): bool|string
    {
        if ($valid !== true || $value === '' || $value === null) {
            return $valid;
        }

        if (!amnesty_clh_is_digits($value)) {
            return __('Saisissez un nombre entier, sans espace ni décimale.', 'amnesty');
        }

        if ((int) $value < $min || (int) $value > $max) {
            /* translators: 1: lowest accepted value, 2: highest accepted value */
            return sprintf(__('Saisissez un nombre compris entre %1$d et %2$d.', 'amnesty'), $min, $max);
        }

        return true;
    }
}

if (!function_exists('amnesty_clh_validate_final_signatures')) {
    /**
     * Checks the final signature count: a whole number up to the cap.
     *
     * @param bool|string $valid true, or the error of an earlier check
     * @param mixed $value the posted value
     *
     * @return bool|string true, or the error to show next to the field
     */
    function amnesty_clh_validate_final_signatures(bool|string $valid, mixed $value): bool|string
    {
        return amnesty_clh_validate_integer_between($valid, $value, 0, AMNESTY_CLH_FINAL_SIGNATURES_MAX);
    }
}

if (!function_exists('amnesty_clh_validate_campaign_year')) {
    /**
     * Checks the campaign year: from the first campaign to the current year.
     *
     * @param bool|string $valid true, or the error of an earlier check
     * @param mixed $value the posted value
     *
     * @return bool|string true, or the error to show next to the field
     */
    function amnesty_clh_validate_campaign_year(bool|string $valid, mixed $value): bool|string
    {
        return amnesty_clh_validate_integer_between($valid, $value, ...amnesty_get_clh_campaign_year_range());
    }
}

add_filter('acf/validate_value/name=end_date_highlight_clh', 'amnesty_clh_validate_end_date', 10, 2);
add_filter('acf/validate_value/name=final_signatures_clh', 'amnesty_clh_validate_final_signatures', 10, 2);
add_filter('acf/validate_value/name=campaign_year_clh', 'amnesty_clh_validate_campaign_year', 10, 2);
