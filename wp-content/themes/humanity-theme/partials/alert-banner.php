<?php

declare(strict_types=1);

$args = [
    'post_type' => 'alert-banner',
    'posts_per_page' => 1,
    'orderby' => 'date',
    'order' => 'DESC',
];


$query = new WP_Query($args);
$alert_banner = $query->post;

if (!$alert_banner) {
    return;
}

$thumbnail = get_the_post_thumbnail($alert_banner);
$alert_banner_description = get_field('description', $alert_banner->ID);
$alert_banner_url = get_field('url', $alert_banner->ID);
$alert_banner_label_cta = get_field('label_cta', $alert_banner->ID);
$url_alert_banner = get_field('url_alert_banner', $alert_banner->ID);
$label_cta_alert_banner = get_field('label_cta_alert_banner', $alert_banner->ID);
$template_directory = get_template_directory();
$svg = file_get_contents($template_directory . '/assets/images/icon-cross.svg');

?>

<div id="alert-banner-<?= $query->post->ID ?>" class="alert-banner">
	<?php
    echo wp_kses(
        $svg,
        [
            'svg' => [
                'class' => true,
                'width' => true,
                'height' => true,
                'viewBox' => true,
                'fill' => true,
                'xmlns' => true,
            ],
            'path' => [
                'd' => true,
                'stroke' => true,
                'stroke-width' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
            ],
        ]
    );
?>
	<div class="alert-banner-body">
		<div class="alert-banner-main">
			<?php if ($thumbnail) : ?>
				<div class="alert-body-image">
					<?php echo $thumbnail; ?>
				</div>
			<?php endif; ?>
			<div class="alert-body-text">
				<p class="title"><?php echo esc_html(get_the_title($alert_banner->ID)); ?></p>
				<p class="description"><?php echo esc_html($alert_banner_description); ?></p>
			</div>
		</div>
		<a class="cta" href="<?php echo esc_url($alert_banner_url); ?>" target="_blank"
		   rel="noopener noreferrer"><?php echo esc_html($alert_banner_label_cta); ?></a>
	</div>
</div>
<?php
wp_reset_postdata();
?>


