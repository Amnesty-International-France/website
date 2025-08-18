<?php

use blocks\BlockMapper;
use utils\LinksUtils;
use utils\ReturnType;

class ListeFilmsMapper extends BlockMapper {

	private array $blocks;

	public function __construct($prismicBlock) {
		parent::__construct($prismicBlock);
		$this->blocks = [];

		foreach ( $prismicBlock['items'] as $item ) {
			try {
				$this->blocks[] = [
					'blockName' => 'amnesty-core/card-image-text',
					'attrs' => [
						'postId' => LinksUtils::processLink($item['link'], ReturnType::ID),
						'direction' => 'horizontal',
					],
					'innerContent' => [],
				];
			} catch ( Exception $e ) {}

		}
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
        return array_merge(['<div class="wp-block-group">'], array_map( static fn($v) => null, $this->blocks), ['</div>']);
    }
}
