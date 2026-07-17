<?php

declare(strict_types=1);

// Single shared fake for get_local_user()/update_signature_status(), required
// by every test file that fakes the real petitions/tables.php implementation
// (tests/Salesforce/SalesforcePetitionBulkCsvTest.php,
// tests/SalesforceSync/SyncSignaturesToSalesforceTest.php). One shared file
// (rather than each consumer declaring its own copy) avoids two different
// inline copies silently reading/writing each other's globals when both
// testsuites load in the same process.
//
// Not wrapped in function_exists(): tables.php declares these unconditionally,
// so guarding this file would let tables.php's real functions load silently
// (backed by a live $wpdb this suite never seeds) instead of the loud
// "cannot redeclare" fatal we want when Petitions and this file collide.
// Also not in tests/bootstrap.php, since that's always loaded and would
// permanently shadow the real implementation PetitionsTablesTest.php tests.

$updated_signature_statuses = [];
$local_users = [];

function update_signature_status($petition_id, $user_id, $pending, $is_synched, $last_sync, $increment_nb_try = false): void
{
    global $updated_signature_statuses;

    $updated_signature_statuses[] = compact('petition_id', 'user_id', 'pending', 'is_synched', 'last_sync', 'increment_nb_try');
}

function get_local_user(string $email): object
{
    global $local_users;

    return (object) [
        'id' => $local_users[strtolower($email)] ?? 0,
    ];
}
