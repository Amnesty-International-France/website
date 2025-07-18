<?php

namespace transformers;

use Type;
use utils\BrokenTypeException;
use utils\LinksUtils;
use utils\ReturnType;

class NewsTransformer extends DocTransformer {

    public function parse( $prismicDoc ): array {
		$wp_post = parent::parse( $prismicDoc );

		$data = $prismicDoc['data'];

		$terms = $this->getTerms( $prismicDoc );

		$informed_block = $this->createGetInformedBlock( $prismicDoc, $terms );

		if ( $informed_block ) {
			$wp_post['post_content'][] = [$informed_block];
		}

        if ( isset($data['authorName']) ) {
            $wp_post['post_author'] = $this->getAuthor($data['authorName']);
        }

		$wp_post['post_type'] = Type::get_wp_post_type(\Type::NEWS);

		$wp_post['post_category'] = $this->getCategories(array('actualites'));
		$wp_post['tax_terms'] = [
			'location' => array_filter( array_column($terms['countries'], 'slug'), static fn($s) => $s !== null ),
			'combat' => array_filter( array_column($terms['combats'], 'slug'), static fn($s) => $s !== null )
		];

		$subCat = $this->getSubCategory( $data );
		$wp_post['meta_input']['editorial_category'] = $subCat ?? '';
		$wp_post['meta_input']['_editorial_category'] = 'field_68248747c71a5';

		$this->addRelatedContent($prismicDoc, $wp_post);

		return $wp_post;
    }

	private function createGetInformedBlock( $prismicDoc, $terms ): array|null {
		$links = [];
		foreach ($terms['countries'] as $country) {
			$links[] = ['type' => 'pays', 'title' => $country['name'], 'url' => $country['url'], 'customLabel' => ''];
		}
		foreach ($terms['combats'] as $combat) {
			$links[] = ['type' => 'combat', 'title' => $combat['name'], 'url' => $combat['url'], 'customLabel' => ''];
		}
		foreach ($terms['dossiers'] as $dossier) {
			$links[] = ['type' => 'dossier', 'title' => $dossier['name'], 'url' => $dossier['url'], 'customLabel' => ''];
		}
		foreach ($terms['chroniques'] as $chronique) {
			$links[] = ['type' => 'libre', 'title' => $chronique['name'], 'url' => $chronique['url'], 'customLabel' => 'Chronique'];
		}
		if( isset($prismicDoc['data']['relatedResources']) ) {
			foreach ( $prismicDoc['data']['relatedResources'] as $related ) {
				$content = $related['relatedcontent'];
				if( isset($content['type'], $content['uid']) ) {
					$links[] = ['type' => 'libre', 'title' => LinksUtils::generatePlaceHolderDoc($content['type'], $content['uid'], ReturnType::NAME), 'url' => LinksUtils::generatePlaceHolderDoc($content['type'], $content['uid'], ReturnType::URL), 'customLabel' => 'Ressource liée'];
				}
			}
		}
		if( empty($links) ) {
			return null;
		}
		return [
			'blockName' => 'amnesty-core/get-informed',
			'attrs' => ['links' => $links],
			'innerBlocks' => [],
			'innerContent' => []
		];
	}

	private function getSubCategory( $data ) : string|null {
		$first = match ( $data['smallTitle'] ?? '') {
			'enquête' => 'enquetes',
			'entretien' => 'entretiens',
			'témoignage' => 'temoignages',
			'tribune' => 'tribunes',
			default => null
		};
		$second = match ( $data['smallTitle2'] ?? '') {
			'Portrait' => 'portraits',
			'Rapport' => 'rapports',
			default => null
		};
		return $first ?? $second;
	}
}
