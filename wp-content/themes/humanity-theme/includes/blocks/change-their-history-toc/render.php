<?php

declare(strict_types=1);

if (!function_exists('render_change_their_history_toc_block')) {
    /**
     * Render the Change Their History TOC block.
     *
     * @param array<string, mixed> $attributes Block attributes.
     * @return string HTML output.
     */
    function render_change_their_history_toc_block(array $attributes): string
    {
        ob_start();
        ?>
        <nav class="change-their-history-toc-block" aria-label="<?php echo esc_attr__('Sommaire', 'amnesty'); ?>">
            <ul class="change-their-history-toc-list" data-change-their-history-toc-list></ul>
        </nav>
        <?php
        return ob_get_clean();
    }
}
