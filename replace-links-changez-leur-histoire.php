<?php

/**
 * Remplace les liens internes vers l'ancienne page « Changez leur histoire » par la nouvelle URL (CLH-48).
 *
 * Ancienne URL : https://www.amnesty.fr/nous-connaitre/nos-combats/changez-leur-histoire/
 * Nouvelle URL : https://www.amnesty.fr/changez-leur-histoire/
 *
 * Périmètre : le relevé produit par find-links-changez-leur-histoire.php (CLH-40), validé par le client.
 *   1. Contenus (lignes 2 à 8)      : remplacement de l'URL dans post_content (blocs, attributs, HTML).
 *   2. Menus (ligne 9, menu 213)    : entrées « lien personnalisé » qui pointent vers l'ancienne URL.
 *   3. Cache Jetpack (lignes 10-16) : suppression de la meta _jetpack_related_posts_cache, qui référence
 *                                     la page par son ID (92767). Jetpack la recalcule au prochain affichage.
 *
 * La redirection 301 ancienne URL → nouvelle URL n'est PAS posée par ce script : elle se fait dans Yoast
 * (SEO > Redirections), APRÈS la publication de la nouvelle page, sinon boucle de redirection.
 *
 * Par défaut le script est en SIMULATION : il n'écrit rien et affiche ce qu'il ferait.
 *
 * Usage (WP-CLI) :
 *   wp eval-file replace-links-changez-leur-histoire.php          # simulation
 *   wp eval-file replace-links-changez-leur-histoire.php apply    # écriture en base (après le GO client)
 *
 * En mode apply, une sauvegarde JSON des valeurs modifiées est écrite dans le dossier temporaire,
 * et chaque contenu modifié garde une révision WordPress.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

const CLH48_OLD_PATH = '/nous-connaitre/nos-combats/changez-leur-histoire';
const CLH48_NEW_PATH = '/changez-leur-histoire';
const CLH48_NEW_URL  = 'https://www.amnesty.fr/changez-leur-histoire/';

/** ID de l'ancienne page, référencé dans le cache Jetpack. */
const CLH48_OLD_PAGE_ID = 92767;

/** Lignes 2 à 8 du relevé : contenus dont le post_content contient l'ancienne URL. */
const CLH48_CONTENT_IDS = [ 37029, 36842, 37112, 66589, 38199, 91511, 110329 ];

/** Lignes 10 à 16 du relevé : contenus dont le cache Jetpack référence l'ancienne page. */
const CLH48_JETPACK_IDS = [ 64405, 64408, 50591, 35976, 50779, 50339, 161932 ];

/**
 * Motif de l'ancienne URL, absolue (amnesty.fr, avec ou sans www) ou relative,
 * avec ou sans slash final, slashs éventuellement échappés en JSON (« \/ »).
 * Le lookahead final exclut les URLs plus longues (« changez-leur-histoire-2026 », sous-pages).
 */
const CLH48_PATTERN = '~(?P<host>https?:(?:\\\\?/){2}(?:www\.)?amnesty\.fr)?(?P<s>\\\\?/)nous-connaitre(?P=s)nos-combats(?P=s)changez-leur-histoire(?P<end>(?P=s))?(?![\w/-])~i';

/**
 * Remplace l'ancienne URL par la nouvelle en conservant la forme d'origine
 * (absolue ou relative, slashs échappés ou non, slash final ou non).
 */
function clh48_replace( string $value, int &$count ): string {
	return (string) preg_replace_callback(
		CLH48_PATTERN,
		static fn( array $m ): string => ( $m['host'] ?? '' ) . $m['s'] . 'changez-leur-histoire' . ( $m['end'] ?? '' ),
		$value,
		-1,
		$count
	);
}

/**
 * Indique si une URL (de menu notamment) pointe vers l'ancienne page, quel que soit le domaine.
 * Le relevé a été fait sur la preview Infomaniak : l'entrée de menu y portait le domaine de la copie.
 */
