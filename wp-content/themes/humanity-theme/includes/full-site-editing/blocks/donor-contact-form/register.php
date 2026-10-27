<?php

declare(strict_types=1);

require_once __DIR__ . '/submission.php';

function register_donor_contact_form_block(): void
{
    register_block_type_from_metadata(__DIR__, [
        'render_callback' => 'render_donor_contact_form_block',
    ]);
    // auth_my_space() prepares the authorized member at priority 10.
    add_action('template_redirect', 'aif_handle_donor_contact_form', 20);
}
