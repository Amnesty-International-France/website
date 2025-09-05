<?php

function render_related_posts_block( $attributes ) {
	if ( ! is_singular() ) {
		return '';
	}

	$nb_posts = (int) $attributes['nb_posts'];

	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return '';
	}

	$selected_posts = get_field( '_related_posts_selected', $post_id );

	if ( is_array( $selected_posts ) && isset( $selected_posts[0] ) && is_object( $selected_posts[0] ) ) {
		$selected_posts = array_map( fn( $post ) => $post->ID, $selected_posts );
	}

	if ( is_array( $selected_posts ) && count( $selected_posts ) > 0 ) {
		$selected_posts = array_filter( array_slice( $selected_posts, 0, $nb_posts ) );

		$query = new WP_Query( [
			'post_type'      => 'any',
			'post__in'       => $selected_posts,
			'orderby'        => 'post__in',
			'posts_per_page' => $nb_posts,
		] );
	} else {
		$term = amnesty_get_a_post_term( $post_id );
		if ( $term ) {
			$tax_query = [
				[
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => $term->slug,
				],
			];
		}

		$query = new WP_Query( [
			'post_type'      => get_post_type( $post_id ),
			'posts_per_page' => $nb_posts,
			'post__not_in'   => [ $post_id ],
			'tax_query'      => $tax_query ?? [],
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );
	}

	if ( ! $query->have_posts() ) {
		return '';
	}

	$card_direction = $attributes['display'] === 'chronique' ? 'landscape' : 'portrait';

	ob_start();
	?>
	<div class="related-posts-block <?php echo esc_attr( $attributes['display'] ); ?>">
		<div class="content">
			<h2 class="title"><?php echo esc_html( $attributes['title'] ?? 'À lire aussi' ); ?></h2>
			<div class="list">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<?php
					$block = [
						'blockName'   => 'amnesty-core/article-card',
						'attrs'       => [
							'direction' => $card_direction,
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
