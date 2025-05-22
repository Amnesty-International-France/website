<?php
$direction = $args['direction'] ?? 'portrait';

global $post;

$post_id = $args['post_id'] ?? ($args['post']->ID ?? ($post->ID ?? null));
$post_object = get_post($post_id);

if (!$post_object instanceof WP_Post) {
	$title = $args['title'] ?? 'Titre par défaut';
	$permalink = $args['permalink'] ?? '#';
	$date = $args['date'] ?? date('Y-m-d');
	$thumbnail = $args['thumbnail'] ?? null;
	$main_category = $args['main_category'] ?? null;
	$post_terms = $args['terms'] ?? [];

	$label = $args['label'] ?? ($main_category->name ?? null);
	$link = $args['label_link'] ?? '';
	$chip_style = $args['chip_style'] ?? match ($main_category->slug ?? null) {
		'actualites' => 'bg-yellow',
		'dossiers' => 'bg-black',
		default => 'bg-yellow',
	};
} else {
	$permalink = get_permalink($post_object);
	$title = get_the_title($post_object);
	$date = get_the_date('', $post_object);
	$thumbnail = get_the_post_thumbnail($post_id, 'medium', ['class' => 'article-image']);

	$main_category = amnesty_get_a_post_term( $post_id );
	if ( ! ( $main_category instanceof WP_Term ) ) {
		$main_category = null;
	}

	$post_terms = amnesty_get_post_terms($post_id);
	$post_terms = array_filter($post_terms, static fn($term) => !in_array($term->taxonomy, ['keyword', 'landmark_category']));

	if ($main_category) {
		$post_terms = array_filter($post_terms, static function ($term) use ($main_category) {
			return $term->taxonomy !== $main_category->taxonomy && $term->term_id !== $main_category->term_id;
		});
	}

	if ($main_category) {
		$chip_style = match ($main_category->slug) {
			'actualites' => 'bg-yellow',
			'dossiers' => 'bg-black',
			default => 'bg-yellow',
		};

		$acf_singular = get_field('category_singular_name', $main_category);
		$default_label = $acf_singular ?: $main_category->name;

		$editorial_category = get_field('editorial_category', $post_id);
		$label = $editorial_category && isset($editorial_category['label']) ? $editorial_category['label'] : $default_label;
		if ($editorial_category && isset($editorial_category['label'], $editorial_category['value'])) {
			$label = get_editorial_category_singular_label($editorial_category['value']) ?: $editorial_category['label'];
		}
		$link = '';
	} else {
		$post_type = get_post_type($post_object);
		$chip_style = 'bg-yellow';

		if ('landmark' === $post_type) {
			$repere_terms = wp_get_object_terms($post_id, 'landmark_category');

			if (!empty($repere_terms) && !is_wp_error($repere_terms)) {
				$main_category = $repere_terms[0];
				$label = $main_category->name;
				$link = '';
				$icon = match (strtolower($main_category->slug)) {
					'decryptage' => 'decoding',
					'droit-international' => 'employment-law',
					'data' => 'data',
					'desintox' => 'detox',
					default => '',
				};
			} else {
				$post_type_object = get_post_type_object($post_type);
				$label = $post_type_object->labels->singular_name;
				$link = get_post_type_archive_link($post_type);
			}
		} else {
			$post_type_object = get_post_type_object($post_type);
			$label = $post_type_object->labels->singular_name;
			$link = get_post_type_archive_link($post_type);
		}
	}
}
if ( 'evenements' === $main_category->slug ) {

	$event_id             = get_the_ID();
	$event                = get_post( $event_id );
	$use_yearless_format  = apply_filters(
		'tribe_events_event_block_datetime_use_yearless_format',
		(
			tribe_get_start_date( $event_id, false, 'Y' ) === date_i18n( 'Y' )
			&& tribe_get_end_date( $event_id, false, 'Y' ) === date_i18n( 'Y' )
		),
		$event
	);
	$time_format          = tribe_get_time_format();
	$formatted_start_date = tribe_get_start_date( $event_id, false, 'd M Y' );
	$formatted_start_time = tribe_get_start_time( $event_id, $time_format );
	$formatted_end_date   = tribe_get_end_date( $event_id, false, 'd M Y' );
	$formatted_end_time   = tribe_get_end_time( $event_id, $time_format );

	$is_all_day        = tribe_event_is_all_day( $event_id );
	$is_same_day       = $formatted_start_date == $formatted_end_date;
	$is_same_start_end = $formatted_start_date == $formatted_end_date && $formatted_start_time == $formatted_end_time;
}

$chip_style = match ( $main_category->slug ?? null ) {
	'actualites' => 'bg-yellow',
	'dossiers' => 'bg-black',
	default => 'bg-yellow',
};
?>


