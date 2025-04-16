<?php
/**
 * Template part: Article Card
 *
 * @param WP_Post $post
 * @param string $direction 'portrait' | 'landscape'
 */

$post_type = get_post_type($post);
$classes = ['article-card', "type-$post_type", "direction-$direction"];
$thumbnail = get_the_post_thumbnail_url($post, 'medium');
?>

<article class="<?php echo esc_attr(implode(' ', $classes)); ?>">
	<?php if ($thumbnail): ?>
		<div class="article-card-image">
			<a href="<?php echo get_permalink($post); ?>">
				<img src="<?php echo esc_url($thumbnail); ?>" alt="">
			</a>
		</div>
	<?php endif; ?>

	<div class="article-card-content">
		<h2 class="article-card-title">
			<a href="<?php echo get_permalink($post); ?>">
				<?php echo esc_html(get_the_title($post)); ?>
			</a>
		</h2>

		<?php if ($post_type === 'post'): ?>
			<p class="article-card-date"><?php echo get_the_date('', $post); ?></p>
		<?php elseif ($post_type === 'event'): ?>
			<p class="article-card-meta">📅 Événement à venir</p>
		<?php endif; ?>

		<p class="article-card-excerpt">
			<?php echo esc_html(wp_trim_words(get_the_excerpt($post), 20)); ?>
		</p>
	</div>
</article>
