<?php

use blocks\BlockMapper;

class ContenuHtmlMapper extends BlockMapper {

	private $contenu;

	public function __construct($prismicBlock, $contenu) {
		parent::__construct($prismicBlock);
		$this->contenu = $contenu;
	}

	protected function getBlockName(): string {
        return 'core/html';
    }

    protected function getAttributes(): array {
		return [
			'content' => $this->contenu
		];
    }

    protected function getInnerBlocks(): array {
        return [];
    }

    protected function getInnerContent(): array {
        return [$this->contenu];
    }
}
