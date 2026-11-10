<?php

declare(strict_types=1);

/**
 * Stand-ins shared by the tests of the donor space pages: the Salesforce
 * member of the request, the Salesforce response checks, which let the
 * stubbed data through, and render_block(), whose patterns these tests leave
 * out.
 */

if (!function_exists('aif_get_request_salesforce_member')) {
    function aif_get_request_salesforce_member(): object
    {
        return (object) ['Id' => '003000000000001', 'isMembre' => true, 'isDonateur' => false, 'hasMandatActif' => false];
    }
}

if (!function_exists('aif_require_salesforce_array')) {
    function aif_require_salesforce_array(mixed $result, string $endpoint): mixed
    {
        return $result;
    }
}

if (!function_exists('aif_require_salesforce_object')) {
    function aif_require_salesforce_object(mixed $result, string $endpoint, ?string $required_property = null): mixed
    {
        return $result;
    }
}

if (!function_exists('aif_require_salesforce_records')) {
    function aif_require_salesforce_records(mixed $result, string $endpoint): mixed
    {
        return $result;
    }
}

if (!function_exists('render_block')) {
    function render_block(array $block): string
    {
        return '';
    }
}
