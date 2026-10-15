<?php

/**
 * Title: CLH Victories Timeline
 * Description: Year axis of the Change Their History victories, with one panel per victory.
 * Slug: amnesty/clh-victories-timeline
 * Inserter: no
 */

declare( strict_types = 1 );

$first_year = 2013;
$last_year  = (int) wp_date( 'Y' );

// Une année peut porter plusieurs victoires. Chaque carte suit le format des exemples envoyés par
// Amnesty le 28/09 : le nom, sa qualification, la citation, puis un paragraphe qui dit la
// situation, la mobilisation et la victoire. Les cartes de 2016, 2020 et 2022 reprennent ces
// exemples mot pour mot ; les autres sont des textes de maquette, résumés des faits publiés sur
// https://www.amnesty.org/en/get-involved/write-for-rights/, et leurs citations sont rédigées
// pour tenir la volumétrie : Amnesty doit les remplacer avant toute mise en ligne.
// `photo` est le nom du fichier dans `assets/images/clh-victories/` : sans photo, la case le dit.
$victories = [
	2016 => [
		[
			'nom'           => 'Phyoe Phyoe Aung',
			'qualification' => 'Ancienne prisonnière d\'opinion au Myanmar',
			'pays'          => 'Myanmar',
			'texte'         => 'Phyoe Phyoe, étudiante birmane arrêtée en 2015 pour avoir manifesté contre une loi sur l’éducation, risquait jusqu’à 9 ans de prison. Grâce à une mobilisation internationale portée par Amnesty International, avec plus de 394 000 messages et 50 000 signatures en France, elle a été libérée en avril 2016.',
			'citation'      => 'Je tiens à remercier du fond du cœur chacune et chacun d’entre vous, pas simplement pour vos efforts en faveur de ma libération, mais aussi pour nous avoir permis de garder espoir et foi en nos opinions.',
		],
	],
	2017 => [
		[
			'nom'           => 'Chelsea Manning',
			'qualification' => 'Lanceuse d\'alerte, libérée après sept ans de détention',
			'pays'          => 'États-Unis',
			'texte'         => 'Analyste du renseignement dans l\'armée américaine, elle transmet à WikiLeaks des documents sur des exactions commises en Irak et en Afghanistan. Elle est condamnée à 35 ans de prison en 2013, et libérée en mai 2017.',
			'citation'      => 'Vos lettres arrivaient par sacs entiers. Dans ma cellule, elles me rappelaient chaque jour que je n\'étais pas oubliée.',
		],
	],
	2018 => [
		[
			'nom'           => 'Mahadine',
			'qualification' => 'Cyberactiviste, libéré au Tchad',
			'pays'          => 'Tchad',
			'texte'         => 'Cyberactiviste, il publie en 2016 des vidéos sur Facebook qui dénoncent la gestion des fonds publics. Arrêté, accusé d\'atteinte à l\'ordre constitutionnel, il risque la prison à vie. Il est libéré en avril 2018.',
			'citation'      => 'On m\'a enfermé pour une vidéo. Ce sont des milliers de signatures venues de partout qui ont fini par m\'ouvrir la porte.',
		],
	],
	2019 => [
		[
			'nom'           => 'Gulzar Duishenova',
			'qualification' => 'Militante pour les droits des personnes handicapées',
			'pays'          => 'Kirghizistan',
			'texte'         => 'Paralysée des jambes après un accident de voiture, elle se bat pour que les personnes handicapées puissent se déplacer, travailler et voter dans un pays où presque rien ne leur est accessible. En mars 2019, le Kirghizistan signe la Convention relative aux droits des personnes handicapées.',
			'citation'      => 'Pendant des années, on ne m\'a pas écoutée. Avec vous, notre voix est devenue impossible à ignorer pour le gouvernement.',
		],
	],
	2020 => [
		[
			'nom'           => 'Magai Matiop Ngong',
			'qualification' => 'Libéré du quartier des condamnés à mort',
			'pays'          => 'Soudan du Sud',
			'texte'         => 'À 15 ans, Magai a été condamné à mort par pendaison au Soudan du Sud après avoir assuré seul sa défense. Il a passé quatre ans dans le couloir de la mort. A nos côtés, plus de 765 000 personnes - dont plus de 70 000 en France – ont résisté et dit « non » à ce châtiment cruel, finalement levé en avril 2020.',
			'citation'      => 'Merci de m’avoir sauvé la vie',
			'photo'         => '2020-magai-matiop-ngong.jpg',
		],
	],
	2021 => [
		[
			'nom'           => 'Germain Rukuki',
			'qualification' => 'Défenseur des droits humains, libéré au Burundi',
			'pays'          => 'Burundi',
			'texte'         => 'Défenseur des droits humains, engagé contre la torture, il est arrêté en 2017. Un an plus tard, il est condamné à 32 ans de prison pour « rébellion », sur la seule base de son militantisme. Il est libéré en juin 2021.',
			'citation'      => 'J\'ai vu grandir mon fils sur des photos. Vos messages m\'ont tenu debout pendant plus de quatre ans de détention.',
		],
	],
	2022 => [
		[
			'nom'           => 'Bernardo Caal Xol',
			'qualification' => 'Défenseur des droits du peuple maya q’eqchi',
			'pays'          => 'Guatemala',
			'texte'         => 'Parce qu’il s’est battu pour les droits des populations autochtones face à de vastes projets hydroélectriques détournant un fleuve sacré au Guatemala, Bernardo risquait plus de 7 ans de prison. Face à cette injustice, plus de 500 000 personnes se sont mobilisées à nos côtés en 2021 pour sa libération. Victoire : en mars 2022, Bernardo a été libéré !',
			'citation'      => 'Moi, Bernardo Caal Xol, membre du peuple maya q’eqchi du Guatemala, je tiens à remercier chacune et chacun d’entre vous. Vous m’avez donné espoir pour la justice, la liberté et l’égalité qui doivent prévaloir pour tous les peuples et toutes les nations.',
		],
	],
	2024 => [
		[
			'nom'           => 'Aleksandra Skotchilenko',
			'qualification' => 'Artiste, libérée d\'une colonie pénitentiaire russe',
			'pays'          => 'Russie',
			'texte'         => 'Artiste à Saint-Pétersbourg, elle remplace en 2022 des étiquettes de prix d\'un supermarché par des messages contre la guerre en Ukraine. Condamnée à sept ans de colonie pénitentiaire, elle est libérée en août 2024.',
			'citation'      => 'Sept ans pour cinq petits papiers. Chaque carte reçue me disait que ce que j\'avais fait comptait, et que je n\'étais pas seule.',
			'photo'         => '2024-aleksandra-skotchilenko.jpg',
		],
		[
			'nom'           => 'Rita Karasartova',
			'qualification' => 'Défenseuse des droits humains, acquittée au Kirghizistan',
			'pays'          => 'Kirghizistan',
			'texte'         => 'Défenseuse des droits humains, elle est arrêtée pour s\'être opposée publiquement à un nouvel accord frontalier, et poursuivie pour « tentative de renversement violent du gouvernement ». Elle est acquittée en juin 2024.',
			'citation'      => 'Plus de 400 000 personnes ont écrit pour moi. Au tribunal, je savais que je n\'étais pas seule à me tenir debout.',
		],
	],
];

