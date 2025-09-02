<?php

declare(strict_types=1);

/**
 * Title: Single Chronicle Content
 * Description: Output the content of a single chronicle post
 * Slug: amnesty/single-chronicle-content
 * Inserter: no
 */

$is_promo_context = $args['is_promo_context'] ?? false;
$open_cover_image = get_field( 'cover_image_with_magazine_open' );
$cover_image = get_field( 'cover_image' );
$month = get_field( 'publication_month' );
if ($month) {
	$date_objet = DateTime::createFromFormat('!m', $month);
	$month = date_i18n('F', $date_objet->getTimestamp());
}
$year = get_field( 'publication_year' );
$summary_title = get_field('summary_title');
$summary_content = get_field('summary_content');

?>

<?php
if ( $is_promo_context ) {
    $open_cover_image = get_field( 'cover_image_with_magazine_open' );
    if ( $open_cover_image ) : ?>
        <figure class="promo-open-magazine">
			<img src="<?php echo esc_url( $open_cover_image['sizes']['medium_large'] ); ?>" alt="<?php echo esc_attr( $open_cover_image['alt'] ); ?>">
        </figure>
    <?php endif;
}
?>

<!-- wp:group {"tagName":"section","className":"single-chronicle-content container has-gutter","layout":{"type":"constrained"}} -->
<section class="wp-block-group single-chronicle__content container has-gutter">
	<!-- wp:columns {"className":"columns"} -->
		<!-- wp:column {"width":"33.33%"} -->
		<aside class="single-chronicle__content__aside u-sticky">
			<?php if ( $cover_image ) : ?>
			<figure class="cover-image">
				<img src="<?php echo esc_url( $cover_image['sizes']['medium_large'] ); ?>"
					 alt="<?php echo esc_attr( $cover_image['alt'] ); ?>"
				/>
			</figure>
			<?php endif; ?>
			<?php
			echo do_blocks('
			<!-- wp:amnesty-core/button {"label":"Abonnez-vous pour 3€/mois","size":"medium","icon":"arrow-right","linkType":"external","externalUrl":"https://soutenir.amnesty.fr/b?cid=365&lang=fr_FR&reserved_originecode=null","alignment":"center"} /-->
			<!-- wp:amnesty-core/button {"label":"Voir les anciens numéros","size":"small","icon":"arrow-right","style":"bg-white outline-black","linkType":"internal","alignment":"center"} /-->
			');
			?>
		</aside>
		<!-- /wp:column -->

		<!-- wp:column {"width":"66.66%"} -->
		<article class="single-chronicle__content__summary">
			<span class="chip chip--bg-black"><?= $month ?? '' ?>&nbsp;<?= $year ?? '' ?></span>
			<h2><?= $summary_title ?? '' ?></h2>
			<div><?= $summary_content ?? '' ?></div>
		</article>
		<!-- /wp:column -->
	<!-- /wp:columns -->
</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"container has-gutter","layout":{"type":"constrained"}} -->
<section class="wp-block-group container has-gutter related-posts">
	<?php
	echo do_blocks('
	<!-- wp:amnesty-core/related-posts {"title":"ET AUSSI", "nb_posts": 2, "display": "chronique"} /-->
	');
	?>
</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"container has-gutter","layout":{"type":"constrained"}} -->
<section class="wp-block-group container has-gutter">
	<?php
	echo do_blocks('
	<!-- wp:pattern {"slug":"amnesty/subscribe-to-the-chronicle-banner"} /-->
	');
	?>
</section>
<!-- /wp:group -->
