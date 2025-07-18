<?php

use blocks\BlockMapper;
use utils\LinksUtils;

class CallToActionMapper extends BlockMapper {

	private string $title = '';
	private string $subtitle = '';
	private string $btnLabel = '';
	private string $btnLink = '';

	public function __construct($prismicBlock) {
		parent::__construct($prismicBlock);

		if( isset($prismicBlock['value'][0]) ) {
			$data = $prismicBlock['value'][0];
			$this->btnLabel = $data['textLink'] ?? '';
			foreach( $data['contenu'] as $contenu ) {
				if( str_starts_with($contenu['type'], 'heading') ) {
					$this->title .= $contenu['text'];
				}
				if( $contenu['type'] ==='paragraph' ) {
					$this->subtitle .= $contenu['text'];
				}
			}
			$this->btnLink = LinksUtils::processLink($data['actionLink']);
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
