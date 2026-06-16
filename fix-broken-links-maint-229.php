<?php

/**
 * MAINT-229 — Traitement des liens cassés (erreurs 404) assignés à l'équipe technique.
 *
 * Réécrit le `post_content` des contenus publiés pour corriger les liens 404 listés
 * dans l'audit SEO (lignes « Les Tilleuls » du CSV). Trois catégories :
 *
 *   CAT 1 — Remplacement de lien (8 mappings ancien slug repère/dossier -> nouveau).
 *   CAT 2 — Anciennes fiches pays `?p=ID&post_type=fiche_pays` -> permalink `/pays/{slug}/`
 *           résolu à l'exécution à partir du nom du pays (ancre).
 *   CAT 3 — Liens d'obfuscation email Cloudflare (`/cdn-cgi/l/email-protection`).
 *           Décodés et remplacés par un vrai `mailto:` lorsqu'ils sont réellement
 *           présents dans le contenu stocké.
 *
 * Usage (WP-CLI) :
 *   wp eval-file fix-broken-links-maint-229.php            # dry-run (par défaut, n'écrit rien)
 *   wp eval-file fix-broken-links-maint-229.php live       # applique réellement les changements
 *
 * Un log détaillé est écrit dans fixed_links_maint229.txt.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Mode d'exécution : dry-run par défaut, « live » uniquement si demandé explicitement.
 * `wp eval-file` expose les arguments positionnels dans $args.
 */
$fix_links_live = ( isset( $args ) && is_array( $args ) && in_array( 'live', $args, true ) )
	|| 'live' === getenv( 'FIX_LINKS_MODE' );

/**
 * CAT 1 — Mappings directs : ancienne URL absolue => nouvelle URL absolue.
 */
$cat1_mappings = [
	'https://www.amnesty.fr/dossiers/investiture-de-trump/'                       => 'https://www.amnesty.fr/dossiers/ma-vie-sous-trump-recits-amerique-en-resistance/',
	'https://www.amnesty.fr/reperes/convention-contre-la-torture/'                => 'https://www.amnesty.fr/reperes/torture-traitements-cruels-degradants-inhumains/',
	'https://www.amnesty.fr/reperes/crimes-de-guerre-et-crimes-contre-lhumanite/' => 'https://www.amnesty.fr/reperes/terrorisme-crime-de-guerre-crime-contre-lhumanite-que-dit-le-droit-international/',
	'https://www.amnesty.fr/reperes/droit-asile/'                                 => 'https://www.amnesty.fr/reperes/definitions-refugie-migrant-demandeur-asile/',
	'https://www.amnesty.fr/reperes/droit-des-femmes/'                            => 'https://www.amnesty.fr/reperes/8-mars-journee-internationale-des-femmes/',
	'https://www.amnesty.fr/reperes/migrant/'                                     => 'https://www.amnesty.fr/reperes/definitions-refugie-migrant-demandeur-asile/',
	'https://www.amnesty.fr/reperes/traite-sur-le-commerce-des-armes-tca/'        => 'https://www.amnesty.fr/reperes/traite-commerce-armes-explication/',
	'https://www.amnesty.fr/reperes/traitements-cruels-inhumains-ou-degradants/'  => 'https://www.amnesty.fr/reperes/torture-traitements-cruels-degradants-inhumains/',
];

/**
 * CAT 2 — Anciennes fiches pays : ID de l'ancien site => nom du pays (ancre).
 * Le permalink cible est résolu à l'exécution à partir du nom du pays.
 */
$cat2_country_anchors = [
	8331 => 'Togo',
	8333 => 'Russie',
	8339 => 'Inde',
	8374 => 'Algérie',
	8376 => 'Cambodge',
	8388 => 'Pakistan',
	8394 => 'Syrie',
	8398 => 'France',
	8417 => 'Égypte',
	8442 => 'Guinée',
	8466 => "Côte d'Ivoire",
	8468 => 'Philippines',
	8470 => 'République Démocratique du Congo',
	8512 => 'Mexique',
	8518 => 'Burkina Faso',
	8538 => 'Sénégal',
	8554 => 'Chine',
	8576 => 'Kazakhstan',
	8582 => 'Colombie',
	8584 => 'Yémen',
	8586 => 'Kenya',
	8616 => 'Tunisie',
	8618 => 'Burundi',
	8640 => 'Hong Kong',
	8642 => 'Jamaïque',
	8644 => 'Liberia',
	8652 => 'Brunéi Darussalam',
	8654 => 'Nauru',
	8660 => 'Luxembourg',
];

