<?php

declare( strict_types = 1 );

// Bandeau jaune des signatures, en campagne, ou du total de l'année une fois la campagne terminée.
// `sign_button` affiche « Je signe » en campagne : le bandeau du haut s'en passe, celui du bas le
// garde.
$sign_button = (bool) ( $args['sign_button'] ?? true );

$page_id         = (int) get_the_ID();
$active_campaign = amnesty_get_active_clh_campaign_for_page( $page_id );

$petitions = amnesty_get_clh_campaign_petitions();
$collected = 0;
$goal      = 0;

foreach ( $petitions as $petition ) {
	$goal      += (int) get_post_meta( $petition->ID, 'objectif_signatures', true );
	$collected += amnesty_get_petition_signature_count( $petition->ID );
}

$start_date = (string) get_field( 'start_date_highligth_clh', $page_id );
$end_date   = (string) get_field( 'end_date_highlight_clh', $page_id );

// Hors campagne, le total de l'année prend la place du compteur.
if ( ! $active_campaign ) {
	get_template_part(
		'partials/clh-closed-band',
		null,
		[
			'year'       => $end_date ? (int) wp_date( 'Y', strtotime( $end_date ) ) : (int) wp_date( 'Y' ),
			'signatures' => amnesty_get_clh_final_signatures( $page_id, $collected ),
		]
	);

	return;
}

$remaining = max( 0, strtotime( $end_date ) - time() );
$sign_url  = function_exists( 'amnesty_get_clh_petition_tunnel_url' ) ? amnesty_get_clh_petition_tunnel_url() : '';

?>

<div class="clh-band" data-clh-reveal>
	<?php
	get_template_part(
		'partials/clh-campaign-indicators',
		null,
		[
			'variant'        => 'banner',
			'button_variant' => 'outline-black',
			'days'           => (int) ceil( $remaining / DAY_IN_SECONDS ),
			'span'           => $start_date ? (int) ceil( ( strtotime( $end_date ) - strtotime( $start_date ) ) / DAY_IN_SECONDS ) : 0,
			'seconds'        => $remaining,
			'collected'      => $collected,
			'goal'           => $goal,
			'sign_url'       => $sign_button ? $sign_url : '',
		]
	);
	?>
</div>
