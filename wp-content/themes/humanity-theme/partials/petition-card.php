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

$label = get_field('type', $post_id)['label'] ?? 'Pétition';
$pdf_file = null;
if (isset($GLOBALS['is_my_space_petitions_loop'])) {
    $pdf_file = wp_get_attachment_url(get_field('pdf_petition', $post_id));
    $page_nos_petitions = get_page_by_path('mon-espace/agir-et-se-mobiliser/nos-petitions');
    if ($page_nos_petitions) {
        $permalink_parts = explode('/', parse_url($permalink)['path']);
        $permalink = sprintf('%s%s/', $page_nos_petitions->guid, $permalink_parts[\count($permalink_parts) - 2]);
    }
}
?>

<article class="petition-card card-<?php echo esc_attr($direction); ?>">
    <?php if ($thumbnail): ?>
        <a href="<?= esc_url($permalink); ?>" class="petition-thumbnail">
            <?= $thumbnail; ?>
        </a>
    <?php else: ?>
        <div class="petition-thumbnail"></div>
    <?php endif; ?>

    <?php if (!empty($label)): ?>
        <?= render_chip_category_block([
            'label' => esc_html($label),
            'link' => '',
            'size' => 'large',
            'style' => 'bg-yellow',
            'icon' => '',
        ]); ?>
    <?php endif; ?>

    <div class="petition-content">
        <div class="petition-title">
            <a class="as-h5" href="<?= esc_url($permalink); ?>">
                <?= esc_html($title); ?>
            </a>
        </div>

        <div class="petition-infos">
            <p class="end-date">
                <?php echo esc_html(__("Jusqu'au", 'amnesty')); ?> <?php echo esc_html($end_date); ?>
            </p>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: <?php echo esc_attr($percentage); ?>%;"></div>
            </div>
            <p class="supports">
                <?php
                    $soutien_mot = ($current > 1) ? 'soutiens.' : 'soutien.';
echo esc_html(number_format_i18n($current) . ' ' . $soutien_mot);
?>
                <span class="help-us">
                    <?php echo esc_html(__('Aidez-nous à atteindre', 'amnesty')); ?> <?php echo esc_html(number_format_i18n($goal)); ?>
                </span>
            </p>
        </div>

        <div class="petition-sign-button">
            <div class='custom-button-block center' style="gap: 12px;">
                <a href="<?= esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer" class="custom-button">
                    <div class='content bg-yellow small'>
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
                        <div class="button-label">Signer la pétition</div>
                    </div>
                </a>

				<?php if ($pdf_file): ?>
					<a href="<?= esc_url($pdf_file); ?>" target="_blank" rel="noopener noreferrer" class="custom-button">
						<div class='content bg-black small'>
							<div class="petition-download-icon">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
								</svg>
							</div>
						</div>
					</a>
				<?php endif; ?>
            </div>
        </div>
    </div>
</article>
