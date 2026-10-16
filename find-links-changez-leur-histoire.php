<?php

/**
 * Recense tous les contenus qui présentent un lien vers la page « Changez leur histoire ».
 *
 * Script en LECTURE SEULE : il n'écrit rien en base, il produit uniquement un CSV.
 *
 * Sources analysées :
 *   1. Le contenu des posts publiés / privés (tous les types publics + blocs réutilisables,
 *      templates, template parts et menus de navigation FSE). Les blocs sont parsés : un lien
 *      est détecté qu'il soit écrit en dur (href, attribut URL) ou référencé par ID de post
 *      (attributs postId, postIds, selectedPostId… des blocs amnesty-core).
 *   2. Les métadonnées de ces posts (wp_postmeta, ex. champs ACF).
 *   3. Les descriptions de termes (pages d'archive).
 *   4. Les menus classiques (wp_nav_menu).
 *   5. Les options (widgets, réglages du thème…), hors transients.
 *   6. La table de liens de Yoast SEO (wp_yoast_seo_links), utilisée en recoupement.
 *
 * Usage (WP-CLI) :
 *   wp eval-file find-links-changez-leur-histoire.php            # ID de la page résolu depuis son URL
 *   wp eval-file find-links-changez-leur-histoire.php id=12345   # force l'ID de la page cible
 *
 * Résultat : liens_changez_leur_histoire.csv (UTF-8, séparateur « ; », ouvrable dans Excel).
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

const CLH_TARGET_URL = 'https://www.amnesty.fr/nous-connaitre/nos-combats/changez-leur-histoire/';
const CLH_OUTPUT     = 'liens_changez_leur_histoire.csv';

/**
 * Clés d'attributs de blocs qui référencent un post par son ID.
 */
const CLH_ID_KEYS = [ 'postId', 'postIds', 'selectedPostId', 'id', 'ID' ];

/**
 * Indique si une URL trouvée dans le contenu pointe vers la page cible.
 *
 * @param string $url         URL telle que trouvée en base (absolue, relative, avec ancre…).
 * @param string $target_path Chemin de la page cible, sans slash final
 *                            (« /nous-connaitre/nos-combats/changez-leur-histoire »).
 * @param int    $target_id   ID de la page cible (pour les liens du type « ?page_id=ID »).
 */
function clh_url_matches_target( string $url, string $target_path, int $target_id ): bool {
	parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
	if ( (int) ( $query['page_id'] ?? $query['p'] ?? 0 ) === $target_id ) {
		return true;
	}

	return rtrim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' ) === $target_path;
}

/**
 * Extrait les URLs candidates d'une chaîne : valeurs href/src, URLs absolues dans le texte,
 * ou la chaîne entière si elle ressemble à une URL (attribut de bloc « url », « link »…).
 *
 * @return string[]
 */
function clh_extract_urls( string $value ): array {
	$urls  = [];
	$value = str_replace( '\/', '/', $value );

	if ( preg_match_all( '~(?:href|src)\s*=\s*(["\'])(.*?)\1~i', $value, $m ) ) {
		$urls = array_merge( $urls, $m[2] );
	}

	if ( preg_match_all( '~https?://[^\s"\'<>]+~i', $value, $m ) ) {
		$urls = array_merge( $urls, $m[0] );
	}

	$trimmed = trim( $value );
	if ( '' !== $trimmed && ! preg_match( '~[\s<>]~', $trimmed ) ) {
		$urls[] = $trimmed;
	}

	return array_unique( array_map( 'html_entity_decode', $urls ) );
}

/**
 * Parcourt récursivement une valeur (attributs de bloc, meta désérialisée, option…) et
 * collecte les références à la page cible, avec le chemin de clé où elles ont été trouvées.
 *
 * @param mixed    $value
 * @param string[] $hits  Collecte « chemin => valeur trouvée ».
 */
function clh_scan_value( $value, string $path, string $key, int $target_id, string $target_path, array &$hits ): void {
	if ( is_object( $value ) ) {
		$value = (array) $value;
	}

	if ( is_array( $value ) ) {
		foreach ( $value as $k => $v ) {
			$is_named = is_string( $k );
			clh_scan_value(
				$v,
				'' === $path ? (string) $k : $path . '.' . $k,
				$is_named ? $k : $key,
				$target_id,
				$target_path,
				$hits
			);
		}
		return;
	}

	if ( in_array( $key, CLH_ID_KEYS, true ) && is_numeric( $value ) && (int) $value === $target_id ) {
		$hits[] = "$path = $value (ID)";
		return;
	}

	if ( ! is_string( $value ) || '' === $value ) {
		return;
	}

	// Valeur sérialisée ou JSON stockée en chaîne : on la décode et on la parcourt.
	if ( is_serialized( $value ) ) {
		clh_scan_value( maybe_unserialize( $value ), $path, $key, $target_id, $target_path, $hits );
		return;
	}

	foreach ( clh_extract_urls( $value ) as $url ) {
		if ( clh_url_matches_target( $url, $target_path, $target_id ) ) {
			$hits[] = '' === $path ? $url : "$path = $url";
		}
	}
}

