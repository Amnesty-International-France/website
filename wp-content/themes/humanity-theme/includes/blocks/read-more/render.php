<?php

declare(strict_types=1);

/**
 * Render callback for the "amnesty-core/read-more" block.
 *
 * @param array $attributes Block attributes.
 * @param string $content Inner content of the block.
 *
 * @return string
 */

function render_read_more_block($attributes, $content) {
    $read_more_label = $attributes['label'] ?? 'Lire la suite';

    ob_start();
    ?>
    <div class="read-more-block">
        <div class="read-more-toggle"
            data-read-more-label="<?php echo esc_attr($read_more_label); ?>">
            <div class="icon-container">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="10" viewBox="0 0 15 10" fill="currentColor">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M7.03859 6.3641L12.5133 0L14.0772 1.81795L7.03859 10L0 1.81795L1.56389 0L7.03859 6.3641Z" fill="#FFFF00"/>
                </svg>
            </div>
            <span class="label"><?php echo esc_html($read_more_label); ?></span>
        </div>
        <div class="read-more-content collapsed">
            <?php echo $content; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
