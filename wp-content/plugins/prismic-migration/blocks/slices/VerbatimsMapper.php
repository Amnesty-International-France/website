<?php

use blocks\BlockMapper;

class VerbatimsMapper extends BlockMapper {

	private array $blocks;

	public function __construct($prismicBlock) {
		parent::__construct($prismicBlock);
		$this->blocks = [];

		foreach ($prismicBlock['items'] as $item) {
			if( isset($item['picture']['url']) ) {
				$id = FileUploader::uploadMedia( $item['picture']['url'], alt: $item['picture']['alt'] ?? '' );
				$image = [
					'showImage' => true,
					'imageId' => $id
				];
			}
			$text = $item['quote'] ?? '';
			$author = ($item['firstname'] ?? '') . ($item['lastname'] ?? '') . ($item['position'] ? ', ' . $item['position'] : '');
			$base = [
				'quoteText' => $text,
				'author' => $author,
				'bgColor' => 'white'
			];
			$attrs = isset($image) ? array_merge($base, $image) : $base;
			$this->blocks[] = [
				'blockName' => 'amnesty-core/blockquote',
				'attrs' => $attrs,
				'innerContent' => []
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
