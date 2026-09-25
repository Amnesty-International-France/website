<?php

class Sync_Command
{
    public function import_users()
    {
        $response = get_salesforce_users();
        insert_users_records($response);
        while (isset($response['nextRecordsUrl'])) {
            $response = get_salesforce_users_query(substr($response['nextRecordsUrl'], 1));
            insert_users_records($response);
        }
    }

    public function import_signatures()
    {
        $petition_ids = new WP_Query([
            'post_type' => 'petition',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);

        foreach ($petition_ids->posts as $post_id) {
            $end_date = get_field('date_de_fin', $post_id);

            if (isset($end_date) && (strtotime(date('Y-m-d')) <= strtotime($end_date))) {
                $uidsf = get_field('uidsf', $post_id);

                $encoded_query = urlencode(sprintf(
                    "SELECT Ext_ID_WP__c, Petition__r.Ext_ID_Petition__c, Email__c, Date_signature_petition__c, Code_Marketing_Prestataire__c, Message__c FROM Signature_de_petition__c WHERE Petition__r.Ext_ID_Petition__c = '%s'",
                    esc_sql($uidsf)
                ));

                $response = get_salesforce_data("services/data/v57.0/query/?q={$encoded_query}");

                if (! $response) {
                    continue;
                }

                foreach ($response['records'] as ['Ext_ID_WP__c' => $petition_id, 'Petition__r' => $petition_r, 'Email__c' => $email, 'Date_signature_petition__c' => $date, 'Code_Marketing_Prestataire__c' => $code_origine, 'Message__c' => $message]) {
                    if (empty($petition_id)) {
                        $petition_id = get_petition_id_from_uidsf($petition_r['Ext_ID_Petition__c']);
                    }

                    $user = get_local_user($email);
                    if (! $user) {
                        continue;
                    }

                    if (have_signed($petition_id, $user->id)) {
                        continue;
                    }

                    insert_petition_signature($petition_id, $user->id, $date, $code_origine, $message, 0, 1, date('Y-m-d'));
                }
            }
        }
    }

    public function compteurs()
    {
        $petition_ids = new WP_Query([
            'post_type' => 'petition',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);

        foreach ($petition_ids->posts as $post_id) {
            $uidsf = get_field('uidsf', $post_id);
            if (! $uidsf) {
                continue;
            }

            $petition = get_salesforce_petition_counter($uidsf);
            if (! $petition || $petition['totalSize'] === 0) {
                continue;
            }

            $signatures = $petition['records'][0]['Nb_signatures_total__c'];
            update_field('_amnesty_signature_count', $signatures, $post_id);
        }
    }

    public function signatures()
    {
        $signatures_to_sync = get_signatures_to_sync();
        if (empty($signatures_to_sync)) {
            WP_CLI::error('No signatures to sync');
            return;
        }

        sync_signatures_to_salesforce($signatures_to_sync);
    }

    public function signatures_failed()
    {
        $signatures_to_sync = get_failed_signatures_to_sync();
        if (empty($signatures_to_sync)) {
            WP_CLI::error('No failed signatures to sync');
            return;
        }

        sync_signatures_to_salesforce($signatures_to_sync);
    }

    /**
     * Creates in Salesforce the published petitions without Salesforce ID.
     *
     * Catches up on the scheduled petitions published by WP-Cron before
     * create_petition() was hooked on publish_future_post.
     *
     * ## OPTIONS
     *
     * [<id>...]
     * : Only these petitions.
     *
     * [--since=<date>]
     * : Only the petitions published on or after this date (YYYY-MM-DD).
     *
     * [--dry-run]
     * : List the petitions without creating them in Salesforce.
     *
     * ## EXAMPLES
     *
     *     wp sync create_missing_petitions --since=2026-07-01 --dry-run
     *     wp sync create_missing_petitions 159460
     */
    public function create_missing_petitions(array $args, array $assoc_args)
    {
        $since = $assoc_args['since'] ?? '';
        $dry_run = ! empty($assoc_args['dry-run']);

        // Petitions imported from Prismic may have no Salesforce ID on purpose,
        // so never target every published petition.
        if (empty($args) && $since === '') {
            WP_CLI::error('Pass petition IDs or --since=<date>');
            return;
        }

        // WP_Date_Query ignores an invalid date, which would widen the scope to
        // every petition.
        $since_date = DateTime::createFromFormat('Y-m-d', $since);
        if ($since !== '' && (! $since_date || $since_date->format('Y-m-d') !== $since)) {
            WP_CLI::error("Invalid --since date, expected YYYY-MM-DD: {$since}");
            return;
        }

        $query = [
            'post_type' => 'petition',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post__in' => array_map('absint', $args),
            'meta_query' => [
                'relation' => 'OR',
                ['key' => 'uidsf', 'compare' => 'NOT EXISTS'],
                ['key' => 'uidsf', 'value' => ''],
            ],
        ];

        if ($since !== '') {
            $query['date_query'] = [['after' => $since, 'inclusive' => true]];
        }

        $post_ids = get_posts($query);

        if (empty($post_ids)) {
            WP_CLI::success('No published petition without Salesforce ID');
            return;
        }

        $failures = 0;

        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            $label = "#{$post_id} \"{$post->post_title}\" (published {$post->post_date})";

            // The record was created but its Ext ID couldn't be read back:
            // creating the petition again would duplicate it in Salesforce.
            $sfid = get_field('sfid', $post_id);
            if ($sfid) {
                WP_CLI::warning("{$label}: skipped, Salesforce record {$sfid} exists but its Ext ID is missing");
                $failures++;
                continue;
            }

            if ($dry_run) {
                WP_CLI::log("{$label}: to create");
                continue;
            }

            create_petition($post_id);

            $uidsf = get_field('uidsf', $post_id);
            if (! $uidsf) {
                WP_CLI::warning("{$label}: could not be linked to Salesforce");
                $failures++;
                continue;
            }

            WP_CLI::log("{$label}: created, Ext ID {$uidsf}, code origine " . get_field('code_origine', $post_id));
        }

        if ($failures > 0) {
            WP_CLI::error("{$failures} petition(s) need a manual check in Salesforce");
            return;
        }

        $count = count($post_ids);
        WP_CLI::success($dry_run ? "{$count} petition(s) to create" : "{$count} petition(s) created");
    }
}

function insert_users_records($response)
{
    if (isset($response['records'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aif_users';
        foreach ($response['records'] as $record) {
            $civility = $record['Salutation'] ?? '';
            $first_name = $record['FirstName'] ?? '';
            $last_name = $record['LastName'] ?? '';
            $email = $record['Email'] ?? '';
            $code_postal = $record['Code_Postal__c'] ?? '';
            $pays = $record['Pays__c'] ?? '';
            $mobile_phone = $record['MobilePhone'] ?? '';
            $data = [
                'firstname' => $first_name,
                'lastname' => $last_name,
                'email' => $email,
                'civility' => $civility,
                'country' => $pays,
                'postal_code' => $code_postal,
                'phone' => $mobile_phone,
            ];
            $format = ['%s', '%s', '%s', '%s', '%s', '%s', '%s'];

            $existing_user_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $table_name WHERE email = %s",
                    $email
                )
            );

            if ($existing_user_id) {
                $where = ['email' => $email];
                $where_format = ['%s'];
                unset($data['email']);
                unset($format[count($format) - 1]);

                $wpdb->update($table_name, $data, $where, $format, $where_format);
            } else {
                $wpdb->insert($table_name, $data, $format);
            }
        }
    }
}

function get_petition_id_from_uidsf($uidsf)
{
    $args = [
        'post_type' => 'petition',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => 'uidsf',
                'value' => $uidsf,
                'compare' => '=',
            ],
        ],
    ];

    $posts_found = get_posts($args);

    if (! empty($posts_found)) {
        return $posts_found[0];
    }

    return null;
}

WP_CLI::add_command('sync', new Sync_Command());
