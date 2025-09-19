<?php

declare(strict_types=1);

class Sync_Register_Command
{
    public function syncs_ua_with_salesforce()
    {
        $unsyncs = get_unsynced_actions();

        if (empty($unsyncs)) {
            WP_CLI::error('[ REGISTER AU ] No new registred user to sync');
        }

        $optins = [];
        $codeModifications = [];

        foreach ($unsyncs as $item) {
            $user = get_local_user_by_id($item['user_id']);

            switch ($item['type']) {
                case 'Sms':
                    $optins['Actions_urgentes_via_le_smartphone__c'] = true;
                    $optins['MobilePhone'] = $user->phone;
                    $codeModifications['Code_Modification__c'] = getenv('AIF_SALESFORCE_CODES_AUWEBAPP') ;
                    break;
                case 'Email':
                    $optins['Optin_Actions_Urgentes__c'] = true;
                    $optins['Reseaux_thematiques_AU_Origine__c'] = $user->thematicsNetworksOrigin ?? null;
                    break;
                case 'Militant':
                    if ($user->phone) {
                        $optins['MobilePhone'] = $user->phone;
                    }
                    break;
            }

            $user_from_sf = get_salesforce_user_with_email($user->email);
            $user_exist_on_sf = $user_from_sf['totalSize'] > 0;

			if (false ===$user_exist_on_sf) {
                switch ($item['type']) {
                    case 'Sms':
                        $optins['Origine__c'] = getenv('AIF_SALESFORCE_CODES_AUWEBAPP');
                        break;
                    case 'Email':
                        $optins['Origine__c'] = getenv('AIF_SALESFORCE_CODES_AUWEB');
                        break;
                    case 'Militant':
                        $optins['Origine__c'] = getenv('AIF_SALESFORCE_CODES_MILITANT');
                        break;
                }

                $data_user_sf = [
                    'Email' => $user->email,
                    'Salutation' => $user->civility,
                    'Code_Postal__c' => $user->postal_code,
                    'FirstName' => $user->firstname,
                    'LastName' =>  $user->lastname,
                    'Pays__c' => $user->country,
                    ...$optins,
                ];

                post_salesforce_users($data_user_sf);
                WP_CLI::success('[ REGISTER UA ] - New user registered on Salesforce');

                if ($item['type'] !== 'Militant') {
                    update_ua_syncs_with_sf($item['id']);
                    continue;
                }
            }

            if ($user_exist_on_sf) {
                $sfUser = $user_from_sf[0];

                $data_user_sf = [
                    'ID'    => $sfUser->id,
                    'Email' => $user->email,
                    ...$optins,
                    ...$codeModifications,
                ];

                update_salesforce_users($sfUser->id, $data_user_sf);
                WP_CLI::success('[ REGISTER UA SYNC ] - User updated on Salesforce');
            }

            if ('Militant' === $item['type']) {
                $sfUser = $user_exist_on_sf ? $user_from_sf[0] : $user;

                $data_activist_sf = [
                    'Email__c'              => $user->email,
                    'Prenom__c'             => $sfUser->FirstName ?? $user->firstname,
                    'Nom__c'                => $sfUser->LastName ?? $user->lastname,
                    'Code_Postal__c'        => $sfUser->Code_Postal__c ?? $user->postal_code,
                    'Cle_contact__c'        => $user->email,
                    'Optin_Militant__c'     => true,
                    'Date_MAJ_militant__c'  => date('Y-m-d'),
                    'Code_Modification__c'  => getenv('AIF_SALESFORCE_CODES_MILITANT'),
                ];

                post_salesforce_activist($data_activist_sf);
                WP_CLI::success('[ REGISTER MILITANT ] - New militant registered on Salesforce');
            }
            update_ua_syncs_with_sf($item['id']);
            WP_CLI::success('[ UPDATE USER FROM UA ] - Update User on Salesforce');
        }
        return false;
    }
}

WP_CLI::add_command('sync', new Sync_Register_Command());
