<?php

declare(strict_types=1);

/**
 * Render callback for the "amnesty-core/button" block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function render_button_block(array $attributes): string {
    $postId      = $attributes['postId'] ?? 0;
    $label       = $attributes['label'] ?? 'Bouton';
    $size        = $attributes['size'] ?? 'medium';
    $style       = $attributes['style'] ?? 'bg-yellow';
    $icon        = $attributes['icon'] ?? '';
    $externalUrl = $attributes['externalUrl'] ?? '';
    $linkType    = $attributes['linkType'] ?? 'internal';
    $alignment   = $attributes['alignment'] ?? 'left';
    
    if ($linkType === 'external') {
        $href = $externalUrl;
    } elseif ($linkType === 'internal' && $postId) {
        $post = get_post($postId);
        $href = $post ? get_permalink($post) : '#';
    } else {
        $href = '#';
    }

    $iconHtml = '';
    switch ($icon) {
        case 'arrow-left':
            $iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>';
            break;
        case 'arrow-right':
            $iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>';
            break;
        case 'zoom-in':
            $iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Zm8.25-3.75a.75.75 0 0 1 .75.75v2.25h2.25a.75.75 0 0 1 0 1.5h-2.25v2.25a.75.75 0 0 1-1.5 0v-2.25H7.5a.75.75 0 0 1 0-1.5h2.25V7.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" /></svg>';
            break;
        case 'pencil':
            $iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>';
            break;
    }

    $classes = [
        'custom-button-block',
        $alignment,
    ];
    $contentClasses = [
        'content',
        $size,
        $style,
    ];

    ob_start();
    ?>
    <div class="<?php echo esc_attr(implode(' ', $classes)); ?>">
        <a href="<?php echo esc_url($href); ?>" class="custom-button" target="_blank" rel="noopener noreferrer">
            <div class="<?php echo esc_attr(implode(' ', $contentClasses)); ?>">
                <?php if ($icon) : ?>
                    <div class="icon-container">
                        <?php echo $iconHtml; ?>
                    </div>
                <?php endif; ?>
                <div class="button-label"><?php echo esc_html($label); ?></div>
            </div>
        </a>
    </div>
    <?php
    return ob_get_clean();
}