/**
 * Parcourt l'arbre de blocs d'un contenu et renvoie les correspondances par bloc.
 *
 * @return array<string, string[]> Nom du bloc => références trouvées.
 */
function clh_scan_blocks( array $blocks, int $target_id, string $target_path ): array {
	$found = [];

	foreach ( $blocks as $block ) {
		$name = $block['blockName'] ?? null;
		$name = $name ?: 'contenu classique';
		$hits = [];

		clh_scan_value( $block['attrs'] ?? [], '', '', $target_id, $target_path, $hits );

		// innerHTML contient le HTML propre au bloc, sans celui de ses blocs enfants.
		clh_scan_value( $block['innerHTML'] ?? '', '', '', $target_id, $target_path, $hits );

		if ( $hits ) {
			$found[ $name ] = array_merge( $found[ $name ] ?? [], $hits );
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			foreach ( clh_scan_blocks( $block['innerBlocks'], $target_id, $target_path ) as $child => $child_hits ) {
				$found[ $child ] = array_merge( $found[ $child ] ?? [], $child_hits );
			}
		}
	}

	return $found;
}

/**
 * Ajoute (ou complète) une ligne de résultat pour un post.
 */
function clh_add_post_row( array &$rows, int $post_id, string $source, string $location, array $hits ): void {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$key = "post:$post_id|$source|$location";

	if ( ! isset( $rows[ $key ] ) ) {
		$rows[ $key ] = [
			'source'      => $source,
			'id'          => $post_id,
			'titre'       => html_entity_decode( get_the_title( $post ), ENT_QUOTES ),
			'type'        => $post->post_type,
			'statut'      => $post->post_status,
			'url'         => 'publish' === $post->post_status ? (string) get_permalink( $post ) : '',
			'edition'     => admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
			'emplacement' => $location,
			'valeurs'     => [],
			'yoast'       => 'non',
		];
	}

	$rows[ $key ]['valeurs'] = array_values( array_unique( array_merge( $rows[ $key ]['valeurs'], $hits ) ) );
}

/**
 * Ajoute une ligne de résultat pour un objet qui n'est pas un post (terme, menu, option).
 */
function clh_add_other_row( array &$rows, string $source, string $id, string $title, string $type, string $url, string $edit, string $location, array $hits ): void {
	$rows[ "$source:$id|$location" ] = [
		'source'      => $source,
		'id'          => $id,
		'titre'       => $title,
		'type'        => $type,
		'statut'      => '',
		'url'         => $url,
		'edition'     => $edit,
		'emplacement' => $location,
		'valeurs'     => array_values( array_unique( $hits ) ),
		'yoast'       => '',
	];
}

global $wpdb;

// --- Résolution de la page cible -------------------------------------------------------

$target_id = 0;
foreach ( ( isset( $args ) && is_array( $args ) ) ? $args : [] as $arg ) {
	if ( str_starts_with( (string) $arg, 'id=' ) ) {
		$target_id = (int) substr( (string) $arg, 3 );
	}
}

if ( ! $target_id ) {
	$target_id = url_to_postid( CLH_TARGET_URL );
}

if ( ! $target_id || ! get_post( $target_id ) ) {
	WP_CLI::error( 'Page cible introuvable. Relancer avec id=<ID de la page « Changez leur histoire »>.' );
}

$target_path = rtrim( (string) wp_parse_url( CLH_TARGET_URL, PHP_URL_PATH ), '/' );
$target_slug = basename( $target_path );

WP_CLI::line( sprintf( 'Page cible : #%d « %s » (%s)', $target_id, get_the_title( $target_id ), get_permalink( $target_id ) ) );

$rows = [];

// --- 1. Contenu des posts ----------------------------------------------------------------

$post_types = array_values(
	array_diff(
		array_merge(
			get_post_types( [ 'public' => true ] ),
			[ 'wp_block', 'wp_template', 'wp_template_part', 'wp_navigation' ]
		),
		[ 'attachment' ]
	)
);
$statuses = [ 'publish', 'private' ];

