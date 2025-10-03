<?php

function tax_location_rewrite_rules()
{
    add_rewrite_rule(
        '^categorie/([^/]+)/page/([0-9]{1,})/?$',
        'index.php?location=$matches[1]&paged=$matches[2]',
        'top'
    );

    add_rewrite_rule(
        '^categorie/([^/]+)/?$',
        'index.php?location=$matches[1]',
        'top'
    );
}
add_action('init', 'tax_location_rewrite_rules');
