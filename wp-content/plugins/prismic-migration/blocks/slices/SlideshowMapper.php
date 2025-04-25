<?php

use blocks\BlockMapper;

class SlideshowMapper extends BlockMapper {

	private array $mediaIds;

	public function __construct($prismicBlock) {
		parent::__construct($prismicBlock);
		$this->mediaIds = [];
		foreach( $prismicBlock['items'] as $item ) {
			$desc = $item['title'] ?? '';
			$caption = $item['subtitle'] ?? '';
			if( isset($item['image']['url']) ) {
				$id = FileUploader::uploadMedia( $item['image']['url'], $caption, $desc, $item['image']['alt'] ?? '' );
				$this->mediaIds[] = $id;
			}
		}
	}

	protected function getBlockName(): string {
        return 'amnesty-core/carousel';
    }

    protected function getAttributes(): array {
        return [
			'mediaIds' => $this->mediaIds
		];
    }

    protected function getInnerBlocks(): array {
		return [];
    }

    protected function getInnerContent(): array {
        return [];
    }
}
