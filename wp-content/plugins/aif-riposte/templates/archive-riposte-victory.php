<?php

/**
 * Riposte Victory archive template.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}
add_filter('body_class', static function (array $classes): array {
	$classes[] = 'archive';
	$classes[] = 'post-type-archive';
	$classes[] = 'post-type-archive-riposte_victory';

	return $classes;
});
get_header();

global $wp_query;

add_filter('get_the_terms', 'amnesty_limit_post_terms_results_for_archive');
?>

<main class="wp-block-group">
	<div class="wp-block-group section">
		<div class="wp-block-group container has-gutter">
			<div class="wp-block-group">
				<?php
				echo do_blocks('<!-- wp:pattern {"slug":"amnesty/archive-hero"} /-->'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

                $chapo_text = get_option('aif_riposte_archive_chapo', '');

                if ($chapo_text) {
                    echo '<div class="chapo aif-riposte-archive-chapo">';
                    ?><p class="text"><?php echo wp_kses_post(nl2br(stripslashes($chapo_text))); ?></p><?php
                    echo '</div>';
                }
				require AIF_RIPOSTE_PATH . 'templates/partials/archive-filters.php';
				?>
			</div>
		</div>
	</div>

	<div class="wp-block-group container has-gutter">
		<section class="wp-block-group">
			<div class="wp-block-query">
				<div class="wp-block-group news-section section section--small section--tinted has-gutter">
					<div class="wp-block-group postlist">
						<?php if (have_posts()) : ?>
							<div class="aif-riposte-grid">
								<?php
                                $card_index = 0;
								while (have_posts()) :
									the_post();

									aif_riposte_render_card(null, $card_index);
                                    $card_index++;
								endwhile;
								?>
							</div>
						<?php else : ?>
							<div class="wp-block-query-no-results">
								<p><?php esc_html_e('Nous n’avons pas trouvé d’articles correspondant à vos critères de recherche.', 'aif-riposte'); ?></p>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<?php if ((int) $wp_query->max_num_pages > 1) : ?>
					<div class="wp-block-group section section--small aif-riposte-load-more-wrapper">
						<button
							type="button"
							class="wp-block-button__link aif-riposte-load-more"
							data-aif-riposte-load-more
							data-page="2"
						>
							<?php esc_html_e('Charger plus', 'aif-riposte'); ?>
						</button>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
</main>

<?php
remove_filter('get_the_terms', 'amnesty_limit_post_terms_results_for_archive');

echo do_blocks(
	'<!-- wp:template-part {"slug":"footer","theme":"humanity-theme","area":"footer","className":"amnesty-footer-template-part"} /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();