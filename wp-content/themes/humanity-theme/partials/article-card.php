<?php
$direction = $args['direction'] ?? 'portrait';

global $post;

$post_id     = $args['post_id'] ?? ( $args['post']->ID ?? ( $post->ID ?? null ) );
$post_object = get_post( $post_id );

if ( ! $post_object instanceof WP_Post ) {
	$title         = $args['title'] ?? 'Titre par défaut';
	$permalink     = $args['permalink'] ?? '#';
	$date          = $args['date'] ?? date( 'Y-m-d' );
	$thumbnail     = $args['thumbnail'] ?? null;
	$main_category = $args['main_category'] ?? null;
	$post_terms    = $args['terms'] ?? [];

	$label      = $args['label'] ?? ( $main_category->name ?? null );
	$link       = $args['label_link'] ?? '';
	$chip_style = $args['chip_style'] ?? match ( $main_category->slug ?? null ) {
		'actualites' => 'bg-yellow',
		'dossiers' => 'bg-black',
		default => 'bg-yellow',
	};
} else {
	$permalink = get_permalink( $post_object );
	$title     = get_the_title( $post_object );
	$date      = get_the_date( '', $post_object );
	$thumbnail = get_the_post_thumbnail( $post_id, 'medium', [ 'class' => 'article-image' ] );

	$main_category = amnesty_get_a_post_term( $post_id );
	if ( ! ( $main_category instanceof WP_Term ) ) {
		$main_category = null;
	}

	$post_terms = amnesty_get_post_terms( $post_id );
	$post_terms = array_filter( $post_terms, static fn( $term ) => ! in_array( $term->taxonomy, [ 'keyword', 'landmark_category' ] ) );

	if ( $main_category ) {
		$post_terms = array_filter(
			$post_terms,
			static function ( $term ) use ( $main_category ) {
				return $term->taxonomy !== $main_category->taxonomy && $term->term_id !== $main_category->term_id;
			}
		);
	}

	if ( $main_category ) {
		$chip_style = match ( $main_category->slug ) {
			'actualites' => 'bg-yellow',
			'dossiers' => 'bg-black',
			default => 'bg-yellow',
		};

		$acf_singular  = get_field( 'category_singular_name', $main_category );
		$default_label = $acf_singular ?: $main_category->name;

		$editorial_category = get_field( 'editorial_category', $post_id );
		$label              = $editorial_category && isset( $editorial_category['label'] ) ? $editorial_category['label'] : $default_label;
		if ( $editorial_category && isset( $editorial_category['label'], $editorial_category['value'] ) ) {
			$label = get_editorial_category_singular_label( $editorial_category['value'] ) ?: $editorial_category['label'];
		}
		$link = '';
	} else {
		$post_type  = get_post_type( $post_object );
		$chip_style = 'bg-yellow';

		if ( 'landmark' === $post_type ) {
			$repere_terms = wp_get_object_terms( $post_id, 'landmark_category' );

			if ( ! empty( $repere_terms ) && ! is_wp_error( $repere_terms ) ) {
				$main_category = $repere_terms[0];
				$label         = $main_category->name;
				$link          = '';
				$icon          = match ( strtolower( $main_category->slug ) ) {
					'decryptage' => 'decoding',
					'droit-international' => 'employment-law',
					'data' => 'data',
					'desintox' => 'detox',
					default => '',
				};
			} else {
				$post_type_object = get_post_type_object( $post_type );
				$label            = $post_type_object->labels->singular_name;
				$link             = get_post_type_archive_link( $post_type );
			}
		} else {
			$post_type_object = get_post_type_object( $post_type );
			$label            = $post_type_object->labels->singular_name;
			$link             = get_post_type_archive_link( $post_type );
		}
	}
}
?>

<article class="article-card card-<?php echo esc_attr( $direction ); ?>">
	<?php if ( $thumbnail ) : ?>
		<a href="<?= esc_url( $permalink ); ?>" class="article-thumbnail">
			<?= $thumbnail; ?>
		</a>
	<?php else : ?>
		<div class="article-thumbnail"></div>
	<?php endif; ?>

	<?php if ( ! empty( $label ) ) : ?>
		<?=
		render_chip_category_block(
			[
				'label'      => esc_html( $label ),
				'link'       => esc_url( $link ),
				'size'       => 'large',
				'style'      => esc_attr( $chip_style ),
				'isLandmark' => ( 'landmark' === get_post_type( $post_object ) ),
				'icon'       => $icon ?? '',
			]
		);
		?>
	<?php endif; ?>

	<div class="article-content">
		<time class="article-date" datetime="<?= esc_attr( date( 'c', strtotime( $date ) ) ); ?>">
			<?= esc_html( $date ); ?>
		</time>
		<div class="article-title">
			<a class="as-h5" href="<?= esc_url( $permalink ); ?>">
				<?= esc_html( $title ); ?>
			</a>
		</div>
		<div class="article-terms
		<?php
		if ( empty( $post_terms ) ) {
			echo 'is-empty';
		}
		?>
		">
			<?php foreach ( $post_terms as $term ) : ?>
					<?=
					render_chip_category_block(
						[
							'label' => esc_html( $term->name ),
							'size'  => 'small',
							'style' => 'bg-gray',
							'link'  => '',
						]
					)
					?>
			<?php endforeach; ?>
		</div>
	</div>
</article>
