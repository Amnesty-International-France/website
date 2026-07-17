<?php

declare(strict_types=1);

// Single shared definition of get_local_user()/update_signature_status(),
// required by every test file that needs to fake the real implementation
// from petitions/tables.php (tests/Salesforce/SalesforcePetitionBulkCsvTest.php,
// tests/SalesforceSync/SyncSignaturesToSalesforceTest.php). Kept out of
// tests/bootstrap.php (always loaded for every suite) because
// tests/Petitions/PetitionsTablesTest.php requires the REAL implementation
// directly, and a shared bootstrap stub would permanently shadow it there.
//
// A single shared definition matters here, as opposed to each consumer
// declaring its own function_exists()-guarded copy: PHP only keeps the first
// declaration loaded in a given process, so two *different* inline copies
// silently produce wrong results once both testsuites happen to load in the
// same process (the second file's guard sees "already defined" and silently
// reuses the first file's backing globals instead of its own - no fatal, a
// silently wrong test result). Requiring this one file from every consumer
// keeps them consistent regardless of load order.

$updated_signature_statuses = [];
$local_users = [];

if (!function_exists('update_signature_status')) {
    function update_signature_status($petition_id, $user_id, $pending, $is_synched, $last_sync, $increment_nb_try = false): void
    {
        global $updated_signature_statuses;

        $updated_signature_statuses[] = compact('petition_id', 'user_id', 'pending', 'is_synched', 'last_sync', 'increment_nb_try');
    }
}

if (!function_exists('get_local_user')) {
    function get_local_user(string $email): object
    {
        global $local_users;

        return (object) [
            'id' => $local_users[strtolower($email)] ?? 0,
        ];
    }
}
