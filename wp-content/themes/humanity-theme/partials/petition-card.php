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
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" width="20" height="19.97" viewBox="0 0 20 19.97"><defs></defs><path fill="currentColor" d="M6.691,0.131 L0.137,6.674 C-0.061,6.872 -0.061,7.216 0.137,7.414 L2.490,9.762 C2.694,9.965 3.026,9.965 3.231,9.762 L3.745,9.249 C5.096,10.921 6.425,13.060 6.425,14.032 C6.425,14.187 6.389,14.302 6.320,14.371 C6.194,14.497 6.137,14.703 6.177,14.874 C6.263,15.244 6.298,15.396 12.933,17.736 C16.079,18.845 19.265,19.924 19.292,19.933 C19.480,19.997 19.691,19.948 19.845,19.793 C19.944,19.694 19.999,19.563 19.999,19.424 C19.999,19.366 19.990,19.310 19.961,19.227 C19.695,18.444 18.749,15.675 17.770,12.907 C15.428,6.283 15.274,6.247 14.904,6.161 C14.727,6.119 14.529,6.175 14.401,6.304 C13.876,6.828 11.357,5.413 9.269,3.733 L9.783,3.220 C9.981,3.022 9.981,2.678 9.783,2.480 L7.431,0.132 C7.226,-0.072 6.895,-0.072 6.691,0.131 ZM14.586,7.370 C15.206,8.761 16.880,13.480 18.252,17.466 L13.151,12.384 C13.288,12.126 13.360,11.839 13.360,11.544 C13.360,11.067 13.174,10.618 12.837,10.282 C12.138,9.585 11.002,9.585 10.304,10.282 C9.966,10.619 9.780,11.068 9.780,11.546 C9.780,12.024 9.966,12.473 10.304,12.811 C10.858,13.364 11.724,13.487 12.409,13.124 L17.499,18.217 C13.502,16.845 8.773,15.172 7.388,14.557 C7.434,14.402 7.457,14.232 7.457,14.048 C7.457,12.364 5.491,9.737 4.541,8.567 L8.587,4.528 C9.951,5.634 12.959,7.851 14.586,7.370 Z"></path></svg>
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