/**
 * Résout un nom de pays vers le permalink de la fiche pays publiée correspondante.
 * Tente d'abord la correspondance par slug, puis par titre.
 *
 * @return string|null Permalink, ou null si aucune fiche pays publiée ne correspond.
 */
function maint229_resolve_country_url( string $country ): ?string {
	$slug = sanitize_title( $country );

	$post = get_page_by_path( $slug, OBJECT, 'fiche_pays' );
	if ( $post instanceof WP_Post && 'publish' === $post->post_status ) {
		return get_permalink( $post );
	}

	$query = new WP_Query(
		[
			'post_type'              => 'fiche_pays',
			'post_status'            => 'publish',
			'title'                  => $country,
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	if ( ! empty( $query->posts ) ) {
		return get_permalink( $query->posts[0] );
	}

	return null;
}

/**
 * Construit la table de remplacement CAT 2 : ancienne URL `?p=ID...` => permalink résolu.
 * Logge les pays non résolus (fiche pays absente) afin de les traiter manuellement.
 *
 * @return array{0: array<string,string>, 1: array<int,string>}
 */
function maint229_build_country_map( array $anchors, $log_file ): array {
	$map        = [];
	$unresolved = [];

	foreach ( $anchors as $id => $country ) {
		$url = maint229_resolve_country_url( $country );

		if ( null === $url ) {
			$unresolved[ $id ] = $country;
			fwrite( $log_file, "  [CAT2][NON RÉSOLU] p=$id ($country) : aucune fiche pays publiée trouvée." . PHP_EOL );
			continue;
		}

		$map[ (string) $id ] = $url;
	}

	return [ $map, $unresolved ];
}

/**
 * Décode une adresse email obfusquée par Cloudflare (algorithme XOR sur la chaîne hex).
 */
function maint229_cf_decode_email( string $encoded ): string {
	$key   = hexdec( substr( $encoded, 0, 2 ) );
	$email = '';

	for ( $i = 2, $len = strlen( $encoded ); $i < $len; $i += 2 ) {
		$email .= chr( hexdec( substr( $encoded, $i, 2 ) ) ^ $key );
	}

	return $email;
}

/**
 * CAT 1 — Remplace une URL absolue par une autre, en gérant le slash final
 * et en exigeant une frontière pour ne pas casser une URL plus longue.
 */
function maint229_replace_absolute_url( string $content, string $old, string $new, int &$count ): string {
	$old_no_slash = rtrim( $old, '/' );
	$new_canonical = rtrim( $new, '/' ) . '/';

	// Slash final optionnel, suivi d'une frontière (guillemet, balise, espace, fin, etc.).
	$pattern = '~' . preg_quote( $old_no_slash, '~' ) . '/?(?=["\'\\\\\s<>)\]}#?]|$)~';

	return (string) preg_replace_callback(
		$pattern,
		static function () use ( $new_canonical, &$count ) {
			$count++;
			return $new_canonical;
		},
		$content
	);
}

/**
 * CAT 2 — Remplace une ancienne URL de fiche pays `?p=ID&post_type=fiche_pays`,
 * en tolérant les différents encodages de l'esperluette (&, &amp;, &, &#038;).
 */
function maint229_replace_country_url( string $content, string $id, string $new, int &$count ): string {
	$amp     = '(?:&|&amp;|\\\\u0026|&#0?38;)';
	$pattern = '~https://www\.amnesty\.fr/\?p=' . preg_quote( $id, '~' ) . $amp . 'post_type=fiche_pays~';

	return (string) preg_replace_callback(
		$pattern,
		static function () use ( $new, &$count ) {
			$count++;
			return $new;
		},
		$content
	);
}

/**
 * CAT 3 — Décode et remplace les liens d'obfuscation email Cloudflare réellement
 * présents dans le contenu. Couvre le lien `email-protection#HEX` et le span
 * `__cf_email__` porteur de `data-cfemail`.
 */
function maint229_replace_cf_emails( string $content, int &$count ): string {
	// Lien : href="...email-protection#HEX"
	$content = (string) preg_replace_callback(
		'~https?://[^"\']*?/cdn-cgi/l/email-protection#([0-9a-fA-F]+)~',
		static function ( array $m ) use ( &$count ) {
			$count++;
			return 'mailto:' . maint229_cf_decode_email( $m[1] );
		},
		$content
	);

	// Lien relatif éventuel : href="/cdn-cgi/l/email-protection#HEX"
	$content = (string) preg_replace_callback(
		'~/cdn-cgi/l/email-protection#([0-9a-fA-F]+)~',
		static function ( array $m ) use ( &$count ) {
			$count++;
			return 'mailto:' . maint229_cf_decode_email( $m[1] );
		},
		$content
	);

	return $content;
}

/**
 * Applique les trois catégories de corrections à une chaîne de contenu.
 * Réutilisé pour le `post_content` des posts et la `description` des termes.
 *
 * @return array{0: string, 1: array<string,int>} Contenu réécrit + compteurs par catégorie.
 */
function maint229_apply_all( string $content, array $cat1, array $cat2 ): array {
	$counts = [ 'cat1' => 0, 'cat2' => 0, 'cat3' => 0 ];

	foreach ( $cat1 as $old => $new ) {
		$content = maint229_replace_absolute_url( $content, $old, $new, $counts['cat1'] );
	}

	foreach ( $cat2 as $id => $url ) {
		$content = maint229_replace_country_url( $content, $id, $url, $counts['cat2'] );
	}

	$content = maint229_replace_cf_emails( $content, $counts['cat3'] );

	return [ $content, $counts ];
}

/**
 * Applique les trois catégories de corrections à un post et le met à jour si besoin.
 *
 * @return array<string,int> Compteurs de remplacements par catégorie pour ce post.
 */
function maint229_process_post( WP_Post $post, array $cat1, array $cat2, bool $live, $log_file ): array {
	$content = $post->post_content;

	if ( '' === $content ) {
		return [ 'cat1' => 0, 'cat2' => 0, 'cat3' => 0 ];
	}

	[ $new_content, $counts ] = maint229_apply_all( $content, $cat1, $cat2 );

	if ( array_sum( $counts ) > 0 && $new_content !== $content ) {
		fwrite(
			$log_file,
			sprintf(
				'  [#%d] %s — CAT1:%d CAT2:%d CAT3:%d%s',
				$post->ID,
				get_permalink( $post ),
				$counts['cat1'],
				$counts['cat2'],
				$counts['cat3'],
				PHP_EOL
			)
		);

		if ( $live ) {
			$result = wp_update_post(
				[
					'ID'           => $post->ID,
					'post_content' => $new_content,
				],
				true
			);

			if ( is_wp_error( $result ) ) {
				fwrite( $log_file, "    /!\\ ÉCHEC wp_update_post : " . $result->get_error_message() . PHP_EOL );
				WP_CLI::warning( "Échec de mise à jour du post {$post->ID} : " . $result->get_error_message() );
			}
		}
	}

	return $counts;
}

/**
 * Applique les corrections à la description d'un terme (affichée sur les archives
 * via wp_kses_post($term->description), cf. patterns/archive-heading.php) et met
 * à jour le terme si besoin.
 *
 * @return array<string,int> Compteurs de remplacements par catégorie pour ce terme.
 */
function maint229_process_term( WP_Term $term, array $cat1, array $cat2, bool $live, $log_file ): array {
	$content = (string) $term->description;

	if ( '' === $content ) {
		return [ 'cat1' => 0, 'cat2' => 0, 'cat3' => 0 ];
	}

	[ $new_content, $counts ] = maint229_apply_all( $content, $cat1, $cat2 );

	if ( array_sum( $counts ) > 0 && $new_content !== $content ) {
		fwrite(
			$log_file,
			sprintf(
				'  [term #%d] %s/%s — CAT1:%d CAT2:%d CAT3:%d%s',
				$term->term_id,
				$term->taxonomy,
				$term->slug,
				$counts['cat1'],
				$counts['cat2'],
				$counts['cat3'],
				PHP_EOL
			)
		);

		if ( $live ) {
			$result = wp_update_term(
				$term->term_id,
				$term->taxonomy,
				[ 'description' => $new_content ]
			);

			if ( is_wp_error( $result ) ) {
				fwrite( $log_file, "    /!\\ ÉCHEC wp_update_term : " . $result->get_error_message() . PHP_EOL );
				WP_CLI::warning( "Échec de mise à jour du terme {$term->term_id} : " . $result->get_error_message() );
			}
		}
	}

	return $counts;
}

/* ----------------------------------------------------------------------------
 * Exécution
 * ------------------------------------------------------------------------- */

$log_filename = 'fixed_links_maint229.txt';
$log_file     = fopen( $log_filename, 'w' );

if ( ! $log_file ) {
	WP_CLI::error( "Impossible d'ouvrir le fichier de log $log_filename" );
	return;
}

WP_CLI::line( '=== MAINT-229 — Correction des liens cassés ===' );
WP_CLI::line( $fix_links_live ? 'Mode : LIVE (les contenus seront modifiés)' : 'Mode : DRY-RUN (aucune écriture)' );
fwrite( $log_file, '=== MAINT-229 — ' . ( $fix_links_live ? 'LIVE' : 'DRY-RUN' ) . ' ===' . PHP_EOL );

WP_CLI::line( 'Résolution des fiches pays (CAT 2)...' );
fwrite( $log_file, '--- Résolution CAT 2 ---' . PHP_EOL );
[ $cat2_map, $cat2_unresolved ] = maint229_build_country_map( $cat2_country_anchors, $log_file );
WP_CLI::line( sprintf( '  %d fiches pays résolues, %d non résolues.', count( $cat2_map ), count( $cat2_unresolved ) ) );

fwrite( $log_file, '--- Posts modifiés ---' . PHP_EOL );

$totals      = [ 'cat1' => 0, 'cat2' => 0, 'cat3' => 0 ];
$updated     = 0;
$scanned     = 0;
$paged       = 1;
$batch_size  = 100;
$post_types  = [ 'post', 'page', 'tribe_events', 'actualities-my-space', 'chronique', 'training', 'edh', 'portrait', 'fiche_pays', 'landmark', 'local-structures', 'petition', 'press-release' ];

do {
	$query = new WP_Query(
		[
			'post_type'              => $post_types,
			'posts_per_page'         => $batch_size,
			'paged'                  => $paged,
			'post_status'            => [ 'publish', 'private' ],
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	foreach ( $query->posts as $post ) {
		$scanned++;
		$counts = maint229_process_post( $post, $cat1_mappings, $cat2_map, $fix_links_live, $log_file );

		if ( array_sum( $counts ) > 0 ) {
			$updated++;
			$totals['cat1'] += $counts['cat1'];
			$totals['cat2'] += $counts['cat2'];
			$totals['cat3'] += $counts['cat3'];
		}
	}

	wp_reset_postdata();

	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}

	$paged++;
} while ( $query->post_count > 0 );

// Passe sur les descriptions de termes (rendues sur les archives /categorie/{slug}/, etc.).
fwrite( $log_file, '--- Descriptions de termes modifiées ---' . PHP_EOL );

$terms_updated = 0;
$terms_scanned = 0;
$terms         = get_terms(
	[
		'taxonomy'   => get_taxonomies(),
		'hide_empty' => false,
	]
);

if ( ! is_wp_error( $terms ) ) {
	foreach ( $terms as $term ) {
		$terms_scanned++;
		$counts = maint229_process_term( $term, $cat1_mappings, $cat2_map, $fix_links_live, $log_file );

		if ( array_sum( $counts ) > 0 ) {
			$terms_updated++;
			$totals['cat1'] += $counts['cat1'];
			$totals['cat2'] += $counts['cat2'];
			$totals['cat3'] += $counts['cat3'];
		}
	}
} else {
	WP_CLI::warning( 'get_terms a échoué : ' . $terms->get_error_message() );
}

$summary = sprintf(
	'CAT1 (repères/dossiers) : %d | CAT2 (fiches pays) : %d | CAT3 (emails Cloudflare) : %d',
	$totals['cat1'],
	$totals['cat2'],
	$totals['cat3']
);

fwrite( $log_file, '--- Résumé ---' . PHP_EOL . $summary . PHP_EOL );
fwrite( $log_file, "$terms_updated descriptions de termes corrigées (sur $terms_scanned analysées)." . PHP_EOL );
fclose( $log_file );

WP_CLI::line( '-----------------------------------------------------' );
WP_CLI::line( "$scanned posts analysés, $updated contenant des liens à corriger." );
WP_CLI::line( "$terms_scanned termes analysés, $terms_updated descriptions à corriger." );
WP_CLI::line( $summary );

if ( $cat2_unresolved ) {
	WP_CLI::warning( 'Fiches pays non résolues (à traiter manuellement) : ' . implode( ', ', $cat2_unresolved ) );
}

if ( $fix_links_live ) {
	WP_CLI::success( "Corrections appliquées. Détail dans $log_filename" );
} else {
	WP_CLI::success( "Dry-run terminé (aucune écriture). Détail dans $log_filename — relancer avec 'live' pour appliquer." );
}
