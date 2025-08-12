<?php

declare( strict_types = 1 );

if ( ! function_exists( 'amnesty_overlay' ) ) {
	/**
	 * Render overlay div for masking the body when modals are open
	 *
	 * @return void
	 */
	function amnesty_overlay(): void {
		echo '<div class="overlay" aria-hidden="true"></div>';
	}
}

add_action( 'wp_body_open', 'amnesty_overlay', 1 );

if ( ! function_exists( 'amnesty_language_selector' ) ) {
	/**
	 * Render the standalone language selector
	 *
	 * @return void
	 */
	function amnesty_language_selector(): void {
		get_template_part( 'partials/language-selector' );
	}
}

add_action( 'wp_body_open', 'amnesty_language_selector', 2 );

if ( ! function_exists( 'amnesty_site_header' ) ) {
    /**
     * Render the site header (navigation) bar
     *
     * @return void
     */
    function amnesty_site_header(): void {
        $is_mon_espace_section = false;

        if ( is_page( 'mon-espace' ) ) {
            $is_mon_espace_section = true;
        } else {
            $parent_page = get_page_by_path( 'mon-espace' );

            if ( $parent_page ) {
                $ancestors = get_post_ancestors( get_the_ID() );

                if ( in_array( $parent_page->ID, $ancestors, true ) ) {
                    $is_mon_espace_section = true;
                }
            }
        }

        if ( ! $is_mon_espace_section ) {
            get_template_part( 'partials/navigation/desktop' );
        }
    }
}

add_action( 'wp_body_open', 'amnesty_site_header', 3 );
