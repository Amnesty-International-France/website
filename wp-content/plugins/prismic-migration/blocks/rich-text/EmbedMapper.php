<?php

use blocks\BlockMapper;

class EmbedMapper extends BlockMapper {

	protected function getBlockName(): string {
		return 'amnesty-core/video';
	}

	protected function getAttributes(): array {
		return [
			'url' => $this->prismicBlock['oembed']['embed_url'],
			'title' => $this->prismicBlock['oembed']['title'] ?? ''
		];
	}

	protected function getInnerBlocks(): array {
		return [];
	}

	protected function getInnerContent(): array {
		return [];
	}
}
