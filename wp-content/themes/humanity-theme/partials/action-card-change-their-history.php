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

    $goal = $args['goal'] ?? 200000;
    $current = $args['current'] ?? 0;
    $end_date = $args['end_date'] ?? '30.06.2025';
    $percentage = ($goal > 0) ? min(($current / $goal) * 100, 100) : 0;
} else {
    $permalink   = get_permalink($post_object);
    $title       = get_the_title($post_object);
    $date        = get_the_date('', $post_object);
    $thumbnail   = get_the_post_thumbnail($post_id, 'medium', ['class' => 'petition-image']);

    $post_terms  = wp_get_post_terms($post_id, ['category', 'post_tag']);

    $goal = get_field('objectif_signatures', $post_id) ?: 200000;
    $current = amnesty_get_petition_signature_count($post_id) ?: 0;
    $end_date_raw = get_field('date_de_fin', $post_id);
    $end_date = !empty($end_date_raw) ? format_date_php($end_date_raw) : '30.06.2025';
    $percentage = ($goal > 0) ? min(($current / $goal) * 100, 100) : 0;
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
