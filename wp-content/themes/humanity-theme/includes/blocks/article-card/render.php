<?php

declare(strict_types=1);

if (!function_exists('render_article_card_block')) {

	/**
	 * Render Article Card Block
	 *
	 * @param array<string,mixed> $attributes the block attributes
	 *
	 * @return string
	 * @package Amnesty\Blocks
	 *
	 */
	function render_article_card_block($attributes, $content, $block) {
		$direction = $attributes['direction'] ?? 'portrait';
		$thumbnail = $attributes['thumbnail'] ?? '';
		$date = $attributes['date'] ?? date('Y-m-d');
		$title = $attributes['title'] ?? 'Titre par défaut';
		$permalink = $attributes['permalink'] ?? '#';
		$post_terms = $attributes['terms'] ?? [];
		$main_category = $attributes['main_category'] ?? null;
		$is_custom = $attributes['is_custom'] ?? false;

		if ($is_custom) {
			if (is_string($main_category)) {
				$main_category = (object) [
					'name' => $main_category,
					'slug' => sanitize_title($main_category),
					'taxonomy' => 'category',
					'term_id' => 0,
				];
			} elseif (is_array($main_category)) {
				$main_category = (object) $main_category;
			}
			
			if (is_numeric($thumbnail)) {
				$thumbnail = wp_get_attachment_image((int) $thumbnail, 'medium', false, ['class' => 'article-image']);
			}

			$args = [
				'direction' => $direction,
				'title' => $title,
				'permalink' => $permalink,
				'date' => $date,
				'thumbnail' => $thumbnail,
				'main_category' => $main_category,
				'terms' => $post_terms,
			];
		} else {
			if (!empty($attributes['postId']) && !is_admin()) {
				$post = get_post((int) $attributes['postId']);
				$permalink = get_permalink($post);
				$title = get_the_title($post);
				$date = get_the_date('', $post);
				$thumbnail = get_the_post_thumbnail($post->ID, 'medium', ['class' => 'article-image']);
				$post_terms = wp_get_object_terms($post->ID, get_object_taxonomies(get_post_type($post)));
				$main_category = amnesty_get_a_post_term($post->ID);
			} elseif (empty($attributes['postId']) && have_posts()) {
				the_post();
				$permalink = get_permalink();
				$title = get_the_title();
				$date = get_the_date();
				$thumbnail = get_the_post_thumbnail(null, 'medium', ['class' => 'article-image']);
				$post_terms = wp_get_object_terms(get_the_ID(), get_object_taxonomies(get_post_type()));
				$main_category = amnesty_get_a_post_term(get_the_ID());
			}

			$args = [
				'direction' => $direction,
				'title' => $title,
				'permalink' => $permalink,
				'date' => $date,
				'thumbnail' => $thumbnail,
				'main_category' => $main_category,
				'terms' => $post_terms,
			];
		}

		ob_start();
		get_template_part('partials/article-card', null, $args);
		return ob_get_clean();
	}
}

