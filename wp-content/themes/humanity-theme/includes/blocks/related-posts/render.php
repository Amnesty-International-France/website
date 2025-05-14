<?php

function render_related_posts_block( $attributes, $content = '' ) {
	if ( ! is_singular() ) {
		return '';
	}

	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return '';
	}

	$selected_posts = get_post_meta( $post_id, '_related_posts_selected', true );

	if ( is_array( $selected_posts ) && count( $selected_posts ) > 0 ) {
		$selected_posts = array_filter( array_slice( $selected_posts, 0, 3 ) );

		$query = new WP_Query( [
			'post_type'      => 'post',
			'post__in'       => $selected_posts,
			'orderby'        => 'post__in',
			'posts_per_page' => 3,
		] );
	} else {
		$term = amnesty_get_a_post_term( $post_id );
		if ( ! $term ) {
			return '';
		}

		$query = new WP_Query( [
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'post__not_in'   => [ $post_id ],
			'tax_query'      => [
				[
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => $term->slug,
				],
			],
			'orderby' => 'date',
			'order'   => 'DESC',
		] );
	}

	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();
	?>
	<div class="related-posts-block">
		<div class="content">
			<h2 class="title"><?php echo esc_html( $attributes['title'] ?? 'À lire aussi' ); ?></h2>
			<div class="list">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<?php
					$block = [
						'blockName' => 'amnesty-core/article-card',
						'attrs'     => [
							'direction' => 'portrait',
							'postId'    => get_the_ID(),
						],
						'innerBlocks' => [],
					];
					echo render_block( $block );
					?>
				<?php endwhile; ?>
			</div>
		</div>
	</div>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}
