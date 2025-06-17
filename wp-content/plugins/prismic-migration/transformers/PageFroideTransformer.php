<?php

namespace transformers;

use Type;
use utils\LinksUtils;
use utils\ReturnType;

class PageFroideTransformer extends DocTransformer {

	public function parse($prismicDoc): array {
		$wp_post = parent::parse($prismicDoc);

		$terms = $this->getTerms($prismicDoc);

		$informed_block = $this->createGetInformedBlock( $prismicDoc, $terms );

		if ( $informed_block ) {
			$wp_post['post_content'][] = [$informed_block];
		}

		$wp_post['post_type'] = Type::get_wp_post_type(\Type::PAGE_FROIDE);

		$wp_post['terms'] = [
			'location' => array_filter( array_column($terms['countries'], 'slug'), static fn($s) => $s !== null ),
			'combat' => array_filter( array_column($terms['combats'], 'slug'), static fn($s) => $s !== null )
		];

		$this->addRelatedContent( $prismicDoc, $wp_post );

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

}