if ( ! $victories ) {
	return;
}

$years            = range( $first_year, $last_year );
$victory_years    = array_values( array_filter( $years, static fn ( $timeline_year ) => isset( $victories[ $timeline_year ] ) ) );
$selected_year    = end( $victory_years );
$total            = array_sum( array_map( 'count', $victories ) );
$last_year_digits = strlen( (string) $last_year );

?>

<section class="clh-section clh-timeline" id="clh-victoires">
	<header class="clh-timeline-head">
		<h2 class="clh-section-title"><?php esc_html_e( 'Ils vous disent merci', 'amnesty' ); ?></h2>
		<p class="clh-timeline-intro">
			<?php
			printf(
				/* translators: 1: nombre de victoires, 2: nombre d'éditions. */
				esc_html__( '%1$s victoires en %2$s éditions : des personnes libérées, une peine de mort annulée, un droit reconnu.', 'amnesty' ),
				esc_html( number_format_i18n( $total ) ),
				esc_html( number_format_i18n( count( $years ) ) )
			);
			?>
		</p>
	</header>

	<div class="clh-timeline-dial" data-clh-reveal="soon" style="--clh-timeline-years: <?php echo esc_attr( (string) count( $years ) ); ?>;">
		<ol class="clh-timeline-years">
		<?php foreach ( $years as $index => $timeline_year ) : ?>
			<?php $year_victories = $victories[ $timeline_year ] ?? []; ?>
			<li class="clh-timeline-year <?php echo $year_victories ? 'has-victory' : 'is-empty'; ?><?php echo count( $year_victories ) > 1 ? ' has-victories' : ''; ?>" style="--clh-timeline-column: <?php echo esc_attr( (string) ( $index + 1 ) ); ?>;">
				<?php if ( ! $year_victories ) : ?>
					<span class="clh-timeline-mark"><?php echo esc_html( (string) $timeline_year ); ?></span>
				<?php else : ?>
					<label class="clh-timeline-mark">
						<input
							type="radio"
							name="clh-victory"
							id="clh-victory-<?php echo esc_attr( (string) $timeline_year ); ?>"
							class="clh-timeline-input"
							<?php checked( $timeline_year, $selected_year ); ?>
						>
						<span aria-hidden="true"><?php echo esc_html( (string) $timeline_year ); ?></span>
						<span class="screen-reader-text">
							<?php
							printf(
							/* translators: 1: noms des personnes, 2: année. */
								esc_html__( 'Voir la victoire de %1$s en %2$s', 'amnesty' ),
								esc_html( implode( ', ', array_column( $year_victories, 'nom' ) ) ),
								esc_html( (string) $timeline_year )
							);
							?>
						</span>
					</label>

					<div class="clh-timeline-panel">
						<?php
						// Plusieurs victoires la même année : un onglet par personne, la première ouverte.
						?>
						<?php if ( count( $year_victories ) > 1 ) : ?>
							<div class="clh-timeline-picks" role="radiogroup" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: année. */ __( 'Victoires de %s', 'amnesty' ), $timeline_year ) ); ?>">
								<?php foreach ( $year_victories as $rank => $victory ) : ?>
									<label class="clh-timeline-pick">
										<input
											type="radio"
											name="clh-victory-<?php echo esc_attr( (string) $timeline_year ); ?>"
											class="clh-timeline-pick-input"
											<?php checked( 0, $rank ); ?>
										>
										<span><?php echo esc_html( $victory['nom'] ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<?php foreach ( $year_victories as $victory ) : ?>
							<article class="clh-timeline-slide">
								<figure class="clh-timeline-panel-figure">
									<?php if ( ! empty( $victory['photo'] ) ) : ?>
										<img
											class="clh-timeline-panel-photo"
											src="<?php echo esc_url( get_theme_file_uri( 'assets/images/clh-victories/' . $victory['photo'] ) ); ?>"
											alt="<?php echo esc_attr( $victory['nom'] ); ?>"
											width="560"
											height="700"
											loading="lazy"
											decoding="async"
										>
									<?php else : ?>
										<span class="clh-timeline-panel-photo is-missing">
											<?php esc_html_e( 'Photo à fournir par Amnesty', 'amnesty' ); ?>
										</span>
									<?php endif; ?>
									<figcaption class="clh-timeline-panel-stamp"><?php esc_html_e( 'Victoire', 'amnesty' ); ?></figcaption>
								</figure>

								<div class="clh-timeline-panel-body">
									<h3 class="clh-timeline-panel-name"><span><?php echo esc_html( $victory['nom'] ); ?></span></h3>
									<p class="clh-timeline-panel-place"><?php echo esc_html( $victory['pays'] ); ?></p>
									<?php if ( ! empty( $victory['qualification'] ) ) : ?>
										<p class="clh-timeline-panel-role"><?php echo esc_html( $victory['qualification'] ); ?></p>
									<?php endif; ?>
									<?php
									// Une citation longue passe à un corps plus petit.
									?>
									<blockquote class="clh-timeline-panel-quote<?php echo mb_strlen( $victory['citation'] ) > 180 ? ' is-long' : ''; ?>">
										<p>&laquo;&nbsp;<?php echo esc_html( $victory['citation'] ); ?>&nbsp;&raquo;</p>
									</blockquote>
									<p class="clh-timeline-panel-text"><?php echo esc_html( $victory['texte'] ); ?></p>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
		</ol>

		<p class="clh-timeline-watermark" aria-hidden="true">
			<?php for ( $rank = 0; $rank < $last_year_digits; $rank += 1 ) : ?>
				<span class="clh-roll"><span class="clh-roll-strip">
				<?php
				foreach ( $years as $timeline_year ) :
					?>
					<span class="clh-roll-digit"><?php echo esc_html( ( (string) $timeline_year )[ $rank ] ); ?></span>
					<?php
				endforeach;
				?>
				</span></span>
			<?php endfor; ?>
		</p>
	</div>
</section>
