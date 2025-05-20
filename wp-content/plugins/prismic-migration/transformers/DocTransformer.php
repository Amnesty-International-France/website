<?php

namespace transformers;
use blocks\MapperFactory;
use Exception;
use utils\ImageDescCaptionUtils;
use utils\LinksUtils;
use utils\ReturnType;

abstract class DocTransformer {

	public function parse( $prismicDoc ): array {
		$wp_post = [];

		$data = $prismicDoc['data'];

		$chapoContent = isset($data['chapo']) ? trim( implode( " ", array_column( $data['chapo'], 'text')) ) : '';

		$chapoBlock = !empty( $chapoContent) ? array(MapperFactory::getInstance()->getRichTextMapper([
			'type' => 'chapo',
			'text' => $chapoContent,
			'spans' => []
		], new \ArrayIterator())->map()) : [];

		$contenuBlocks = [];
		$itContenu = isset($data['contenu']) ? new \ArrayIterator( $data['contenu'] ) : new \ArrayIterator();
		while( $itContenu->valid() ) {
			$contenu = $itContenu->current();
			try {
				$mapper = MapperFactory::getInstance()->getRichTextMapper( $contenu, $itContenu );
				if( $mapper !== null ) {
					$contenuBlocks[] = $mapper->map();
				}
			} catch (\Exception $e) {
				echo $e->getMessage().PHP_EOL;
			}

			$itContenu->next();
		}

		$slicesBlocks = [];
		foreach( $data['contenuEtendu'] as $slice ) {
			try {
				$mapper = MapperFactory::getInstance()->getSliceMapper($slice);
				if( $mapper !== null) {
					$slicesBlocks[] = $mapper->map();
				}
			} catch (\Exception $e) {
				echo $e->getMessage().PHP_EOL;
			}
		}

		$wp_post['post_content'] = wp_slash(serialize_blocks(array_merge($chapoBlock, $contenuBlocks, $slicesBlocks)));

		if( $data['accroche'] !== null ) {
			$wp_post['post_excerpt'] = $data['accroche'];
		}

		$wp_post['post_date'] = (new \DateTime($data['datePub'] ?? $prismicDoc['last_publication_date']))->format('Y-m-d H:i:s');
		$wp_post['post_title'] = $data['title'][0]['text'];
		$wp_post['post_status'] = isset($data['visibility']) && $data['visibility'] === 'member' ? 'private' : 'publish';
		$wp_post['comment_status'] = 'closed';
		$wp_post['ping_status'] = 'closed';
		$wp_post['post_name'] = $prismicDoc['uid'];

		return $wp_post;
	}

	public function featuredImage( $prismicDoc ): array|false {
		$data = $prismicDoc['data'];

		if( ! isset($data['image']['url'])) {
			return false;
		}

		$desc = '';
		$legend = '';
		if( isset($data['legend']) ) {
			$descCaption = ImageDescCaptionUtils::getDescAndCaption( $data['legend'] );
		}
		return [
			'alt' => $data['image']['alt'] ?? '',
			'imageUrl' => $data['image']['url'],
			'description' => isset($descCaption) ? $descCaption['description'] : $desc,
			'legend' => isset($descCaption) ? $descCaption['caption'] : $legend
		];
	}

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

			try {
				$name = LinksUtils::processLink( $content, ReturnType::NAME );
				$url = LinksUtils::processLink( $content );
			} catch ( Exception $e ) {
				continue;
			}

			$term = get_term_by( 'slug', \TaxMapper::mapCountry( $content['uid'] ), 'location');
			if( $term ) {
				$countries[] = ['slug' => $term->slug, 'name' => $name, 'url' => $url ];
			} else {
				$countries[] = ['slug' => null, 'name' => $name, 'url' => $url];
			}
		}

		foreach ( $prismicDoc['data']['thematique'] as $thematique ) {
			$content = $thematique['relatedcontent'];
			if( ! isset( $content['type'] ) || $content['type'] !== 'thematique' ) {
				continue;
			}

			try {
				$name = LinksUtils::processLink( $content, ReturnType::NAME );
				$url = LinksUtils::processLink( $content );
			} catch ( Exception $e ) {
				continue;
			}

			$term = get_term_by( 'slug', \TaxMapper::mapCombat( $content['uid'] ), 'combat');
			if( $term ) {
				$combats[] = ['slug' => $term->slug, 'name' => $name, 'url' => $url];
			} {
				$combats[] = ['slug' => null, 'name' => $name, 'url' => $url];
			}
		}

		foreach ( $prismicDoc['data']['dossier'] as $dossier ) {
			$content = $dossier['relatedcontent'];
			if( ! isset( $content['type'] ) ) {
				continue;
			}

			try {
				$name = LinksUtils::processLink( $content, ReturnType::NAME );
				$url = LinksUtils::processLink( $content );
			} catch ( Exception $e ) {
				continue;
			}

			if( $content['type'] === 'thematique' ) {
				$term = get_term_by( 'slug', \TaxMapper::mapCombat( $content['uid'] ), 'combat');
				if( $term ) {
					$combats[] = ['slug' => $term->slug, 'name' => $name, 'url' => $url];
				} else {
					$combats[] = ['slug' => null, 'name' => $name, 'url' => $url];
				}
			} else if( $content['type'] === 'dossier' ) {
				$dossiers[] = ['name' => $name, 'url' => $url];
			}
		}

		foreach ( $prismicDoc['data']['chronique'] as $chronique ) {
			$content = $chronique['relatedcontent'];

			if( ! isset( $content['type'] ) || $content['type'] !== 'chronique' ) {
				continue;
			}

			try {
				$name = LinksUtils::processLink( $content, ReturnType::NAME );
				$url = LinksUtils::processLink( $content );
			} catch ( Exception $e ) {
				continue;
			}

			$chroniques[] = ['name' => $name, 'url' => $url];
		}

		return [
			'countries' => $countries,
			'combats' => $combats,
			'dossiers' => $dossiers,
			'chroniques' => $chroniques
		];
	}

}
