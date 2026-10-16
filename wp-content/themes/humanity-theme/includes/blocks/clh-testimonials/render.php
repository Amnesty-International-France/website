<?php

declare(strict_types=1);

/**
 * Render each block from its own testimonials, in either campaign state.
 *
 * @param array<string, mixed> $attributes Block attributes.
 */
function render_clh_testimonials_block(array $attributes): string
{
    $victories = amnesty_get_clh_testimonials($attributes);
    if (!$victories) {
        return '';
    }

    ob_start();
    // The reminder belongs immediately before the first real timeline, only on CLH.
    static $reminder_rendered = false;
    if (is_page('changez-leur-histoire') && !$reminder_rendered) {
        $reminder_rendered = true;
        get_template_part('partials/clh-signature-band', null, ['variant' => 'reminder']);
    }
    require __DIR__ . '/view.php';

    return ob_get_clean();
}
