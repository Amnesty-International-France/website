<?php

use blocks\BlockMapper;

class BlockQuoteMapper extends BlockMapper {

	private $citation;
	private $author;

	public function __construct( $block, $citation, $author ) {
		parent::__construct( $block );
		$this->citation = $citation;
		$this->author = $author;
	}
    protected function getBlockName(): string {
        return 'amnesty-core/blockquote';
    }

    protected function getAttributes(): array {
        return [
			'quoteText' => $this->citation,
			'author' => $this->author,
			'bgColor' => 'white'
		];
    }

    protected function getInnerBlocks(): array {
        return [];
    }

    protected function getInnerContent(): array {
        return [];
    }
}
