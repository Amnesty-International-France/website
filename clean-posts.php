<?php

if ( !defined( 'WP_CLI' ) || !WP_CLI ) {
	return;
}

function clean_blocks( $blocks ) {
	$cleaned_blocks = [];

	foreach ( $blocks as $block ) {
		if ( $block['blockName'] === 'core/group' ) {
			if ( !empty($block['innerBlocks']) ) {
				$cleaned_blocks = array_merge(
					$cleaned_blocks,
					clean_blocks( $block['innerBlocks'] )
				);
			}
			continue;
		}

		if ( !empty($block['innerBlocks']) ) {
			$block['innerBlocks'] = clean_blocks( $block['innerBlocks'] );
		}

		$cleaned_blocks[] = $block;
	}
	return $cleaned_blocks;
}

function process_post($post): bool {
	$original_content = $post->post_content;

	if ( empty($original_content) ) {
		return false;
	}

	$blocks = parse_blocks( $original_content );
	$cleaned_blocks = clean_blocks( $blocks );
	$new_content = serialize_blocks( $cleaned_blocks );

	if ($original_content !== $new_content) {
		$result = wp_update_post([
			'ID' => $post->ID,
			'post_content' => $new_content,
		], true);

		return !is_wp_error($result);
	}
	return false;
}

WP_CLI::line( 'Starting script to clean posts...' );

$log_filename = 'cleaned_posts.txt';
$log_file = fopen($log_filename, 'w');

if (!$log_file) {
	WP_CLI::error("Impossible d'ouvrir le fichier de log $log_filename");
	return;
}

$updated_count = 0;
$scanned_count = 0;
$paged = 1;
$batch_size = 100;
$post_types = ['post'];

do {
	$args = [
		'post_type' => $post_types,
		'posts_per_page' => $batch_size,
		'paged' => $paged,
		'post_status' => ['publish', 'private'],
		'no_found_rows'  => true,
	];

	$query = new WP_Query($args);

	foreach ($query->posts as $post) {
		$scanned_count++;

		if ( process_post($post) ) {
			$updated_count++;
			fwrite( $log_file, $post->ID . PHP_EOL );
		}
	}

	wp_reset_postdata();

	if (function_exists('wp_cache_flush')) {
		wp_cache_flush();
	}

	$paged++;
} while ( $query->post_count > 0);

fclose($log_file);

WP_CLI::line( "-----------------------------------------------------" );
WP_CLI::line( "$scanned_count posts analysés." );
WP_CLI::success( "$updated_count posts ont été mis à jour." );
WP_CLI::success( "Liste des IDs modifiés dans $log_filename" );
