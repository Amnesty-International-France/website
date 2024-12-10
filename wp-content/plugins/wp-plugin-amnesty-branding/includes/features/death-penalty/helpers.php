<?php

declare( strict_types = 1 );

// no namespace intentionally

if ( ! function_exists( 'get_death_penalty_statuses' ) ) {
	/**
	 * Static list of death penalty status options
	 *
	 * @package Amnesty
	 *
	 * @return array
	 */
	function get_death_penalty_statuses(): array {
		static $statuses = [];

		if ( empty( $statuses ) ) {
			$statuses = [
				'abolitionist-totally'  => [
					/* translators: [front] Location single template https://www.amnesty.org/en/location/asia-and-the-pacific/south-asia/afghanistan/ */
					'label'       => _x( 'Abolitionist for all crimes', 'death penalty status', 'aibrand' ),
					/* translators: [front] Location single template https://www.amnesty.org/en/location/asia-and-the-pacific/south-asia/afghanistan/ */
					'description' => __( 'Does not use the death penalty', 'aibrand' ),
				],
				'abolitionist-ordinary' => [
					/* translators: [front] Location single template https://www.amnesty.org/en/location/asia-and-the-pacific/south-asia/afghanistan/ */
					'label'       => _x( 'Abolitionist for ordinary crimes', 'death penalty status', 'aibrand' ),
					/* translators: [front] Location single template https://www.amnesty.org/en/location/asia-and-the-pacific/south-asia/afghanistan/ */
					'description' => __( 'Retains the death penalty only for serious crimes, such as those committed during times of war', 'aibrand' ),
				],
				'abolitionist-practice' => [
					/* translators: [front] Location single template https://www.amnesty.org/en/location/asia-and-the-pacific/south-asia/afghanistan/ */
					'label'       => _x( 'Abolitionist in practice', 'death penalty status', 'aibrand' ),
					/* translators: [front] Location single template https://www.amnesty.org/en/location/asia-and-the-pacific/south-asia/afghanistan/ */
					'description' => __( 'Retains the death penalty in law, but hasn’t executed for at least 10 years', 'aibrand' ),
				],
				'retentionist'          => [
					/* translators: [front] Location single template https://www.amnesty.org/en/location/asia-and-the-pacific/south-asia/afghanistan/ */
					'label'       => _x( 'Retentionist', 'death penalty status', 'aibrand' ),
					/* translators: [front] Location single template https://www.amnesty.org/en/location/asia-and-the-pacific/south-asia/afghanistan/ */
					'description' => __( 'Retains the death penalty in law', 'aibrand' ),
				],
			];
		}

		return $statuses;
	}
}

if ( ! function_exists( 'get_death_penalty_status' ) ) {
	/**
	 * Retrieve the death penalty status for a term
	 *
	 * @package Amnesty
	 *
	 * @param WP_Term|stdClass $term the term to get the death penalty status of
	 *
	 * @return array
	 */
	function get_death_penalty_status( $term ): array {
		$id = $term->term_id ?? $term->id;

		$penalty = get_term_meta( $id, 'death_penalty', true );
		$type    = get_term_meta( $id, 'type', true );

		if ( ! $penalty || '' === $penalty || 'default' !== $type ) {
			return [];
		}

		$statuses = get_death_penalty_statuses();

		return $statuses[ $penalty ] ?? [];
	}
}

if ( ! function_exists( 'get_death_penalty_status_labels' ) ) {
	/**
	 * Retrieve all death penalty status labels
	 *
	 * @package Amnesty
	 *
	 * @return array
	 */
	function get_death_penalty_status_labels(): array {
		return array_map(
			function ( $status ) {
				return $status['label'];
			},
			get_death_penalty_statuses()
		);
	}
}

if ( ! function_exists( 'get_death_penalty_status_label' ) ) {
	/**
	 * Retrieve the death penalty status label for a term
	 *
	 * @package Amnesty
	 *
	 * @param WP_Term|stdClass $term the term to get the label of
	 *
	 * @return string
	 */
	function get_death_penalty_status_label( $term ): string {
		return get_death_penalty_status( $term )['label'];
	}
}

if ( ! function_exists( 'get_death_penalty_status_descriptions' ) ) {
	/**
	 * Retrieve all death penalty status descriptions
	 *
	 * @package Amnesty
	 *
	 * @return array
	 */
	function get_death_penalty_status_descriptions(): array {
		return array_map(
			function ( $status ) {
				return $status['description'];
			},
			get_death_penalty_statuses()
		);
	}
}

if ( ! function_exists( 'get_death_penalty_status_desc' ) ) {
	/**
	 * Retrieve the death penalty status description for a term
	 *
	 * @package Amnesty
	 *
	 * @param WP_Term|stdClass $term the term to get the description of
	 *
	 * @return string
	 */
	function get_death_penalty_status_desc( $term ): string {
		return get_death_penalty_status( $term )['description'];
	}
}
