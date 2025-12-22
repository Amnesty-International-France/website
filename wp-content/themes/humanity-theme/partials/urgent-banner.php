<?php

declare(strict_types=1);

$args = [
    'post_type'      => 'pop-in',
    'posts_per_page' => 1,
    'orderby'        => 'date',
    'order'          => 'DESC',
];

$query = new WP_Query($args);
$popin = $query->post;
if (!$popin) {
    return;
}

$thumbnail         = get_the_post_thumbnail($popin);
$link_action_popin = get_field('link_action_popin', $popin->ID);
$button_text       = get_field('button_text_popin', $popin->ID);

$template_directory = get_template_directory();
$svg               = file_get_contents($template_directory . '/assets/images/icon-cross.svg');
$logo              = file_get_contents($template_directory . '/assets/images/icon-logo-fr.svg');
$button_icon       = file_get_contents($template_directory . '/assets/images/icon-arrow.svg');
?>
<div class="urgent-banner hidden">
	<div class="urgent-banner-modal <?= $thumbnail ? 'with-image' : '' ?>">
		<div class="urgent-container">
			<?php if ($thumbnail) : ?>
				<div class="left">
					<?php echo $thumbnail; ?>
				</div>
			<?php endif; ?>
			<div class="right">
				<div class="header-icon">
					<?php echo $logo; ?>
					<?php
                    echo wp_kses(
                        $svg,
                        [
                            'svg'  => [
                                'class'   => true,
                                'width'   => true,
                                'height'  => true,
                                'viewBox' => true,
                                'fill'    => true,
                                'xmlns'   => true,
                            ],
                            'path' => [
                                'd'               => true,
                                'stroke'          => true,
                                'stroke-width'    => true,
                                'stroke-linecap'  => true,
                                'stroke-linejoin' => true,
                            ],
                        ]
                    );
?>
				</div>
				<div class="body">
					<div class="content-wrapper">
						<h1 class="title">
							<?php echo esc_html(get_the_title($query->post->ID)); ?>
						</h1>
						<p class="content">
							<?php echo get_field('popin_content', $popin->ID); ?>
						</p>
					</div>
					<a class="cta"
					   href="
						<?php echo esc_url($link_action_popin); ?>"
					   target="_blank"
					>
						<?php echo $button_icon; ?>
						<?php echo esc_html($button_text); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
    wp_reset_postdata();
?>
