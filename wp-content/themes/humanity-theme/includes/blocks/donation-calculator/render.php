<?php

declare(strict_types=1);

if (! function_exists('render_donation_calculator_block')) {

    /**
     * Render Donation Calculator Block
     *
     * @param array<string,mixed> $attributes the block attributes
     *
     * @return string
     * @package Amnesty\Blocks
     */
    function render_donation_calculator_block($attributes, $content, $block): string
    {
        $size        = $attributes['size'] ?? '';
        $with_header = $attributes['with_header'] ?? false;
        $with_tabs   = $attributes['with_tabs'] ?? false;
        $with_legend = $attributes['with_legend'] ?? false;
        $href        = $attributes['href'] ?? '';
        $rate        = (int) ($attributes['rate'] ?? 66);
        $is_popin     = $attributes['is_popin'] ?? false;
        $position    = $attributes['position'] ?? '';
        $default_tab = $attributes['default_tab'] ?? 'monthly';

        if (! in_array($rate, [ 66, 75 ], true)) {
            $rate = 66;
        }

        $args = [
            'size'        => $size,
            'with_header' => $with_header,
            'with_tabs'   => $with_tabs,
            'with_legend' => $with_legend,
            'href'        => $href,
            'rate'        => $rate,
            'is_popin'     => $is_popin,
            'position'    => $position,
            'default_tab' => $default_tab,
        ];

        ob_start();
        $template_path = locate_template('partials/donation-calculator.php');

        if ($template_path) {
            extract($args);
            include $template_path;
        } else {
            error_log('❌ Template "partials/donation-calculator.php" introuvable');
        }
        return ob_get_clean();
    }
}
