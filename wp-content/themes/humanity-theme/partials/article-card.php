<?php
$direction = $args['direction'] ?? 'portrait';

$post_id = $post->ID;
$permalink = get_permalink($post);
$title = get_the_title($post);
$date = get_the_date('', $post);
$thumbnail = get_the_post_thumbnail($post_id, 'medium', ['class' => 'article-image']);

$categories = get_the_category($post_id);
$tags = get_the_tags($post_id);

$main_category = amnesty_get_a_post_term(get_the_ID());

$taxonomies = get_object_taxonomies(get_post_type());
$post_terms = wp_get_object_terms(get_the_ID(), $taxonomies);

if ($main_category) {
	$post_terms = array_filter($post_terms, static function ($term) use ($main_category) {
		return !(
			$term->taxonomy === $main_category->taxonomy &&
			$term->term_id === $main_category->term_id
		);
	});
}

$chip_style = match ($main_category->slug) {
	'actualites' => 'bg-yellow',
	default => 'bg-black',
};
?>

<article class="article-card card-<?php echo $direction ?>">
	<?php if ($thumbnail): ?>
		<a href="<?= esc_url($permalink); ?>" class="article-thumbnail">
			<?= $thumbnail; ?>
		</a>
	<?php endif; ?>
	<?php
	if ($main_category) {
		echo render_block([
			'blockName' => 'amnesty-core/chip-category',
			'attrs' => [
				'label' => $main_category->name,
				'link' => '',
				'size' => 'large',
				'style' => $chip_style,
			],
		]);
	}
	?>
	<div class="article-content">
		<time class="article-date" datetime="<?= esc_attr(get_the_date('c', $post)); ?>">
			<?= esc_html($date); ?>
		</time>
		<div class="article-title"><a class="as-h5" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
		<div class="article-terms <?php if (empty($post_terms)) echo 'is-empty' ?>">
			<?php foreach ($post_terms as $term): ?>
				<?php
				echo render_block([
					'blockName' => 'amnesty-core/chip-category',
					'attrs' => [
						'label' => $term->name,
						'size' => 'small',
						'style' => 'bg-gray',
						'link' => ''
					],
				]);
				?>
			<?php endforeach; ?>
		</div>
	</div>
</article>
