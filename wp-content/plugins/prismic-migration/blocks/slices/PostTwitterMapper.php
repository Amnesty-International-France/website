<?php

use blocks\BlockMapper;

class PostTwitterMapper extends BlockMapper {

    protected function getBlockName(): string {
        return 'amnesty-core/blockquote';
    }

    protected function getAttributes(): array {
		return [
			'quoteText' => $this->prismicBlock['primary']['tweet'],
			'author' => 'X',
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
