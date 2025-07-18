<?php

use blocks\BlockMapper;
use utils\LinksUtils;

class LiensCartesMapper extends BlockMapper {

	private array $blocks;

	public function __construct($prismicBlock) {
		parent::__construct($prismicBlock);
		$this->blocks = [];

		$currentCards = [];
		foreach ( $prismicBlock['items'] as $key => $item ) {
			$title = $item['titre'] ?? '';
			$subtitle = $item['soustitre'] ?? '';
			$cat = $item['surtitre'] ?? '';

			if( isset( $item['img']['url']) ) {
				$id = FileUploader::uploadMedia( $item['img']['url'], legende: $item['img']['copyright'] ?? '', alt: $item['img']['alt'] ?? '' );
			}

			if( isset($item['lien']['key']) ) {
				$url = LinksUtils::processLink( $item['lien'] );
			} else if( isset(['media']['key']) ) {
				$url = LinksUtils::processLink( $item['media'] );
			} else {
				$url = '';
			}

			$currentCards[] = [
				'blockName' => 'core/column',
				'attrs' => [],
				'innerBlocks' => [[
					'blockName' => 'amnesty-core/article-card',
					'attrs' => [
						'is_custom' => true,
						'title' => $title,
						'date' => $subtitle,
						'main_category' => $cat,
						'thumbnail' => $id ?? 0,
						'permalink' => $url
					],
					'innerBlocks' => [],
					'innerContent' => []
				]],
				'innerContent' => ['<div class="wp-block-column">', null, '</div>']
			];

			if( $key % 3 === 2 ) {
				$this->saveColumns($currentCards);
				$currentCards = [];
			}
		}

		if( !empty( $currentCards ) ) {
			$this->saveColumns($currentCards);
		}
	}

	private function saveColumns( $cards ): void {
		$this->blocks[] = [
			'blockName' => 'core/columns',
			'attrs' => [],
			'innerBlocks' => $cards,
			'innerContent' => array_merge(['<div class="wp-block-columns">'], array_map(static fn($v) => null, $cards), ['</div>'])
		];
	}

	protected function getBlockName(): string {
        return 'core/group';
    }

    protected function getAttributes(): array {
        return [];
    }

    protected function getInnerBlocks(): array {
		return $this->blocks;
    }

    protected function getInnerContent(): array {
		return array_merge(['<div class="wp-block-group">'], array_map(static fn($v) => null, $this->blocks), ['</div>']);
    }
}
