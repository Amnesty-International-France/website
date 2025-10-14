<?php

function aif_block_display()
{
    wp_enqueue_script(
        'aif_block_display',
        get_stylesheet_directory_uri() . '/js/block-display.js',
        [ 'wp-blocks', 'wp-dom-ready', 'wp-edit-post' ],
        false,
        true
    );
}
add_action('enqueue_block_editor_assets', 'aif_block_display');
