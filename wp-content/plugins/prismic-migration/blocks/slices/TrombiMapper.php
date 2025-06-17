<?php

use blocks\BlockMapper;

class TrombiMapper extends BlockMapper {

	private array $blocks;

	public function __construct($prismicBlock) {
		parent::__construct($prismicBlock);
		$this->blocks = [];

		$key = 0;
		$itemsIt = new ArrayIterator($prismicBlock['items']);
		$cols = [];
		while ($itemsIt->valid()) {
			$item = $itemsIt->current();

			if( isset($item['image']['url']) ) {
				$image = [
					'blockName' => 'amnesty-core/image',
					'attrs' => ['className' => 'trombi', 'mediaId' => FileUploader::uploadMedia( $item['image']['url'], legende: $item['image']['copyright'] ?? '', alt: $item['image']['alt'] ?? '')],
					'innerContent' => []
				];
			}
			$name = [
				'blockName' => 'core/heading',
				'attrs' => ['level' => 5],
				'innerContent' => ['<h5 class="wp-block-heading">', $item['name'] ?? '', '</h5>']
			];
			$role = [
				'blockName' => 'core/paragraph',
				'attrs' => [],
				'innerContent' => ['<p>', $item['role'] ?? '', '</p>']
			];

			$cols[] = [
				'blockName' => 'core/column',
				'attrs' => [],
				'innerBlocks' => isset($image) ? [$image, $name, $role] : [$name, $role],
				'innerContent' => array_merge(['<div class="wp-block-column">'], isset($image) ? [null, null, null] : [null, null], ['</div>'])
			];

			if( $key === 2 ) {
				$this->blocks[] = [
					'blockName' => 'core/columns',
					'attrs' => [],
					'innerBlocks' => $cols,
					'innerContent' => array_merge(['<div class="wp-block-columns">'], array_map(static fn($v) => null, $cols), ['</div>'])
				];
				$key = 0;
				$cols = [];
			} else {
				$key++;
			}
			$itemsIt->next();
		}

		if ( $key > 0 ) {
			for( $i=0; $i <= 2-$key+1; $i++ ) {
				$cols[] = [
					'blockName' => 'core/column',
					'attrs' => [],
					'innerContent' => ['<div class="wp-block-column"></div>']
				];
			}
			$this->blocks[] = [
				'blockName' => 'core/columns',
				'attrs' => [],
				'innerBlocks' => $cols,
				'innerContent' => array_merge(['<div class="wp-block-columns">'], array_map(static fn($v) => null, $cols), ['</div>'])
			];
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
        return array_merge(['<div class="wp-block-group">'], array_map(static fn($v) => null, $this->blocks), ['</div>']);
    }
}