function clh48_is_old_url( string $url ): bool {
	return rtrim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' ) === CLH48_OLD_PATH;
}

global $wpdb;

$apply = in_array( 'apply', ( isset( $args ) && is_array( $args ) ) ? $args : [], true );
$mode  = $apply ? 'APPLICATION' : 'SIMULATION (rien n\'est écrit, relancer avec « apply »)';

WP_CLI::line( "Mode : $mode" );

// --- 0. Vérifications préalables -----------------------------------------------------------

// La nouvelle page doit être publiée à la nouvelle URL, sinon les liens remplacés passeraient
// par la redirection inverse (aujourd'hui la nouvelle URL redirige vers l'ancienne).
$new_id   = url_to_postid( CLH48_NEW_URL );
$new_path = $new_id ? rtrim( (string) wp_parse_url( get_permalink( $new_id ), PHP_URL_PATH ), '/' ) : '';
$new_ok   = $new_id && 'publish' === get_post_status( $new_id ) && CLH48_NEW_PATH === $new_path;

if ( $new_ok ) {
	WP_CLI::line( sprintf( 'Nouvelle page : #%d « %s » publiée à %s', $new_id, get_the_title( $new_id ), get_permalink( $new_id ) ) );
} elseif ( $apply ) {
	WP_CLI::error( 'La nouvelle page n\'est pas publiée à ' . CLH48_NEW_URL . ' : publier la page avant de lancer le script.' );
} else {
	WP_CLI::warning( 'La nouvelle page n\'est pas encore publiée à ' . CLH48_NEW_URL . ' (bloquant en mode apply).' );
}

// Redirections Yoast Premium : signaler une redirection au départ de la nouvelle URL (risque de boucle)
// et l'état de la 301 ancienne → nouvelle.
$redirects = get_option( 'wpseo-premium-redirects-base' );
if ( is_array( $redirects ) ) {
	$has_301 = false;
	foreach ( $redirects as $redirect ) {
		$origin = '/' . trim( (string) ( $redirect['origin'] ?? '' ), '/' );
		$target = '/' . trim( (string) wp_parse_url( (string) ( $redirect['url'] ?? '' ), PHP_URL_PATH ), '/' );

		if ( CLH48_NEW_PATH === $origin ) {
			WP_CLI::warning( "Yoast : une redirection part de la nouvelle URL vers $target. À supprimer, sinon boucle." );
		}
		if ( CLH48_OLD_PATH === $origin ) {
			$has_301 = CLH48_NEW_PATH === $target && 301 === (int) ( $redirect['type'] ?? 0 );
			WP_CLI::line( "Yoast : redirection {$redirect['type']} de l'ancienne URL vers $target." );
		}
	}
	if ( ! $has_301 ) {
		WP_CLI::warning( 'Yoast : pas encore de 301 ancienne URL → nouvelle URL. À poser après la publication de la page.' );
	}
} else {
	WP_CLI::warning( 'Redirections Yoast Premium introuvables : vérifier la 301 à la main dans SEO > Redirections.' );
}

$backup = [ 'contents' => [], 'menu_items' => [], 'jetpack' => [] ];
$totals = [ 'contents' => 0, 'menu_items' => 0, 'jetpack' => 0 ];

// --- 1. Contenus (lignes 2 à 8) ----------------------------------------------------------------

// Sans utilisateur connecté, wp_update_post passerait le contenu dans kses et casserait des blocs.
kses_remove_filters();

foreach ( CLH48_CONTENT_IDS as $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		WP_CLI::warning( "Contenu #$post_id introuvable." );
		continue;
	}

	$count   = 0;
	$content = clh48_replace( $post->post_content, $count );

	if ( 0 === $count ) {
		WP_CLI::line( "  #$post_id « {$post->post_title} » : aucune occurrence (déjà traité ?)." );
		continue;
	}

	WP_CLI::line( "  #$post_id « {$post->post_title} » : $count occurrence(s)." );
	++$totals['contents'];

	if ( $apply ) {
		$backup['contents'][ $post_id ] = $post->post_content;

		$result = wp_update_post( wp_slash( [ 'ID' => $post_id, 'post_content' => $content ] ), true );
		if ( is_wp_error( $result ) ) {
			WP_CLI::warning( "  #$post_id : échec de la mise à jour (" . $result->get_error_message() . ').' );
		}
	}
}