$types_sql    = "'" . implode( "','", array_map( 'esc_sql', $post_types ) ) . "'";
$statuses_sql = "'" . implode( "','", $statuses ) . "'";

// Pré-filtre SQL large (slug ou ID présents dans le contenu), affiné ensuite en PHP.
$like_slug = '%' . $wpdb->esc_like( $target_slug ) . '%';
$like_id   = '%' . $wpdb->esc_like( (string) $target_id ) . '%';

$last_id          = 0;
$matched_reusable = [];

do {
	$batch = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID, post_type, post_content FROM {$wpdb->posts}
			WHERE ID > %d AND ID <> %d
			AND post_type IN ($types_sql) AND post_status IN ($statuses_sql)
			AND ( post_content LIKE %s OR post_content LIKE %s )
			ORDER BY ID ASC LIMIT 200",
			$last_id,
			$target_id,
			$like_slug,
			$like_id
		)
	);

	foreach ( $batch as $row ) {
		$last_id = (int) $row->ID;

		foreach ( clh_scan_blocks( parse_blocks( $row->post_content ), $target_id, $target_path ) as $block => $hits ) {
			clh_add_post_row( $rows, (int) $row->ID, 'contenu', "bloc $block", $hits );
		}
	}
} while ( count( $batch ) === 200 );

// Blocs réutilisables : remonter aussi les contenus qui les insèrent.
foreach ( $rows as $row ) {
	if ( 'wp_block' === $row['type'] ) {
		$matched_reusable[] = (int) $row['id'];
	}
}

foreach ( array_unique( $matched_reusable ) as $ref_id ) {
	$users = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			WHERE post_type IN ($types_sql) AND post_status IN ($statuses_sql)
			AND post_content REGEXP %s",
			'"ref":' . $ref_id . '[,}]'
		)
	);

	foreach ( $users as $user_id ) {
		clh_add_post_row(
			$rows,
			(int) $user_id,
			'contenu',
			sprintf( 'via bloc réutilisable #%d « %s »', $ref_id, get_the_title( $ref_id ) ),
			[ "ref = $ref_id" ]
		);
	}
}

WP_CLI::line( '1/6 Contenus analysés.' );

// --- 2. Métadonnées des posts --------------------------------------------------------------

$metas = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT pm.post_id, pm.meta_key, pm.meta_value FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE p.ID <> %d AND p.post_type IN ($types_sql) AND p.post_status IN ($statuses_sql)
		AND pm.meta_key NOT LIKE %s
		AND pm.meta_value LIKE %s",
		$target_id,
		$wpdb->esc_like( '_oembed_' ) . '%',
		$like_slug
	)
);

foreach ( $metas as $meta ) {
	$hits = [];
	clh_scan_value( $meta->meta_value, '', $meta->meta_key, $target_id, $target_path, $hits );

	if ( $hits ) {
		clh_add_post_row( $rows, (int) $meta->post_id, 'meta', "meta {$meta->meta_key}", $hits );
	}
}

WP_CLI::line( '2/6 Métadonnées analysées.' );

// --- 3. Descriptions de termes -------------------------------------------------------------

$terms = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT t.term_id, t.name, tt.taxonomy, tt.description FROM {$wpdb->terms} t
		INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
		WHERE tt.description LIKE %s",
		$like_slug
	)
);

foreach ( $terms as $term ) {
	$hits = [];
	clh_scan_value( $term->description, '', '', $target_id, $target_path, $hits );

	if ( $hits ) {
		$link = get_term_link( (int) $term->term_id, $term->taxonomy );
		clh_add_other_row(
			$rows,
			'terme',
			(string) $term->term_id,
			$term->name,
			$term->taxonomy,
			is_wp_error( $link ) ? '' : $link,
			admin_url( "term.php?taxonomy={$term->taxonomy}&tag_ID={$term->term_id}" ),
			'description du terme',
			$hits
		);
	}
}

WP_CLI::line( '3/6 Descriptions de termes analysées.' );

// --- 4. Menus classiques ---------------------------------------------------------------------

$locations_by_menu = [];
foreach ( get_nav_menu_locations() as $location => $menu_id ) {
	$locations_by_menu[ $menu_id ][] = $location;
}

foreach ( wp_get_nav_menus() as $menu ) {
	foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $item ) {
		$by_id  = 'post_type' === $item->type && (int) $item->object_id === $target_id;
		$by_url = clh_url_matches_target( (string) $item->url, $target_path, $target_id );

		if ( $by_id || $by_url ) {
			clh_add_other_row(
				$rows,
				'menu',
				(string) $item->ID,
				$menu->name,
				'emplacements : ' . implode( ', ', $locations_by_menu[ $menu->term_id ] ?? [ 'aucun' ] ),
				'',
				admin_url( 'nav-menus.php?action=edit&menu=' . $menu->term_id ),
				'entrée de menu « ' . $item->title . ' »',
				[ (string) $item->url ]
			);
		}
	}
}

