<?php

namespace transformers;

use blocks\MapperFactory;
use utils\BrokenTypeException;
use utils\ImageDescCaptionUtils;
use utils\LinksUtils;

class NewsTransformer extends DocTransformer {

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

		$terms = $this->getTerms( $prismicDoc );

		$informed_block = $this->createGetInformedBlock( $prismicDoc, $terms ) ?? [];

        $wp_post['post_content'] = wp_slash(serialize_blocks(array_merge($chapoBlock, $contenuBlocks, $slicesBlocks, $informed_block)));

        if( $data['authorName'] !== null ) {
            $wp_post['post_author'] = $this->getAuthor($data['authorName']);
        }
		if( $data['accroche'] !== null ) {
			$wp_post['post_excerpt'] = $data['accroche'];
		}

		$wp_post['post_date'] = (new \DateTime($data['datePub']))->format('Y-m-d H:i:s');
		$wp_post['post_title'] = $data['title'][0]['text'];
		$wp_post['post_status'] = isset($data['visibility']) && $data['visibility'] === 'member' ? 'private' : 'publish';
		$wp_post['post_type'] = 'post';
		$wp_post['comment_status'] = 'closed';
		$wp_post['ping_status'] = 'closed';
		$wp_post['post_name'] = $prismicDoc['uid'];
		$wp_post['post_category'] = $this->getCategories(array('actualites'));
		$wp_post['terms'] = [
			'location' => array_column($terms['countries'], 'slug'),
			'combat' => array_column($terms['combats'], 'slug')
		];
		$wp_post['meta_input'] = [
			'amnesty_updated' => $data['dateUpdate'] !== null ? (new \DateTime($data['dateUpdate']))->format('Y-m-d H:i:s') : null,
			'prismic_json' => json_encode( $prismicDoc, JSON_UNESCAPED_UNICODE )
		];
		$this->addSeoAndOgData( $prismicDoc, $wp_post['meta_input'] );
		$this->addRelatedContent( $prismicDoc, $wp_post);

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

	private function addSeoAndOgData( $prismicDoc, &$metaInput ): void {
		$data = $prismicDoc['data'];
		if( isset($data['seoTitle']) ) {
			$metaInput['_yoast_wpseo_title'] = $data['seoTitle'];
		}
		if( isset($data['description']) ) {
			$metaInput['_yoast_wpseo_metadesc'] = $data['description'];
		}
		if( isset($data['ogTitle']) ) {
			$metaInput['_yoast_wpseo_opengraph-title'] = $data['ogTitle'];
			$metaInput['_yoast_wpseo_twitter-title'] = $data['ogTitle'];
		}
		if( isset($data['ogDescription']) ) {
			$metaInput['_yoast_wpseo_opengraph-description'] = $data['ogDescription'];
			$metaInput['_yoast_wpseo_twitter-description'] = $data['ogDescription'];
		}
		if( isset($data['ogImage']['url']) ) {
			$id = \FileUploader::uploadMedia( $data['ogImage']['url'], alt: $data['ogImage']['alt'] ?? '' );
			if( $id ) {
				$metaInput['_yoast_wpseo_opengraph-image'] = wp_get_attachment_image_url( $id, 'large' );
				$metaInput['_yoast_wpseo_opengraph-image-id'] = $id;
			}
		}
		if( isset($data['ogImageTwitter']['url'] )) {
			$id = \FileUploader::uploadMedia( $data['ogImageTwitter']['url'], alt: $data['ogImageTwitter']['alt'] ?? '' );
			if( $id ) {
				$metaInput['_yoast_wpseo_twitter-image'] = wp_get_attachment_image_url( $id, 'large' );
				$metaInput['_yoast_wpseo_twitter-image-id'] = $id;
			}
		}
	}

	private function addRelatedContent( $prismicDoc, &$wp_post ): void {
		$data = $prismicDoc['data'];
		if( isset($data['relatedArticle']) ) {
			$result = [];
			$count = 0;
			foreach ( $data['relatedArticle'] as $related ) {
				if( $count < 3 ) {
					$content = $related['relatedcontent'];
					try {
						$id = LinksUtils::processLink($content, false);
					} catch (BrokenTypeException $e) {}
					if( !empty($id) ) {
						$result[] = $id;
						$count++;
					}
				}
			}

			if( ! empty($result) ) {
				$wp_post['relatedArticles'] = $result;
			}
		}
	}

	private function createGetInformedBlock( $prismicDoc, $terms ): array|null {
		$links = [];
		foreach ($terms['countries'] as $country) {
			$links[] = ['type' => 'pays', 'title' => $country['name'], 'url' => LinksUtils::generatePlaceHolderPostUrl($country['slug']), 'customLabel' => ''];
		}
		foreach ($terms['combats'] as $combat) {
			$links[] = ['type' => 'combat', 'title' => $combat['name'], 'url' => LinksUtils::generatePlaceHolderPostUrl($combat['slug']), 'customLabel' => ''];
		}
		foreach ($terms['dossiers'] as $dossier) {
			$links[] = ['type' => 'dossier', 'title' => $dossier['name'], 'url' => LinksUtils::generatePlaceHolderPostUrl($dossier['slug']), 'customLabel' => ''];
		}
		foreach ($terms['chroniques'] as $chronique) {
			$links[] = ['type' => 'libre', 'title' => $chronique['name'], 'url' => LinksUtils::generatePlaceHolderPostUrl($chronique['slug']), 'customLabel' => 'Chronique'];
		}
		if( isset($prismicDoc['data']['relatedResources']) ) {
			foreach ( $prismicDoc['data']['relatedResources'] as $related ) {
				$content = $related['relatedcontent'];
				if( isset($content['type'], $content['uid']) && $content['type'] === 'rapport' ) {
					$links[] = ['type' => 'libre', 'title' => LinksUtils::generatePlaceHolderRapportName($content['uid']), 'url' => LinksUtils::generatePlaceHolderRapportUrl($content['uid']), 'customLabel' => 'Rapport'];
				}
			}
		}
		if( empty($links) ) {
			return null;
		}
		return [[
			'blockName' => 'amnesty-core/get-informed',
			'attrs' => ['links' => $links],
			'innerBlocks' => [],
			'innerContent' => []
		]];
	}
}