<article class="article-card card-<?php echo esc_attr( $direction ); ?>">
	<?php if ( $thumbnail ) : ?>
		<a href="<?= esc_url( $permalink ); ?>" class="article-thumbnail">
			<?= $thumbnail; ?>
		</a>
	<?php else: ?>
		<div class="article-thumbnail"></div>
	<?php endif; ?>

	<?php if (!empty($label)): ?>
		<?= render_chip_category_block([
			'label' => esc_html($label),
			'link' => esc_url($link),
			'size' => 'large',
			'style' => esc_attr($chip_style),
			'isLandmark' => ('landmark' === get_post_type($post_object)),
			'icon' => $icon ?? '',
		]); ?>
	<?php endif; ?>

	<div class="article-content">
		<?php if ( 'evenements' === $main_category->slug ) : ?>
			<p class="article-event-date">
				<?php echo esc_html( $formatted_start_date ); ?>
				<?php if ( ! $is_same_start_end ) : ?>
					<?php if ( ! $is_all_day || ! $is_same_day ) : ?>
						au
					<?php endif; ?>

					<?php if ( ! $is_same_day ) : ?>
						<?php echo esc_html( $formatted_end_date ); ?>
					<?php elseif ( ! $is_all_day ) : ?>
						<?php echo esc_html( $formatted_end_time ); ?>
					<?php endif; ?>
				<?php endif; ?>
			</p>
		<?php else : ?>
			<time class="article-date" datetime="<?= esc_attr( date( 'c', strtotime( $date ) ) ); ?>">
				<?= esc_html( $date ); ?>
			</time>
		<?php endif; ?>

		<div class="article-title">
			<a class="as-h5" href="<?= esc_url( $permalink ); ?>">
				<?= esc_html( $title ); ?>
			</a>
		</div>
		<div class="article-terms <?php if (empty($post_terms)) echo 'is-empty'; ?>">
			<?php if ( 'evenements' === $main_category->slug ) : ?>
				<div class="event-info">
					<?php if ( ! empty( tribe_get_city() ) ) : ?>
						<div class="event-info-icon">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path
									d="M12.2427 11.576L8 15.8187L3.75734 11.576C2.91823 10.7369 2.34679 9.66777 2.11529 8.50389C1.88378 7.34 2.0026 6.13361 2.45673 5.03726C2.91086 3.9409 3.6799 3.00384 4.66659 2.34455C5.65328 1.68527 6.81332 1.33337 8 1.33337C9.18669 1.33337 10.3467 1.68527 11.3334 2.34455C12.3201 3.00384 13.0891 3.9409 13.5433 5.03726C13.9974 6.13361 14.1162 7.34 13.8847 8.50389C13.6532 9.66777 13.0818 10.7369 12.2427 11.576ZM8 8.66665C8.35362 8.66665 8.69276 8.52618 8.94281 8.27613C9.19286 8.02608 9.33334 7.68694 9.33334 7.33332C9.33334 6.9797 9.19286 6.64056 8.94281 6.39051C8.69276 6.14046 8.35362 5.99999 8 5.99999C7.64638 5.99999 7.30724 6.14046 7.05719 6.39051C6.80715 6.64056 6.66667 6.9797 6.66667 7.33332C6.66667 7.68694 6.80715 8.02608 7.05719 8.27613C7.30724 8.52618 7.64638 8.66665 8 8.66665Z"
									fill="#575756"/>
							</svg>
							<p><?php echo tribe_get_city(); ?></p>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( tribe_get_start_time() ) ) : ?>
						<div class="event-info-icon">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd"
									  d="M8.00016 1.33337C11.6822 1.33337 14.6668 4.31804 14.6668 8.00004C14.6668 11.682 11.6822 14.6667 8.00016 14.6667C4.31816 14.6667 1.3335 11.682 1.3335 8.00004C1.3335 4.31804 4.31816 1.33337 8.00016 1.33337ZM8.66683 4.66671H7.3335V9.33337H11.3335V8.00004H8.66683V4.66671Z"
									  fill="#575756"/>
							</svg>
							<p><?php echo tribe_get_start_time(); ?></p>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( tribe_get_organizer_email() ) ) : ?>
					<div class="event-info-icon">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd"
								  d="M2.00016 2H14.0002C14.3684 2 14.6668 2.29848 14.6668 2.66667V13.3333C14.6668 13.7015 14.3684 14 14.0002 14H2.00016C1.63197 14 1.3335 13.7015 1.3335 13.3333V2.66667C1.3335 2.29848 1.63197 2 2.00016 2ZM8.04016 7.78867L3.7655 4.15867L2.90216 5.17467L8.04883 9.54467L13.1028 5.17133L12.2308 4.16267L8.04016 7.78867Z"
								  fill="#575756"/>
						</svg>
						<p><?php echo tribe_get_organizer_email(); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php else : ?>
			<?php foreach ($post_terms as $term): ?>
					<?= render_chip_category_block([
						'label' => esc_html($term->name),
						'size' => 'small',
						'style' => 'bg-gray',
						'link' => '',
					]) ?>
			<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</article>