WP_CLI::line( '4/6 Menus analysés.' );

// --- 5. Options --------------------------------------------------------------------------------

$options = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT option_name, option_value FROM {$wpdb->options}
		WHERE option_name NOT LIKE %s AND option_name NOT LIKE %s
		AND option_value LIKE %s",
		$wpdb->esc_like( '_transient' ) . '%',
		$wpdb->esc_like( '_site_transient' ) . '%',
		$like_slug
	)
);

foreach ( $options as $option ) {
	$hits = [];
	clh_scan_value( $option->option_value, '', '', $target_id, $target_path, $hits );

	if ( $hits ) {
		clh_add_other_row( $rows, 'option', $option->option_name, $option->option_name, 'option', '', '', 'wp_options', $hits );
	}
}

WP_CLI::line( '5/6 Options analysées.' );

// --- 6. Recoupement avec Yoast SEO ----------------------------------------------------------------

$yoast_table = $wpdb->prefix . 'yoast_seo_links';

if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $yoast_table ) ) === $yoast_table ) {
	$yoast_links = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT post_id, target_post_id, url FROM {$yoast_table}
			WHERE type = 'internal' AND post_id <> %d AND ( target_post_id = %d OR url LIKE %s )",
			$target_id,
			$target_id,
			$like_slug
		)
	);

	// Le LIKE remonte aussi les URLs dont le slug contient « changez-leur-histoire »
	// (articles dédiés…) : on ne garde que les liens qui pointent réellement vers la cible.
	$yoast_sources = [];
	foreach ( $yoast_links as $link ) {
		if ( (int) $link->target_post_id === $target_id || clh_url_matches_target( (string) $link->url, $target_path, $target_id ) ) {
			$yoast_sources[] = (int) $link->post_id;
		}
	}
	$yoast_sources = array_values( array_unique( $yoast_sources ) );

	foreach ( $rows as &$row ) {
		if ( 'meta' === $row['source'] || 'contenu' === $row['source'] ) {
			$row['yoast'] = in_array( (int) $row['id'], $yoast_sources, true ) ? 'oui' : 'non';
		}
	}
	unset( $row );

	// Contenus connus de Yoast mais non retrouvés par l'analyse : index Yoast obsolète,
	// ou statut non analysé (brouillon…). Remontés pour vérification manuelle.
	$found_ids = array_map( 'intval', array_column( $rows, 'id' ) );
	foreach ( array_diff( $yoast_sources, $found_ids ) as $orphan_id ) {
		clh_add_post_row( $rows, $orphan_id, 'yoast uniquement', 'à vérifier (index Yoast)', [] );
		$rows[ "post:$orphan_id|yoast uniquement|à vérifier (index Yoast)" ]['yoast'] = 'oui';
	}

	WP_CLI::line( sprintf( '6/6 Yoast : %d contenus sources dans l\'index.', count( $yoast_sources ) ) );
} else {
	WP_CLI::warning( "6/6 Table $yoast_table absente : recoupement Yoast ignoré." );
}

// --- Export CSV --------------------------------------------------------------------------------------

$csv = fopen( CLH_OUTPUT, 'w' );
if ( ! $csv ) {
	WP_CLI::error( 'Impossible d\'écrire ' . CLH_OUTPUT );
}

// BOM UTF-8 : Excel affiche correctement les accents à l'ouverture directe du CSV.
fwrite( $csv, "\xEF\xBB\xBF" );
fputcsv( $csv, [ 'Source', 'ID', 'Titre', 'Type', 'Statut', 'URL publique', 'Lien d\'édition', 'Emplacement du lien', 'Valeurs trouvées', 'Détecté par Yoast' ], ';' );

uasort( $rows, static fn( array $a, array $b ) => [ $a['source'], $a['type'], $a['titre'] ] <=> [ $b['source'], $b['type'], $b['titre'] ] );

foreach ( $rows as $row ) {
	$row['valeurs'] = implode( "\n", $row['valeurs'] );
	fputcsv( $csv, array_values( $row ), ';' );
}

fclose( $csv );

WP_CLI::success( sprintf( '%d emplacements trouvés (%d contenus distincts). Résultat : %s', count( $rows ), count( array_unique( array_column( $rows, 'id' ) ) ), CLH_OUTPUT ) );
