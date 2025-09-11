<?php

declare(strict_types=1);

if (!function_exists('render_chip_category_block')) {

    /**
     * Render Chip Category Block
     *
     * @param array<string,mixed> $attributes the block attributes
     *
     * @return string
     * @package Amnesty\Blocks
     */
    function render_chip_category_block(array $attributes): string
    {
        $attributes = wp_parse_args(
            $attributes,
            [
                'label' => '',
                'link' => '',
                'size' => 'medium',
                'style' => 'bg-yellow',
                'icon' => '',
                'isLandmark' => false,
            ]
        );

        $tag         = !empty($attributes['link']) ? 'a' : 'div';
        $icon        = $attributes['icon'];
        $isLandmark  = (bool) $attributes['isLandmark'];

        $iconHtml = '';
        if ($isLandmark && $icon) {
            switch ($icon) {
                case 'decoding':
                    $iconHtml = '<svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8.5" cy="9" r="8" stroke="black"/><circle cx="8.5" cy="9" r="5" stroke="black"/><circle cx="8.5" cy="9" r="2.5" fill="black"/></svg>';
                    break;
                case 'employment-law':
                    $iconHtml = '<svg width="21" height="16" viewBox="0 0 21 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="9.15625" y="-6.10352e-05" width="1.30779" height="15.6935" fill="black"/><rect x="15.6953" y="14.6923" width="1.30779" height="11.7701" transform="rotate(90 15.6953 14.6923)" fill="black"/><rect x="18.3086" y="-6.10352e-05" width="0.653895" height="15.6935" transform="rotate(90 18.3086 -6.10352e-05)" fill="black"/><path d="M5.23116 6.55956C5.23116 8.14817 4.06013 9.17514 2.61558 9.17514C1.17104 9.17514 0 8.14812 0 6.55951C0 6.55951 1.17104 6.55956 2.61558 6.55956C4.06013 6.55956 5.23116 6.55956 5.23116 6.55956Z" fill="black"/><path d="M4.86133 6.30951H0.369141L2.6123 0.693298L4.86133 6.30951Z" stroke="black" stroke-width="0.5"/><path d="M20.9265 6.55956C20.9265 8.14817 19.7554 9.17514 18.3109 9.17514C16.8663 9.17514 15.6953 8.14812 15.6953 6.55951C15.6953 6.55951 16.8663 6.55956 18.3109 6.55956C19.7554 6.55956 20.9265 6.55956 20.9265 6.55956Z" fill="black"/><path d="M20.5566 6.30951H16.0645L18.3076 0.693298L20.5566 6.30951Z" stroke="black" stroke-width="0.5"/></svg>';
                    break;
                case 'data':
                    $iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="12" viewBox="0 0 13 12" fill="none"><rect x="10" y="0.5" width="3" height="11" fill="black"/><rect x="5" y="4.38232" width="3" height="7.11765" fill="black"/><rect y="7.61768" width="3" height="3.88235" fill="black"/></svg>';
                    break;
                case 'detox':
                    $iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="18" viewBox="0 0 17 18" fill="none"><rect x="11" y="14.1069" width="2.17326" height="5.35259" transform="rotate(-45 11 14.1069)" fill="black"/><circle cx="7.60639" cy="7.71473" r="6.85639" stroke="black" stroke-width="1.5"/><path d="M7.38574 5.2359C8.94564 5.2359 10.1869 5.85349 11.0459 6.48102C11.4749 6.79442 11.8055 7.10741 12.0273 7.34137C12.0798 7.39674 12.124 7.4495 12.1641 7.49469C12.1239 7.54001 12.0801 7.59338 12.0273 7.64899C11.8055 7.88295 11.4749 8.19593 11.0459 8.50934C10.1869 9.13689 8.94571 9.75446 7.38574 9.75446C5.82585 9.75439 4.58457 9.13688 3.72559 8.50934C3.29679 8.19605 2.96688 7.88289 2.74512 7.64899C2.69227 7.59324 2.64766 7.54011 2.60742 7.49469C2.64752 7.44944 2.69253 7.39684 2.74512 7.34137C2.9669 7.10746 3.29681 6.79428 3.72559 6.48102C4.58457 5.85348 5.82585 5.23596 7.38574 5.2359Z" stroke="black" stroke-width="0.75"/><circle cx="7.38653" cy="7.4953" r="1.3172" fill="black"/></svg>';
                    break;
            }

            $iconHtml = '<span class="chip-icon">' . $iconHtml . '</span>';
        }

        return sprintf(
            '<%1$s class="chip-category %2$s %3$s"%4$s>%5$s<span class="chip-label">%6$s</span></%1$s>',
            esc_attr($tag),                                           // %1$s : tag = a ou div
            esc_attr($attributes['style']),                           // %2$s : style class
            esc_attr($attributes['size']),                            // %3$s : size class
            $tag === 'a' ? ' href="' . esc_url($attributes['link']) . '"' : '', // %4$s : href si <a>
            $iconHtml,                                                // %5$s : icône HTML
            esc_html($attributes['label'])                            // %6$s : texte du chip
        );
    }
}
