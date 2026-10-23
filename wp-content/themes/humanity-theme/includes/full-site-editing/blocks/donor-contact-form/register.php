<?php

declare(strict_types=1);

function register_donor_contact_form_block(): void
{
    register_block_type_from_metadata(__DIR__, [
        'render_callback' => 'render_donor_contact_form_block',
    ]);
}
