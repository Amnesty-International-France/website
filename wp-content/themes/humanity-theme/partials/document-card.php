<article class="article-card card-<?php echo esc_attr($direction); ?>">
    <?php if ($thumbnail) : ?>
        <a href="<?= esc_url($permalink); ?>" class="article-thumbnail" target="_blank" rel="noopener noreferrer">
            <?= $thumbnail; ?>
        </a>
    <?php else : ?>
        <div class="article-thumbnail"></div>
    <?php endif; ?>

    <?php if (!empty($label)): ?>
        <?= render_chip_category_block([
            'label' => esc_html($label),
            'size' => 'large',
            'style' => 'bg-yellow',
            'icon' => $icon ?? '',
        ]); ?>
    <?php endif; ?>

    <div class="article-content">
        <time class="article-date" datetime="<?= esc_attr(date('c', strtotime($date))); ?>">
            <?= esc_html($date); ?>
        </time>
        <div class="article-title">
            <a class="as-h5" href="<?= esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer" >
                <?= esc_html($title); ?>
            </a>
        </div>
        <div class="article-terms <?php if (empty($post_terms)) {
            echo 'is-empty';
        } ?>">
            <?php foreach ($post_terms as $term): ?>
                <?php
                $term_link = get_term_link($term);

                if (!is_wp_error($term_link)) {
                    $custom_routes = [
                        'combat'   => '/combats/',
                        'location' => '/categorie/',
                    ];

                    if (array_key_exists($term->taxonomy, $custom_routes)) {
                        $term_link = $custom_routes[$term->taxonomy] . $term->slug;
                    }
                }
                ?>
                <?= render_chip_category_block([
                    'label' => esc_html($term->name),
                    'size'  => 'small',
                    'style' => 'bg-gray',
                    'link'  => esc_url($term_link),
                ]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</article>
