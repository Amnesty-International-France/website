<?php

declare(strict_types=1);

if (! function_exists('render_hero_large_block')) {
    /**
     * Render a hero large block
     *
     * @param array  $attributes the block attributes
     *
     * @package Amnesty\Blocks
     *
     * @return string
     *
     * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- used in view
     */
    function render_hero_large_block(array $attributes = []): string
    {
        if (is_front_page()) {
            return '';
        }

        if (! is_admin()) {
            static $filter_has_been_added = false;
            if (! $filter_has_been_added) {
                add_filter('the_content', 'amnesty_remove_first_hero_from_content', 0);
                $filter_has_been_added = true;
            }
        }

        $title_prefix = $attributes['titlePrefix'] ?? '';
        $page_title = get_the_title();
        $btn_link_text = !empty($attributes['btnLinkText']) ? $attributes['btnLinkText'] : (get_field('btn_link_text') ?? '');
        $btn_link = !empty($attributes['btnLink']) ? $attributes['btnLink'] : (get_field('btn_link') ?? '');
        $button_html = '';

        if (!empty($btn_link) && !empty($btn_link_text)) {
            $button_block = sprintf(
                '<!-- wp:amnesty-core/button {"label":"%s","size":"large","icon":"arrow-right","linkType":"external","externalUrl":"%s"} /-->',
                esc_html($btn_link_text),
                esc_url($btn_link)
            );
            $button_html = do_blocks($button_block);
        }

        ob_start();
        ?>

		<section class="page-hero-block page-hero-block--large">
			<?php
            get_template_part('patterns/featured-image');
        ?>
			<div class="yoast-breadcrumb-wrapper">
				<?php if (function_exists('yoast_breadcrumb')) {
				    yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>');
				} ?>
			</div>
			<div class="page-hero-title-wrapper">
				<div class="container">
					<h1 class="page-hero-title">
						<?php
                        if (! empty($title_prefix)) {
                            echo '<span class="page-hero-title__prefix">' . esc_html($title_prefix) . '</span><br/>';
                        }

        if (! empty($page_title)) {
            echo ' <span class="page-hero-title__title">' . esc_html($page_title) . '</span>';
        }
        ?>
					</h1>
					<?php echo $button_html; ?>
				</div>
			</div>
		</section>
		<?php return ob_get_clean();
    }
}
