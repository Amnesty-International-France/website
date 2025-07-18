<?php

use blocks\BlockMapper;
use utils\LinksUtils;
use utils\ReturnType;

class ButtonMapper extends BlockMapper {

	private bool $intern;
	private string $label;
	private $value;

	public function __construct($prismicBlock, $label, $data) {
		parent::__construct($prismicBlock);
		$this->label = $label;
		$this->intern = true;
		if( isset($data) ) {
			try {
				$this->value = LinksUtils::processLink( $data, ReturnType::ID);

				if( $data['link_type'] === 'Web' && !str_starts_with($data['url'], 'https://www.amnesty.fr') && !str_starts_with($data['url'], 'https://amnestyfr.cdn.prismic.io') ) {
					$this->intern = false;
				}
			} catch ( Exception $e ) {
				$this->intern = false;
				$this->value = '#';
			}
		}
	}

	protected function getBlockName(): string {
		return 'amnesty-core/button';
	}

	protected function getAttributes(): array {
		$attrs = [
			'label' => $this->label,
			'alignment' => 'center'
		];
		if( $this->intern ) {
			$attrs['postId'] = $this->value;
		} else {
			$attrs['linkType'] = 'external';
			$attrs['externalUrl'] = $this->value;
		}
		return $attrs;
	}

	protected function getInnerBlocks(): array {
		return [];
	}

	protected function getInnerContent(): array {
		return [];
	}
}
