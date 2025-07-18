<?php

namespace blocks;
abstract class BlockMapper {

	protected array $prismicBlock;

	public function __construct( $prismicBlock ) {
		$this->prismicBlock = $prismicBlock;
	}

	public function map(): array {
		return [
			'blockName' => $this->getBlockName(),
			'attrs' => $this->getAttributes(),
			'innerBlocks' => $this->getInnerBlocks(),
			'innerContent' => $this->getInnerContent()
		];
	}

	abstract protected function getBlockName(): string;

	abstract protected function getAttributes(): array;

	abstract protected function getInnerBlocks(): array;

	abstract protected function getInnerContent(): array;

}
