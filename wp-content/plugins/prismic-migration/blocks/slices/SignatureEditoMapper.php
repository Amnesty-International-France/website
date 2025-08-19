<?php

use blocks\BlockMapper;

class SignatureEditoMapper extends BlockMapper {

	private $attributes;

	public function __construct($prismicBlock) {
		parent::__construct($prismicBlock);
		if ( isset( $prismicBlock['primary']['editorpic']['url'] ) ) {
			$alt = $prismicBlock['primary']['editorpic']['alt'] ?? '';
			$id = FileUploader::uploadMedia( $prismicBlock['primary']['editorpic']['url'], alt: $alt );
			$image = [
				'showImage' => true,
				'imageId' => $id
			];
		}
		$base = [
			'quoteText' => '',
			'author' => $this->prismicBlock['primary']['editorname'] ?? '',
			'bgColor' => 'white'
		];
		$this->attributes = isset($image) ? array_merge($base, $image) : $base;
	}

	protected function getBlockName(): string {
        return 'amnesty-core/blockquote';
    }

    protected function getAttributes(): array {
        return $this->attributes;
    }

    protected function getInnerBlocks(): array {
        return [];
    }

    protected function getInnerContent(): array {
        return [];
    }
}
