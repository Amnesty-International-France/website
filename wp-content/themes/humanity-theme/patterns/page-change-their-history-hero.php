<?php

/**
 * Title: Page Change Their History Hero
 * Description: Outputs the Change Their History page hero.
 * Slug: amnesty/page-change-their-history-hero
 * Inserter: no
 */

declare( strict_types = 1 );

$page_id          = (int) get_the_ID();
$hero_title       = get_field( 'change_their_history_hero_title', $page_id ) ?: get_the_title();
$hero_description = get_field( 'change_their_history_hero_description', $page_id );
$active_campaign  = amnesty_get_active_clh_campaign_for_page( $page_id );
$petitions        = amnesty_get_clh_campaign_petitions();

$collected = 0;
$goal      = 0;

foreach ( $petitions as $petition ) {
	$goal      += (int) get_post_meta( $petition->ID, 'objectif_signatures', true );
	$collected += amnesty_get_petition_signature_count( $petition->ID );
}

$start_date = (string) get_field( 'start_date_highligth_clh', $page_id );
$end_date   = (string) get_field( 'end_date_highlight_clh', $page_id );
$span       = $start_date && $end_date ? (int) ceil( ( strtotime( $end_date ) - strtotime( $start_date ) ) / DAY_IN_SECONDS ) : 0;

if ( $active_campaign ) {
	$remaining = max( 0, strtotime( $end_date ) - time() );
	$days      = (int) ceil( $remaining / DAY_IN_SECONDS );
	$sign_url  = function_exists( 'amnesty_get_clh_petition_tunnel_url' ) ? amnesty_get_clh_petition_tunnel_url() : '';
}

// Le dernier mot du titre passe sur l'aplat jaune : « changez » se lit sur le mot qui change.
$title_words = preg_split( '/\s+/u', trim( $hero_title ) ) ?: [ $hero_title ];
$title_last  = count( $title_words ) > 1 ? array_pop( $title_words ) : '';
$title_head  = implode( ' ', $title_words );

?>

<section class="clh-hero <?php echo $active_campaign ? 'is-running' : 'is-closed'; ?>">
	<div class="clh-hero-inner">
		<div class="clh-hero-stage">
			<div class="clh-hero-statement">
				<h1 class="clh-hero-title">
					<span class="clh-hero-title-line"><?php echo esc_html( $title_head ); ?></span>
					<?php if ( $title_last ) : ?>
						<span class="clh-hero-title-line is-marked"><?php echo esc_html( $title_last ); ?></span>
					<?php endif; ?>
				</h1>

				<?php if ( $hero_description ) : ?>
					<p class="clh-hero-standfirst"><?php echo esc_html( $hero_description ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<?php if ( $active_campaign ) : ?>
	<div class="clh-sticky-bar" data-clh-sticky-bar hidden>
		<div class="clh-sticky-bar-inner">
			<?php
			get_template_part(
				'partials/clh-campaign-indicators',
				null,
				[
					'variant'        => 'sticky',
					'button_variant' => 'bg-black',
					'days'           => $days,
					'span'           => $span,
					'seconds'        => $remaining,
					'collected'      => $collected,
					'goal'           => $goal,
					'sign_url'       => $sign_url,
				]
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php
if ( ! is_admin() ) {
	add_filter( 'the_content', 'amnesty_remove_first_hero_from_content', 0 );
}
