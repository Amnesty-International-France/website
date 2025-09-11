<?php

declare(strict_types=1);

if (!function_exists('render_actions_homepage_block')) {
    /**
     * Render the Actions Homepage block
     *
     * @param array<string, mixed> $attributes
     *
     * @return string
     */
    function render_actions_homepage_block(array $attributes): string
    {
        $title = $attributes['title'] ?? '';
        $chapo = $attributes['chapo'] ?? '';
        $items = $attributes['items'] ?? [];

        ob_start();
        ?>
        <section class="actions-homepage">
            <div class="content">
                <?php if (!empty($title)) : ?>
                    <h2 class="title"><?php echo esc_html($title); ?></h2>
                <?php endif; ?>

                <?php if (!empty($chapo)) : ?>
                    <p class="chapo"><?php echo esc_html($chapo); ?></p>
                <?php endif; ?>

                <?php if (!empty($items)) : ?>
                    <div class="items">
                        <?php foreach ($items as $item) :
                            $itemTitle       = $item['itemTitle'] ?? '';
                            $itemDescription = $item['itemDescription'] ?? '';
                            $buttonLabel     = $item['buttonLabel'] ?? '';
                            $buttonLink      = $item['buttonLink'] ?? '#';
                            $imageUrl        = $item['imageUrl'] ?? '';
                            ?>
                            <div class="item">
                                <div class="image-wrapper">
                                    <?php if (!empty($imageUrl)) : ?>
                                        <img class="image" src="<?php echo esc_url($imageUrl); ?>" alt="<?php echo esc_attr($itemTitle); ?>" />
                                    <?php else : ?>
                                        <div class="no-image">
                                            <span><?php echo esc_html__('Sélectionnez une image', 'amnesty'); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($itemTitle)) : ?>
                                        <div class="item-title-wrapper">
                                            <h3 class="item-title"><?php echo esc_html($itemTitle); ?></h3>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="item-content">
                                    <?php if (!empty($itemDescription)) : ?>
                                        <span class="item-description"><?php echo esc_html($itemDescription); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($buttonLabel) && !empty($buttonLink)) : ?>
                                        <a
                                            href="<?php echo esc_url($buttonLink); ?>"
                                            class="item-button"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <div class="icon-container">
                                                <svg
                                                    class="icon"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    strokeWidth="1.5"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                      strokeLinecap="round"
                                                      strokeLinejoin="round"
                                                      d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                                                    />
                                                </svg>
                                            </div>
                                            <span class="label"><?php echo esc_html($buttonLabel); ?></span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}
