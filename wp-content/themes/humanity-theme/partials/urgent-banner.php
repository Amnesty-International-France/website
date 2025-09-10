<?php

declare(strict_types=1);

$args = [
    'post_type'      => 'pop-in',
    'posts_per_page' => 1,
    'orderby'        => 'date',
    'order'          => 'DESC',
];

$last_post = new WP_Query($args);

$svg               = file_get_contents(get_template_directory() . '/assets/images/icon-cross.svg');
$logo              = file_get_contents(get_template_directory() . '/assets/images/icon-logo-fr.svg');
$thumbnail         = get_the_post_thumbnail($last_post->post);
$link_action_popin = get_field('link_action_popin', $last_post->post->ID);
$button_text       = get_field('button_text_popin', $last_post->post->ID);
$button_icon       = file_get_contents(get_template_directory() . '/assets/images/icon-arrow.svg');

if ($last_post->have_posts()) :
    while ($last_post->have_posts()) :
        $last_post->the_post(); ?>

		<div class="urgent-banner hidden">
			<div class="urgent-banner-modal
			<?php
            if ($thumbnail) {
                echo 'with-image';
            }
        ?>
			">
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
							<h1 class="title"> <?php echo esc_html(get_the_title($last_post->post->ID)); ?></h1>
							<p class="content"><?php echo wp_kses_post(get_the_content(null, false, $last_post->post->ID)); ?></p>
							<a class="cta"
								href="<?php echo esc_url($link_action_popin); ?>"
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
    endwhile;
    wp_reset_postdata();
endif;

?>
