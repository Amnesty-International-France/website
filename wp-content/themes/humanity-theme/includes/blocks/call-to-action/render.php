<?php

declare(strict_types=1);

if (!function_exists('render_call_to_action_block')) {
    /**
     * Render the "Call to Action" block
     *
     * @param array<string, mixed> $attributes Block attributes
     * @return string HTML output
     */
    function render_call_to_action_block(array $attributes): string {
        $title        = $attributes['title'] ?? '';
        $subTitle     = $attributes['subTitle'] ?? '';
        $buttonLabel  = $attributes['buttonLabel'] ?? __('En savoir plus', 'amnesty');
        $buttonLink   = $attributes['buttonLink'] ?? '';
        $direction    = $attributes['direction'] ?? 'horizontal';

        $buttonAlignmentClass = ($direction === 'vertical') ? 'center' : 'right';

        ob_start();
        ?>
        <div class="call-to-action-block <?php echo esc_attr($direction); ?>">
            <div class="call-to-action-content">
                <?php if ($title): ?>
                    <p class="title"><?php echo esc_html($title); ?></p>
                <?php endif; ?>

                <?php if ($subTitle): ?>
                    <p class="subTitle"><?php echo esc_html($subTitle); ?></p>
                <?php endif; ?>
            </div>
            <div class='custom-button-block <?php echo esc_attr($buttonAlignmentClass); ?>'>
                <a href="<?php echo esc_url($buttonLink); ?>" target="_blank" rel="noopener noreferrer" class="custom-button">
                    <div class='content bg-yellow medium'>
                        <div class="icon-container">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                strokeWidth="1.5"
                                stroke="currentColor"
                            >
                                <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                        <div class="button-label"><?php echo esc_html($buttonLabel); ?></div>
                    </div>
                </a>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }
}
