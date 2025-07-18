<?php

namespace transformers;

use blocks\MapperFactory;
use Type;

class ArticleChroniqueTransformer extends DocTransformer {

	public function parse($prismicDoc): array {
		$wp_post = parent::parse($prismicDoc);

		$data = $prismicDoc['data'];

		$terms = $this->getTerms( $prismicDoc );

		array_splice( $wp_post['post_content'], 1, 0, [[$this->createSummaryBlock($data)]] );

		$lastBlocks = $wp_post['post_content'][count($wp_post['post_content']) - 1];
		if( count($lastBlocks) > 0 && $lastBlocks[count($lastBlocks)-1]['blockName'] === 'amnesty-core/call-to-action' ) {
			array_pop( $wp_post['post_content'][count($wp_post['post_content']) - 1] );
		}

		$wp_post['post_type'] = Type::get_wp_post_type(\Type::ARTICLE_CHRONIQUE);
		$wp_post['post_category'] = $this->getCategories(array('chroniques'));
		$wp_post['tax_terms'] = [
			'location' => array_filter( array_column($terms['countries'], 'slug'), static fn($s) => $s !== null ),
			'combat' => array_filter( array_column($terms['combats'], 'slug'), static fn($s) => $s !== null )
		];

		return $wp_post;
	}

	private function createSummaryBlock( $data ): array {
		if( isset($data['summarypic']['url']) ) {
			$mediaId = \FileUploader::uploadMedia( $data['summarypic']['url'], legende: $data['summarypic']['copyright'] ?? '', alt: $data['summarypic']['alt'] ?? '');
			$imgBlock = [
				'blockName' => 'amnesty-core/image',
				'attrs' => [
					'mediaId' => $mediaId
				],
				'innerContent' => []
			];
		}

		$contenuBlocks = [];
		$itContenu = isset($data['summary']) ? new \ArrayIterator( $data['summary'] ) : new \ArrayIterator();
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

		$cols = [
			'blockName' => 'core/columns',
			'attrs' => [],
			'innerBlocks' => [
				[
					'blockName' => 'core/column',
					'attrs' => ['width' => '40%'],
					'innerBlocks' => [$imgBlock ?? []],
					'innerContent' => array_merge(
						['<div class="wp-block-column" style="flex-basis:40%">'],
						[isset($imgBlock) ? null : ''],
						['</div>']
					)
				],
				[
					'blockName' => 'core/column',
					'attrs' => ['width' => '60%'],
					'innerBlocks' => $contenuBlocks,
					'innerContent' => array_merge(
						['<div class="wp-block-column" style="flex-basis:60%">'],
						array_map(static fn($v) => null, $contenuBlocks),
						['</div>']
					)
				]
			],
			'innerContent' => [
				'<div class="wp-block-columns">',
				null,
				null,
				'</div>'
			]
		];
		return [
			'blockName' => 'core/group',
			'attrs' => [
				'className' => 'import-summary'
			],
			'innerBlocks' => [
				[
					'blockName' => 'amnesty-core/section',
					'attrs' => [
						'sectionSize' => 'small',
						'showTitle' => false,
						'fullWidth' => false
					],
					'innerBlocks' => [$cols],
					'innerContent' => [null]
				]
			],
			'innerContent' => ['<div class="wp-block-group import-summary">', null, '</div>']
		];
	}

}
