<?php

declare(strict_types=1);

if (!function_exists('render_read_also_block')) {
    /**
     * Render the Read Also block dynamically
     *
     * @param array<string, mixed> $attributes
     * @return string
     */
    function render_read_also_block(array $attributes): string
    {
        $link_type      = $attributes['linkType'] ?? 'internal';
        $internal_url   = $attributes['internalUrl'] ?? '';
        $internal_title = $attributes['internalUrlTitle'] ?? '';
        $external_url   = $attributes['externalUrl'] ?? '';
        $external_label = $attributes['externalLabel'] ?? '';
        $target_blank   = $attributes['targetBlank'] ?? false;

        $url   = '';
        $label = '';

        if ($link_type === 'internal' && !empty($internal_url)) {
            $url   = esc_url($internal_url);
            $label = wp_kses_post($internal_title);
        } elseif ($link_type === 'external' && !empty($external_url)) {
            $url   = esc_url($external_url);
            $label = esc_html($external_label ?: $external_url);
        }

        if (empty($url)) {
            $link_html = '<span>' . esc_html__('Aucun contenu sélectionné.', 'amnesty') . '</span>';
        } else {
            $target_attr = $target_blank ? ' target="_blank" rel="noopener noreferrer"' : '';
            $link_html = sprintf(
                '<a href="%s"%s>%s</a>',
                $url,
                $target_attr,
                $label
            );
        }

        return sprintf(
            '<div class="read-also-block"><p>%s : %s</p></div>',
            esc_html__('À lire aussi', 'amnesty'),
            $link_html
        );
    }
}
