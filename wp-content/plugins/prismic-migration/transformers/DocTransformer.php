<?php

namespace transformers;
use utils\LinksUtils;

abstract class DocTransformer {

	public abstract function parse( $prismicDoc ): array;

	public abstract function featuredImage( $prismicDoc ): array|false;

	public function getSeoAndOgData( $prismicDoc ): array {
		$res = [];
		$data = $prismicDoc['data'];
		if( isset($data['seoTitle']) ) {
			$res['_yoast_wpseo_title'] = $data['seoTitle'];
		}
		if( isset($data['description']) ) {
			$res['_yoast_wpseo_metadesc'] = $data['description'];
		}
		if( isset($data['ogTitle']) ) {
			$res['_yoast_wpseo_opengraph-title'] = $data['ogTitle'];
			$res['_yoast_wpseo_twitter-title'] = $data['ogTitle'];
		}
		if( isset($data['ogDescription']) ) {
			$res['_yoast_wpseo_opengraph-description'] = $data['ogDescription'];
			$res['_yoast_wpseo_twitter-description'] = $data['ogDescription'];
		}
		if( isset($data['ogImage']['url']) ) {
			$id = \FileUploader::uploadMedia( $data['ogImage']['url'], alt: $data['ogImage']['alt'] ?? '' );
			if( $id ) {
				$res['_yoast_wpseo_opengraph-image'] = wp_get_attachment_image_url( $id, 'large' );
				$res['_yoast_wpseo_opengraph-image-id'] = $id;
			}
		}
		if( isset($data['ogImageTwitter']['url'] )) {
			$id = \FileUploader::uploadMedia( $data['ogImageTwitter']['url'], alt: $data['ogImageTwitter']['alt'] ?? '' );
			if( $id ) {
				$res['_yoast_wpseo_twitter-image'] = wp_get_attachment_image_url( $id, 'large' );
				$res['_yoast_wpseo_twitter-image-id'] = $id;
			}
		}
		return $res;
	}

	function getCategories( array $categories ): array {
		$categoriesIds = [];
		foreach ( $categories as $category ) {
			$categoriesIds[] = get_category_by_slug( $category )->term_id;
		}
		return $categoriesIds;
	}

	function getAuthor( $authorName ) {
		if ( $authorName !== null ) {
			$id = username_exists( $authorName );
			if( !$id ) {
				$id = wp_create_user( $authorName, '' );
			}
			return $id;
		}
		return null;
	}

	function getTerms( array $prismicDoc ): array {
		$countries = [];
		$combats = [];
		$dossiers = [];
		$chroniques = [];

		foreach ( $prismicDoc['data']['country'] as $country ) {
			$content = $country['relatedcontent'];
			if ( ! isset( $content['type'] ) || $content['type'] !== 'pays' ) {
				continue;
			}

			$term = get_term_by( 'slug', \TaxMapper::mapCountry( $content['uid'] ), 'location');
			if( $term ) {
				$countries[] = ['slug' => $term->slug, 'name' => $term->name];
			}
		}

		foreach ( $prismicDoc['data']['thematique'] as $thematique ) {
			$content = $thematique['relatedcontent'];
			if( ! isset( $content['type'] ) || $content['type'] !== 'thematique' ) {
				continue;
			}

			$term = get_term_by( 'slug', \TaxMapper::mapCombat( $content['uid'] ), 'combat');
			if( $term ) {
				$combats[] = ['slug' => $term->slug, 'name' => $term->name];
			}
		}

		foreach ( $prismicDoc['data']['dossier'] as $dossier ) {
			$content = $dossier['relatedcontent'];
			if( ! isset( $content['type'] ) ) {
				continue;
			}

			if( $content['type'] === 'thematique' ) {
				$term = get_term_by( 'slug', \TaxMapper::mapCombat( $content['uid'] ), 'combat');
				if( $term ) {
					$combats[] = ['slug' => $term->slug, 'name' => $term->name];
				}
			} else if( $content['type'] === 'dossier' ) {
				$dossiers[] = ['slug' => $content['uid'], 'name' => LinksUtils::generatePlaceHolderPostName( $content['uid'] )];
			}
		}

		foreach ( $prismicDoc['data']['chronique'] as $chronique ) {
			$content = $chronique['relatedcontent'];

			if( ! isset( $content['type'] ) || $content['type'] !== 'chronique' ) {
				continue;
			}

			$chroniques[] = ['slug' => $content['uid'], 'name' => LinksUtils::generatePlaceHolderPostName( $content['uid'] )];
		}

		return [
			'countries' => $countries,
			'combats' => $combats,
			'dossiers' => $dossiers,
			'chroniques' => $chroniques
		];
	}

}
