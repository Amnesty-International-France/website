<?php
$direction = $args['direction'] ?? 'portrait';

if (!empty($args['post'])) {
	$post_id = $args['post_id'] ?? $post->ID;
	$post_object = get_post($post_id);

	$permalink = get_permalink($post_object);
	$title = get_the_title($post_object);
	$date = get_the_date('', $post_object);
	$thumbnail = get_the_post_thumbnail($post_id, 'medium', ['class' => 'article-image']);

	$main_category = amnesty_get_a_post_term($post_id);

	$taxonomies = get_object_taxonomies(get_post_type($post_object));
	$post_terms = wp_get_object_terms($post_id, $taxonomies);

	if ($main_category) {
		$post_terms = array_filter($post_terms, static function ($term) use ($main_category) {
			return !(
				$term->taxonomy === $main_category->taxonomy &&
				$term->term_id === $main_category->term_id
			);
		});
	}
} else {
	$title = $args['title'] ?? 'Titre par défaut';
	$permalink = $args['permalink'] ?? '#';
	$date = $args['date'] ?? date('Y-m-d');
	$thumbnail = $args['thumbnail'] ?? null;
	$main_category = $args['main_category'] ?? null;
	$post_terms = $args['terms'] ?? [];
}

$chip_style = match ($main_category->slug ?? null) {
	'actualites' => 'bg-yellow',
	'dossiers' => 'bg-black',
	default => 'bg-yellow',
};
?>

<article class="article-card card-<?php echo esc_attr($direction); ?>">
	<?php if ($thumbnail): ?>
		<a href="<?= esc_url($permalink); ?>" class="article-thumbnail">
			<?= $thumbnail; ?>
		</a>
	<?php endif; ?>

	<?php if ($main_category): ?>
		<?php
		echo render_block([
			'blockName' => 'amnesty-core/chip-category',
			'attrs' => [
				'label' => $main_category->name,
				'link' => '',
				'size' => 'large',
				'style' => $chip_style,
			],
		]);
		?>
	<?php endif; ?>

	<div class="article-content">
		<time class="article-date" datetime="<?= esc_attr(date('c', strtotime($date))); ?>">
			<?= esc_html($date); ?>
		</time>
		<div class="article-title">
			<a class="as-h5" href="<?= esc_url($permalink); ?>">
				<?= esc_html($title); ?>
			</a>
		</div>
		<div class="article-terms <?php if (empty($post_terms)) echo 'is-empty'; ?>">
			<?php foreach ($post_terms as $term): ?>
				<?= render_block([
					'blockName' => 'amnesty-core/chip-category',
					'attrs' => [
						'label' => $term->name,
						'size' => 'small',
						'style' => 'bg-gray',
						'link' => '',
					],
				]); ?>
			<?php endforeach; ?>
		</div>
	</div>
</article>

