<?php
$direction = $args['direction'] ?? 'portrait';

$post_id = $args['post_id'] ?? ($args['post']->ID ?? null);
$post_object = get_post($post_id);

if (!$post_object instanceof WP_Post) {
    $title       = $args['title'] ?? 'Titre par défaut';
    $permalink   = $args['permalink'] ?? '#';
    $date        = $args['date'] ?? date('Y-m-d');
    $thumbnail   = $args['thumbnail'] ?? null;
    $post_terms  = $args['terms'] ?? [];
} else {
    $permalink   = get_permalink($post_object);
    $title       = get_the_title($post_object);
    $date        = get_the_date('', $post_object);
    $thumbnail   = get_the_post_thumbnail($post_id, 'medium', ['class' => 'petition-image']);
    $post_terms  = wp_get_post_terms($post_id, ['category', 'post_tag']);
}

$subtitle_clh = $post_id ? (get_field('subtitle_clh', $post_id) ?: '') : '';
?>

<article class="action-card-change-their-history card-<?php echo esc_attr($direction); ?>">
    <?php if ($thumbnail): ?>
        <a href="<?= esc_url($permalink); ?>" class="petition-thumbnail" aria-label="<?= esc_attr($title); ?>">
            <?= $thumbnail; ?>
        </a>
    <?php else: ?>
        <div class="petition-thumbnail"></div>
    <?php endif; ?>

    <div class="petition-content">
        <div class="petition-title">
            <a class="as-h5" href="<?= esc_url($permalink); ?>">
                <?= esc_html($title); ?>
            </a>
        </div>
        <?php if (!empty($subtitle_clh)) : ?>
            <p class="petition-subtitle">
                <?= esc_html($subtitle_clh); ?>
            </p>
        <?php endif; ?>

        <div class="petition-sign-button">
            <div class='custom-button-block center' style="gap: 12px;">
                <a href="<?= esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer" class="custom-button">
                    <div class='content bg-yellow small'>
                        <div class="button-label">Agir pour <?= esc_html($title); ?></div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</article>
