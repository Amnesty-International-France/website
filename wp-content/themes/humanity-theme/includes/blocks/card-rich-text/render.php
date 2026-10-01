<?php

declare(strict_types=1);

if (!function_exists('render_card_rich_text_block')) {
    /**
     * Render the "Card Rich Text" block
     *
     * The description is made of inner blocks (paragraphs and lists), received in $content.
     * Unlike the "Card Image Text" block, the whole card is not a link, because
     * the description can contain links of its own.
     *
     * @param array<string, mixed> $attributes Block attributes
     * @param string $content Inner blocks HTML
     * @return string HTML output
     */
    function render_card_rich_text_block(array $attributes, string $content = ''): string
    {
        $thumbnail_id = $attributes['thumbnail'] ?? null;
        $surtitle = $attributes['surtitle'] ?? '';
        $title = $attributes['title'] ?? '';
        $permalink = $attributes['permalink'] ?? '';
        $linkTitle = $attributes['linkTitle'] ?? '';
        $newTab = $attributes['newTab'] ?? false;

        $link_extra_attrs = '';
        if ($newTab) {
            $link_extra_attrs = ' target="_blank" rel="noopener noreferrer"';
        }

        ob_start();
        ?>
        <div <?php echo get_block_wrapper_attributes(['class' => 'card-rich-text-block']); ?>>
            <div class="card-rich-text-thumbnail-wrapper">
                <?php if (!empty($thumbnail_id)) : ?>
                    <?php echo wp_get_attachment_image((int) $thumbnail_id, 'large', false, [ 'class' => 'card-rich-text-thumbnail', 'loading' => 'lazy', 'decoding' => 'async' ]); ?>
                <?php endif; ?>
            </div>
            <div class="card-rich-text-content">
                <?php if (!empty($surtitle)) : ?>
                    <p class="card-rich-text-content-surtitle"><?php echo esc_html($surtitle); ?></p>
                <?php endif; ?>
                <?php if (!empty($title)) : ?>
                    <p class="card-rich-text-content-title"><?php echo esc_html($title); ?></p>
                <?php endif; ?>
                <?php if (!empty(trim($content))) : ?>
                    <div class="card-rich-text-content-text"><?php echo $content; ?></div>
                <?php endif; ?>
                <?php if (!empty($permalink) && !empty($linkTitle)) : ?>
                    <div class="card-rich-text-content-see-more">
                        <a href="<?php echo esc_url($permalink); ?>" class="card-rich-text-content-see-more-link"<?php echo $link_extra_attrs; ?>>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                viewBox="0 0 16 16"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M10.7826 7.33336L7.20663 3.75736L8.1493 2.8147L13.3346 8.00003L8.1493 13.1854L7.20663 12.2427L10.7826 8.6667H2.66797V7.33336H10.7826Z"
                                    fill="black"
                                />
                            </svg>
                            <span class="card-rich-text-content-see-more-label"><?php echo esc_html($linkTitle); ?></span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
