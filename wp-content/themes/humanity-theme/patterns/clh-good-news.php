<?php

/**
 * Title: CLH Good News
 * Description: Short standfirst and one to four key figures for the Change Their History page.
 * Slug: amnesty/clh-good-news
 * Inserter: no
 */

declare( strict_types = 1 );

$standfirst = get_field( 'clh_good_news_text', get_the_ID() )
	?: __( 'Chaque année, les signatures recueillies pendant la campagne pèsent sur les décisions des autorités. Libérations, procès annulés, peines commuées : voici ce que votre mobilisation a produit.', 'amnesty' );

$figures = get_field( 'clh_good_news_figures', get_the_ID() ) ?: [
	[
		'title' => '52',
		'text'  => __( 'personnes libérées depuis 2012', 'amnesty' ),
	],
	[
		'title' => '7 M',
		'text'  => __( 'signatures recueillies en France', 'amnesty' ),
	],
	[
		'title' => '200',
		'text'  => __( 'pays et territoires mobilisés', 'amnesty' ),
	],
	[
		'title' => '25',
		'text'  => __( 'éditions depuis 2001', 'amnesty' ),
	],
];

?>

<section class="clh-section clh-good-news">
	<h2 class="clh-section-title"><?php esc_html_e( 'Les bonnes nouvelles', 'amnesty' ); ?></h2>
	<p class="clh-good-news-standfirst"><?php echo esc_html( $standfirst ); ?></p>

	<div class="clh-good-news-figures" data-clh-reveal>
		<?php foreach ( array_slice( $figures, 0, 4 ) as $figure ) : ?>
			<div class="key-figure">
				<p class="title">
					<span class="screen-reader-text"><?php echo esc_html( $figure['title'] ); ?></span>
					<span data-clh-count aria-hidden="true"><?php echo esc_html( $figure['title'] ); ?></span>
				</p>
				<p class="text"><?php echo esc_html( $figure['text'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
</section>
