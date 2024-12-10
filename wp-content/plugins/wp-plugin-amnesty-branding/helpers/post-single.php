<?php

declare( strict_types = 1 );

if ( ! function_exists( 'get_letter_recipients' ) ) {
	/**
	 * Retrieves recipient list for the letter block.
	 *
	 * @package Amnesty
	 *
	 * @return array
	 */
	function get_letter_recipients() {
		$is_refreshed = '1' === amnesty_get_meta_field( 'recipients_refreshed' );

		if ( $is_refreshed ) {
			$recipients = amnesty_get_meta_field( 'recipients_refresh' );
			$recipients = str_replace( '<-->', '', $recipients );
			$recipients = json_decode( $recipients, true );
		} else {
			$recipients = amnesty_get_meta_field( 'recipients' );

			if ( is_string( $recipients ) ) {
				$recipients = str_replace( '"""', '', $recipients );
				$recipients = json_decode( $recipients );
			}

			if ( is_array( $recipients ) ) {
				$recipients = array_map(
					// phpcs:ignore Universal.FunctionDeclarations.NoLongClosures.ExceedsRecommended
					function ( $item ) {
						if ( empty( $item ) ) {
							return [];
						}

						return [
							'name'     => '',
							'jobTitle' => $item,
						];
					},
					$recipients
				);
			}
		}

		if ( ! is_array( $recipients ) ) {
			return [];
		}

		$recipients = array_filter(
			$recipients,
			function ( $item ) {
				return ! empty( $item['jobTitle'] ) && '&nbsp;' !== $item['jobTitle'];
			}
		);

		return array_values( $recipients );
	}
}

if ( ! function_exists( 'display_letter_recipient' ) ) {
	/**
	 * Displays a recipient for the letter block.
	 *
	 * @package Amnesty
	 *
	 * @param array $recipient the recipient to display.
	 *
	 * @return void
	 */
	function display_letter_recipient( array $recipient = [] ) {
		if ( $recipient['name'] && $recipient['jobTitle'] ) {
			printf( '<p><strong>%s</strong>%s %s</p>', esc_html( $recipient['name'] ), ',', esc_html( $recipient['jobTitle'] ) );
			return;
		}

		if ( $recipient['name'] ) {
			printf( '<p><strong>%s</strong></p>', esc_html( $recipient['name'] ) );
			return;
		}

		if ( $recipient['jobTitle'] ) {
			printf( '<p>%s</p>', esc_html( $recipient['jobTitle'] ) );
			return;
		}
	}
}
