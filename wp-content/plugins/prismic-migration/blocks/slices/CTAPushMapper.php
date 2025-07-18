<?php

use blocks\BlockMapper;
use blocks\MapperFactory;
use utils\LinksUtils;

class CTAPushMapper extends BlockMapper {

	private string $title = '';
	private string $subtitle = '';
	private string $btnLabel = '';
	private string $btnLink = '';

	public function __construct($prismicBlock) {
		parent::__construct($prismicBlock);

		if( isset($prismicBlock['primary']) ) {
			$data = $prismicBlock['primary'];
			$this->btnLabel = $data['button_label'] ?? '';

			foreach( $data['content'] as $key => $contenu ) {
				if( $contenu['type'] ==='paragraph' ) {
					if( $key === 0 ) {
						$this->title .= $contenu['text'];
					} else {
						$this->subtitle .= $contenu['text'];
					}
				}
			}
			$this->btnLink = LinksUtils::processLink($data['button_link']);
		}
	}

	protected function getBlockName(): string {
        return 'amnesty-core/call-to-action';
    }

    protected function getAttributes(): array {
        return [
			'title' => $this->title,
			'subTitle' => $this->subtitle,
			'buttonLabel' => $this->btnLabel,
			'buttonLink' => $this->btnLink,
		];
    }

    protected function getInnerBlocks(): array {
        return [];
    }

    protected function getInnerContent(): array {
        return [];
    }
}
