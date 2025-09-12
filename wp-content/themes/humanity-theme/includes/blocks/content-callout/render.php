<?php
/**
 * Render callback for the Content Callout block.
 *
 * @param array $attributes
 * @return string
 */
function amnesty_render_content_callout_block(array $attributes): string
{
    // ACF priority
    $acf_title = get_field('promo_callout_title');
    $acf_text  = get_field('promo_callout_text');

    // Default attributes from editor block fields or from html template
    $title = !empty($acf_title) ? $acf_title : ($attributes['title'] ?? '');
    $text  = !empty($acf_text) ? $acf_text : ($attributes['text'] ?? '');

    // Else put default values
    $title = !empty($title) ? $title : 'Lorem ipsum dolor sit amet';
    $text  = !empty($text) ? $text : 'Consectetur adipiscing elit. Curabitur nec neque erat. Vestibulum molestie sem augue, ac congue nulla faucibus id. Sed placerat scelerisque tristique.';

    ob_start();
    ?>
	<div class="wp-block-amnesty-content-callout content-callout">
		<div class="container">
			<h3 class="content-callout__title"><?php echo esc_html($title); ?></h3>
			<p class="content-callout__text"><?php echo nl2br(esc_html($text)); ?></p>
		</div>
	</div>
	<?php
    return ob_get_clean();
}
