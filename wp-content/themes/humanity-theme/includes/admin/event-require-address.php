<?php

declare(strict_types=1);

/**
 * Address fields that must be filled on the event's venue.
 *
 * @var string[]
 */
const AIF_REQUIRED_VENUE_ADDRESS_FIELDS = [
    '_VenueCity',
    // '_VenueZip', // code postal non requis : souvent absent (intégré à l'adresse).
];

// Classic editor: the venue is linked by The Events Calendar's addEventMeta on
// save_post (priority 15), so priority 25 runs after it. During a REST request
// (block editor), the venue is linked AFTER save_post — bail here and let the
// rest_after_insert hook below handle it once the venue is attached.
add_action('save_post', 'require_event_address', 25, 1);

function require_event_address($post_id): void
{
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    if ('tribe_events' !== get_post_type($post_id)) {
        return;
    }

    enforce_event_address((int) $post_id);
}

// Block editor: fires once REST has saved the post meta, including the venue link.
add_action(
    'rest_after_insert_tribe_events',
    function ($post): void {
        enforce_event_address((int) $post->ID);
    },
    20,
    1
);

/**
 * Force an event back to draft when its venue address is incomplete.
 *
 * National events ("Partout en France") are exempt as they have no location.
 */
function enforce_event_address(int $post_id): void
{
    if ('publish' !== get_post_status($post_id)) {
        return;
    }

    if (get_field('_EventNational', $post_id)) {
        return;
    }

    if (event_has_complete_address($post_id)) {
        return;
    }

    // Avoid recursion: wp_update_post() fires save_post / rest_after_insert again.
    remove_action('save_post', 'require_event_address', 25);

    wp_update_post(
        [
            'ID'          => $post_id,
            'post_status' => 'draft',
        ]
    );

    add_action('save_post', 'require_event_address', 25, 1);

    set_transient('aif_event_address_error_' . get_current_user_id(), true, 60);
}

/**
 * Check that the event's venue exists and has all required address fields.
 */
function event_has_complete_address($post_id): bool
{
    if (! function_exists('tribe_get_venue_id')) {
        return true;
    }

    $venue_id = tribe_get_venue_id($post_id);

    if (! $venue_id) {
        return false;
    }

    foreach (AIF_REQUIRED_VENUE_ADDRESS_FIELDS as $field) {
        if ('' === trim((string) get_post_meta($venue_id, $field, true))) {
            return false;
        }
    }

    return true;
}

add_action('admin_notices', 'show_event_address_error_notice');

/**
 * Display an error notice when an event was forced back to draft.
 */
function show_event_address_error_notice(): void
{
    $transient_key = 'aif_event_address_error_' . get_current_user_id();

    if (! get_transient($transient_key)) {
        return;
    }

    delete_transient($transient_key);

    printf(
        '<div class="notice notice-error is-dismissible"><p>%s</p></div>',
        esc_html__(
            'La ville du lieu est obligatoire pour publier un évènement. L\'évènement a été enregistré en brouillon.',
            'humanity'
        )
    );
}
