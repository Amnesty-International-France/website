<?php

use blocks\BlockMapper;

class ExergueMapper extends BlockMapper {

	private string $text;

	public function __construct($prismicBlock, $text) {
		parent::__construct($prismicBlock);
		$this->text = $text;
	}

	protected function getBlockName(): string {
        return 'amnesty-core/blockquote';
    }

    protected function getAttributes(): array {
        return [
			'quoteText' => $this->text,
			'author' => '',
			'bgColor' => 'gray'
		];
    }

    protected function getInnerBlocks(): array {
        return [];
    }

    protected function getInnerContent(): array {
        return [];
    }
}