WP_CLI::line( sprintf( '1/3 Contenus : %d à modifier.', $totals['contents'] ) );

// --- 2. Menus (ligne 9) --------------------------------------------------------------------------

foreach ( wp_get_nav_menus() as $menu ) {
	foreach ( wp_get_nav_menu_items( $menu->term_id ) ?: [] as $item ) {
		if ( 'custom' !== $item->type || ! clh48_is_old_url( (string) $item->url ) ) {
			continue;
		}

		WP_CLI::line( "  Menu « {$menu->name} » (#{$menu->term_id}), entrée « {$item->title} » : {$item->url}" );
		++$totals['menu_items'];

		if ( $apply ) {
			$backup['menu_items'][ $item->ID ] = $item->url;
			update_post_meta( $item->ID, '_menu_item_url', esc_url_raw( CLH48_NEW_URL ) );
		}
	}
}

WP_CLI::line( sprintf( '2/3 Menus : %d entrée(s) à modifier.', $totals['menu_items'] ) );

// --- 3. Cache Jetpack des articles liés (lignes 10 à 16) ----------------------------------------------

foreach ( CLH48_JETPACK_IDS as $post_id ) {
	$cache = get_post_meta( $post_id, '_jetpack_related_posts_cache', true );
	if ( '' === $cache ) {
		WP_CLI::line( "  #$post_id : pas de cache Jetpack." );
		continue;
	}

	WP_CLI::line( "  #$post_id « " . get_the_title( $post_id ) . ' » : cache Jetpack à vider.' );
	++$totals['jetpack'];

	if ( $apply ) {
		$backup['jetpack'][ $post_id ] = $cache;
		delete_post_meta( $post_id, '_jetpack_related_posts_cache' );
	}
}

WP_CLI::line( sprintf( '3/3 Cache Jetpack : %d contenu(s) à vider.', $totals['jetpack'] ) );

// --- Sauvegarde -----------------------------------------------------------------------------------------

if ( $apply ) {
	$backup_file = get_temp_dir() . 'clh48-backup-' . gmdate( 'Ymd-His' ) . '.json';
	file_put_contents( $backup_file, wp_json_encode( $backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
	WP_CLI::line( "Sauvegarde des valeurs d'origine : $backup_file" );
}

// --- Contrôle : occurrences restantes hors périmètre ----------------------------------------------------

$like      = '%' . $wpdb->esc_like( 'changez-leur-histoire' ) . '%';
$remaining = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT ID, post_title, post_content FROM {$wpdb->posts}
		WHERE post_status IN ('publish','private','future') AND post_type NOT IN ('revision','attachment')
		AND post_content LIKE %s",
		$like
	)
);

$others = [];
foreach ( $remaining as $row ) {
	$count = 0;
	clh48_replace( $row->post_content, $count );
	if ( $count > 0 && ( $apply || ! in_array( (int) $row->ID, CLH48_CONTENT_IDS, true ) ) ) {
		$others[] = "#{$row->ID} « {$row->post_title} » ($count)";
	}
}

if ( $others ) {
	WP_CLI::warning( 'Contenus contenant encore l\'ancienne URL, non traités par le script : ' . implode( ', ', $others ) );
}

WP_CLI::success(
	sprintf(
		'%s : %d contenu(s), %d entrée(s) de menu, %d cache(s) Jetpack.',
		$apply ? 'Terminé' : 'Simulation terminée',
		$totals['contents'],
		$totals['menu_items'],
		$totals['jetpack']
	)
);
