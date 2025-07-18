<?php

use blocks\BlockMapper;
use utils\BrokenTypeException;
use utils\LinksUtils;
use utils\ReturnType;

class ReadAlsoMapper extends BlockMapper {

	private bool $intern;
	private $value;
	private $text;

	public function __construct($paragraph, $data) {
		parent::__construct( $paragraph );
		$this->intern = true;
		try {
			$this->value = LinksUtils::processLink( $data, ReturnType::ID);
			if( $data['link_type'] === 'Web' && !str_starts_with($data['url'], 'https://www.amnesty.fr') && !str_starts_with($data['url'], 'https://amnestyfr.cdn.prismic.io') ) {
				$this->intern = false;
				$this->text = implode( array_slice( explode(': ', $paragraph['text']), 1 ) );
			}
		} catch ( Exception $e ) {
			$this->value = '#';
		}
	}

	protected function getBlockName(): string {
		return 'amnesty-core/read-also';
	}

	protected function getAttributes(): array {
		if( $this->intern ) {
			return [
				'postId' => $this->value,
			];
		}
		return [
			'linkType' => 'external',
			'externalLabel' => $this->text,
			'externalUrl' => $this->value
		];
	}

	protected function getInnerBlocks(): array {
		return [];
	}

	protected function getInnerContent(): array {
		return [];
	}
}
