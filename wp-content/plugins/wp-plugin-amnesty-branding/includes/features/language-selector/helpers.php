<?php

declare( strict_types = 1 );

// no namespace intentionally

use function Amnesty\SharePoint\get_document_languages;

if ( ! function_exists( 'get_document_translations' ) ) {
	/**
	 * Retrieve core language translations for a given document.
	 *
	 * Essentially a filtered list from `get_document_langauges()`
	 *
	 * @package Amnesty\Plugins\Multilingualpress
	 *
	 * @return array<int,object>
	 */
	function get_document_translations() {
		if ( ! function_exists( '\Amnesty\SharePoint\get_document_languages' ) ) {
			return [];
		}

		$all = get_document_languages();

		if ( empty( $all ) ) {
			return [];
		}

		$sites = amnesty_get_sites();
		$trans = [];

		foreach ( $sites as $site ) {
			$found = array_filter( $all, fn ( object $item ): bool => $item->lang_iso === $site->code );

			if ( ! count( $found ) ) {
				continue;
			}

			$trans = array_merge( $trans, array_values( $found ) );
		}

		return $trans;
	}
}

if ( ! function_exists( 'has_document_translations' ) ) {
	/**
	 * Check whether the current document has translations
	 *
	 * @package Amnesty\Plugins\Multilingualpress
	 *
	 * @return bool
	 */
	function has_document_translations() {
		return is_single() && 'attachment' === get_post_type() && (bool) get_document_translations();
	}
}
