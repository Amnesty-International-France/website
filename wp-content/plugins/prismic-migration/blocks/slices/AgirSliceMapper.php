<?php

use blocks\BlockMapper;

class AgirSliceMapper extends BlockMapper {

	private ReadAlsoMapper $readAlsoMapper;

	public function __construct($prismicBlock) {
		parent::__construct($prismicBlock);
		if( isset($prismicBlock['primary']['content'][0]) ) {
			$prismicBlock['text'] = $prismicBlock['primary']['content'][0]['text'];
		} else {
			$prismicBlock['text'] = '';
		}

		$this->readAlsoMapper = new ReadAlsoMapper($prismicBlock, $prismicBlock['primary']['link']);
	}

	protected function getBlockName(): string {
        return 'amnesty-core/agir-legacy';
    }

    protected function getAttributes(): array {
        return $this->readAlsoMapper->getAttributes();
    }

    protected function getInnerBlocks(): array {
        return [];
    }

    protected function getInnerContent(): array {
        return [];
    }
}
