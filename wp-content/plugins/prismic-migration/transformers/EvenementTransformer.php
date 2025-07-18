<?php

namespace transformers;

use PrismicMigrationCli;

class EvenementTransformer extends DocTransformer {

	public function parse( $prismicDoc ): array {
		$wp_post = parent::parse( $prismicDoc );

		$wp_post['post_type'] = \Type::get_wp_post_type( \Type::EVENEMENT );

		$data = $prismicDoc['data'];

		$terms = $this->getTerms( $prismicDoc );

		$wp_post['tax_terms'] = [
			'location' => array_filter( array_column( $terms['countries'], 'slug' ), static fn( $s ) => $s !== null ),
			'combat'   => array_filter( array_column( $terms['combats'], 'slug' ), static fn( $s ) => $s !== null ),
		];

		$query = new \WP_Query(
			[
				'title'     => $wp_post['post_title'],
				'post_type' => \Type::get_wp_post_type( \Type::EVENEMENT ),
			]
		);

		if ( $query->have_posts() && ! \PrismicMigrationCli::$forceMod ) {
			$post_id = $query->next_post()->ID;
		} else {
			if ( isset( $data['contact'][0] ) ) {
				$organizer_id = $this->getOrCreateOrganizer( trim( $data['contact'][0]['text'] ) );
			}

			if ( isset( $data['adresse'] ) || isset( $data['ville'] ) ) {
				$venue_id = $this->getOrCreateVenue( $data );
			}
			$args = [
				'post_title'   => $wp_post['post_title'],
				'post_content' => '',
				'post_status'  => $wp_post['post_status'],
			];

			if ( isset( $data['national'] ) && $data['national'] === true ) {
				$args['meta_input']['_EventNational']  = 1;
				$args['meta_input']['__EventNational'] = 'field_685bfd654bfce';
			}

			if ( isset( $organizer_id ) ) {
				$args['EventOrganizerID'] = $organizer_id;
			}

			if ( isset( $venue_id ) ) {
				$args['EventVenueID'] = $venue_id;
			}

			if ( isset( $data['dateStart'] ) ) {
				$time                   = new \DateTime( $data['dateStart'] );
				$args['EventStartDate'] = $time->format( 'Y-m-d' );
				$args['EventStartTime'] = $time->format( 'H:i:s' );
			}

			if ( isset( $data['dateEnd'] ) ) {
				$time                 = new \DateTime( $data['dateEnd'] );
				$args['EventEndDate'] = $time->format( 'Y-m-d' );
				$args['EventEndTime'] = $time->format( 'H:i:s' );
			}

			if ( ! \PrismicMigrationCli::$dryrun ) {
				if($query->have_posts() && PrismicMigrationCli::$forceMod ) {
					$args['ID'] = $query->next_post()->ID;
				}
				$post_id = tribe_create_event( $args );
			} else {
				$post_id = 0;
			}
		}

		$wp_post['ID'] = $post_id;

		return $wp_post;
	}

	private function getOrCreateOrganizer( string $contact ): int {
		$query = new \WP_Query(
			[
				'title'     => $contact,
				'post_type' => 'tribe_organizer',
			]
		);

		if ( $query->have_posts() && ! \PrismicMigrationCli::$forceMod ) {
			return $query->next_post()->ID;
		}

		if ( \PrismicMigrationCli::$dryrun ) {
			return 0;
		}

		$args = [
			'Organizer' => $contact,
			'Email'     => $contact,
		];

		if( PrismicMigrationCli::$forceMod && $query->have_posts() ) {
			$args['ID'] = $query->next_post()->ID;
		}

		return tribe_create_organizer($args);
	}

	private function getOrCreateVenue( $data ) {
		$query = new \WP_Query(
			[
				'title'     => $data['adresse'] ?? $data['ville'],
				'post_type' => 'tribe_venue',
			]
		);

		if ( $query->have_posts() && ! \PrismicMigrationCli::$forceMod ) {
			return $query->next_post()->ID;
		}

		$args = [
			'Venue' => $data['adresse'] ?? $data['ville'] ?? '',
		];

		if ( isset( $data['adresse'] ) ) {
			$args['Address'] = $data['adresse'];
		}

		if ( isset( $data['ville'] ) ) {
			$args['City'] = $data['ville'];
		}

		if ( \PrismicMigrationCli::$dryrun ) {
			return 0;
		}

		if( PrismicMigrationCli::$forceMod && $query->have_posts() ) {
			$args['ID'] = $query->next_post()->ID;
		}

		$id = tribe_create_venue( $args );

		if ( $id !== false ) {
			if ( isset( $data['geocode']['latitude'] ) ) {
				update_post_meta( $id, '_VenueLatitude', $data['geocode']['latitude'] );
			}
			if ( isset( $data['geocode']['longitude'] ) ) {
				update_post_meta( $id, '_VenueLongitude', $data['geocode']['longitude'] );
			}
		}

		return $id;
	}

}
