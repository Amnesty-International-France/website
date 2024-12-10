<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Features\Language_Selector;

use Locale;

if ( ! function_exists( __NAMESPACE__ . '\\maybe_render_language_selector_for_documents' ) ) {
	/**
	 * Overwrite the default language selector for documents
	 *
	 * @param bool $retval the existing return early value
	 *
	 * @return bool
	 */
	function maybe_render_language_selector_for_documents( bool $retval ): bool {
		if ( is_single() && 'attachment' === get_post_type() && has_document_translations() ) {
			render_language_selector_document_dropdown();
			return true;
		}

		return $retval;
	}
}

add_filter( 'render_language_selector_dropdown_return_early', __NAMESPACE__ . '\\maybe_render_language_selector_for_documents' );


if ( ! function_exists( __NAMESPACE__ . '\\render_language_selector_document_dropdown' ) ) {
	/**
	 * Render the language selector as a dropdown within the full width (read: dotorg) nav
	 * when on document single. Called by `render_language_selector_dropdown()`.
	 *
	 * @package Amnesty\Plugins\Multilingualpress
	 *
	 * @return void
	 */
	function render_language_selector_document_dropdown() {
		$posts = get_document_translations();

		if ( empty( $posts ) ) {
			return;
		}

		$wrapper_open  = sprintf( '<nav class="page-nav page-nav--left" aria-label="%s" aria-expanded="false"><ul class="site-selector">', /* translators: [front] */ esc_attr__( 'Available languages', 'aibrand' ) );
		$wrapper_close = '</ul></nav><div class="site-separator"></div>';

		$language = get_site_language_code();
		$current  = array_filter( $posts, fn ( object $post ): bool => $language === $post->lang_iso );
		$others   = array_filter( $posts, fn ( object $post ): bool => $language !== $post->lang_iso );
		$current  = array_values( $current );
		$current  = array_shift( $current );
		$others   = array_values( $others );

		if ( $current ) {
			$current_item_open = sprintf(
				'<li id="menu-item-%3$s-%4$s" class="menu-item menu-item-current menu-item-has-children menu-item-%3$s-%4$s" dir="%5$s"><a href="%2$s">%1$s</a>',
				esc_html( $current->language ),
				esc_url( get_blog_permalink( $current->blog_id, $current->item_id ) ),
				esc_attr( $current->blog_id ),
				esc_attr( $current->item_id ),
				esc_attr( get_string_textdirection( $current->name ) ),
			);
		} else {
			$name = Locale::getDisplayLanguage( $language, $language );

			$current_item_open = sprintf(
				'<li id="menu-item-%3$s-%4$s" class="menu-item menu-item-current menu-item-has-children menu-item-%3$s-%4$s" dir="%5$s"><a href="%2$s">%1$s</a>',
				esc_html( $name ),
				esc_url( current_url() ),
				esc_attr( get_current_blog_id() ),
				esc_attr( get_queried_object_id() ),
				esc_attr( get_string_textdirection( $name ) ),
			);
		}

		$current_item_close = '</li>';
		$other_items_open   = '<ul class="sub-menu">';
		$other_items_inner  = '';
		$other_items_close  = '</ul>';

		foreach ( amnesty_get_sites() as $site ) {
			if ( $site->code === $language ) {
				continue;
			}

			$post = array_filter( $posts, fn ( object $post ): bool => $site->code === $post->lang_iso );
			$post = array_values( $post );
			$post = array_shift( $post );

			// fallback to current translation on target site if no document translation
			if ( ! $post ) {
				$path = wp_parse_url( current_url(), PHP_URL_PATH );
				$path = preg_replace(
					'/^\/?[a-zA-Z]{2}\/(?:documents\/)?([a-zA-z]{3}[0-9]{2})\/([0-9]{3,4})\/([0-9]{4})\/([a-zA-Z]{2})\/?/',
					$site->code . '/documents/$1/$2/$3/$4/',
					$path
				);

				$url = trim( trim( get_network_option( null, 'siteurl' ), '/' ) . '/' . trim( $path, '/' ), '/' ) . '/';

				$other_items_inner .= sprintf(
					'<li id="menu-item-%3$s-%4$s" class="menu-item menu-item-%3$s-%4$s" dir="%5$s"><a href="%2$s">%1$s</a></li>',
					esc_html( mb_strtoupper( $site->lang, 'UTF-8' ) ),
					esc_url( $url ),
					esc_attr( $site->site_id ),
					esc_attr( $site->post_id ),
					esc_attr( $site->direction ),
				);

				continue;
			}

			$other_items_inner .= sprintf(
				'<li id="menu-item-%3$s-%4$s" class="menu-item menu-item-%3$s-%4$s" dir="%5$s"><a href="%2$s">%1$s</a></li>',
				esc_html( mb_strtoupper( $post->language, 'UTF-8' ) ),
				esc_url( get_blog_permalink( $post->blog_id, $post->item_id ) ),
				esc_attr( $post->blog_id ),
				esc_attr( $post->item_id ),
				esc_attr( get_string_textdirection( $post->post_title ?? $post->name ) ),
			);
		}

		$html = implode(
			'',
			[
				$wrapper_open,
				$current_item_open,
				$other_items_open,
				$other_items_inner,
				$other_items_close,
				$current_item_close,
				$wrapper_close,
			]
		);

		echo wp_kses_post( $html );
	}
}
