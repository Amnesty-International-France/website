<?php

declare(strict_types=1);

require_once __DIR__ . '/class-rest-preview.php';

function register_donor_contact_form_block(): void
{
    register_block_type_from_metadata(__DIR__, [
        'render_callback' => 'render_donor_contact_form_block',
    ]);
    add_filter('rest_request_before_callbacks', [AIF_Donor_Contact_Form_REST_Preview::class, 'begin'], 10, 3);
    add_filter('rest_request_after_callbacks', [AIF_Donor_Contact_Form_REST_Preview::class, 'end']);
}
