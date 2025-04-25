<?php

use blocks\BlockMapper;
use blocks\MapperFactory;

class MaterielMapper extends BlockMapper {

	private array $blocks;

	public function __construct($prismicBlock) {
		parent::__construct($prismicBlock);
		$this->blocks = [];
		$this->blocks[] = (new HeadingMapper(['type' => 'heading3', 'text' => $prismicBlock['primary']['title'] ?? '']))->map();

		foreach ($prismicBlock['items'] as $item) {
			$this->blocks[] = $this->mapItem( $item );
		}

		if( isset($prismicBlock['primary']['actionlink']['link_type']) && $prismicBlock['primary']['actionlink']['link_type'] !== 'Any' ) {
			$this->blocks[] = (new ButtonMapper($prismicBlock, $prismicBlock['primary']['textlink'] ?? $prismicBlock['primary']['title'] ?? '', $prismicBlock['primary']['actionlink'] ))->map();
		}
	}

	private function mapItem( $item ): array {
		$columns = [];
		if( isset($item['image']['url']) ) {
			$mediaId = FileUploader::uploadMedia( $item['image']['url'], alt: $item['image']['alt'] ?? '' );
			$columns[] = [
				'blockName' => 'core/column',
				'attrs' => ['width' => "33.33%"],
				'innerBlocks' => [[
					'blockName' => 'amnesty-core/image',
					'attrs' => ['mediaId' => $mediaId],
					'innerContent' => []
				]],
				'innerContent' => ['<div class="wp-block-column" style="flex-basis:33.33%">', null, '</div>']
			];
		}

		$contenu = [];
		$itContenu = new ArrayIterator( $item['contenu'] );
		while ( $itContenu->valid() ) {
			$mapper = MapperFactory::getInstance()->getRichTextMapper($itContenu->current(), $itContenu);
			if( $mapper !== null ) {
				$contenu[] = $mapper->map();
			}
			$itContenu->next();
		}

		$columns[] = [
			'blockName' => 'core/column',
			'attrs' => ['width' => "66.66%"],
			'innerBlocks' => $contenu,
			'innerContent' => array_merge(['<div class="wp-block-column" style="flex-basis:66.66%">'], array_map(static fn($v) => null, $contenu), ['</div>'])
		];

		return [
			'blockName' => 'core/columns',
			'attrs' => [],
			'innerBlocks' => $columns,
			'innerContent' => array_merge(['<div class="wp-block-columns">'], array_map(static fn($v) => null, $columns), ['</div>'])
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
