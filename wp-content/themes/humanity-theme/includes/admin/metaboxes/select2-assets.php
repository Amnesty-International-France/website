<?php
function enqueue_select2_assets() {
	if (get_post_type() === 'post') {
		wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
		wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], null, true);

		wp_add_inline_script('select2', "
			jQuery(document).ready(function($){
				$('#related_posts_selected').select2({
					placeholder: 'Sélectionner des articles',
					maximumSelectionLength: 3,
				});
			});
		");
	}
}
add_action('admin_enqueue_scripts', 'enqueue_select2_assets');
