<?php

declare(strict_types=1);


$args = array(
	'post_type'      => 'pop-in',
	'posts_per_page' => 1,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

$last_post = new WP_Query( $args );

$svg = file_get_contents( get_template_directory() . '/assets/images/icon-cross.svg' );

if ( $last_post->have_posts() ) :
	while ( $last_post->have_posts() ) :
		$last_post->the_post(); ?>

		<div class="urgent-banner">
			<?php
			echo wp_kses(
				$svg,
				[
					'svg'  => [
						'class'           => true,
						'xmlns'           => true,
						'width'           => true,
						'height'          => true,
						'viewBox'         => true,
						'fill'            => true,
						'stroke'          => true,
						'stroke-width'    => true,
						'stroke-linecap'  => true,
						'stroke-linejoin' => true,
					],
					'line' => [
						'x1' => true,
						'y1' => true,
						'x2' => true,
						'y2' => true,
					],
				]
			);
			?>
			<div class="urgent-container">
				<div class="urgent-body">
				<div class="urgent-title">
					<?php the_title(); ?>
				</div>
					<?php the_content(); ?>
				</div>
			</div>
		</div>
		<?php
	endwhile;
	wp_reset_postdata();
endif;

?>
