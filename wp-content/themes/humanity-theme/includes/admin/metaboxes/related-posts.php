<?php

function add_related_posts_metabox() {
	add_meta_box(
		'related-posts',
		'Articles associés',
		'render_related_posts_metabox',
		'post',
		'normal',
		'high'
	);
}
add_action('add_meta_boxes', 'add_related_posts_metabox');

function render_related_posts_metabox($post) {
	$selected_posts = get_post_meta($post->ID, '_related_posts_selected', true);
	$selected_posts = is_array($selected_posts) ? $selected_posts : [];

	$category = get_the_category($post->ID);
	$related_posts = [];

	if ($category) {
		$related_posts = get_posts([
			'post_type'      => 'post',
			'posts_per_page' => -1,
			'cat'            => $category[0]->term_id,
			'post__not_in'   => [$post->ID],
		]);
	}
	?>
	<p>Sélectionner jusqu'à 3 articles associés :</p>
	<select name="related_posts_selected[]" id="related_posts_selected" multiple="multiple" style="width: 100%;">
		<?php foreach ($related_posts as $related_post): ?>
			<option value="<?= esc_attr($related_post->ID); ?>" <?= in_array($related_post->ID, $selected_posts) ? 'selected' : ''; ?>>
				<?= esc_html($related_post->post_title); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}

function save_related_posts_metabox($post_id) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

	if (isset($_POST['related_posts_selected'])) {
		update_post_meta($post_id, '_related_posts_selected', array_map('intval', $_POST['related_posts_selected']));
	} else {
		delete_post_meta($post_id, '_related_posts_selected');
	}
}
add_action('save_post', 'save_related_posts_metabox');
