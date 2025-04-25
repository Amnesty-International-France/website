<?php

use blocks\BlockMapper;
use utils\LinksUtils;

class ReadAlsoMapper extends BlockMapper {

	private bool $intern;

	private $value;
	private $text;
	private $url;

	public function __construct($paragraph, $data) {
		parent::__construct( $paragraph );
		$this->intern = true;
		if( $data['link_type'] === 'Document' ) {
			if( $data['type'] === 'broken_type') {
				$this->value = '#';
			} else if( $data['type'] === 'rapport' ) {
				$this->value = LinksUtils::generatePlaceHolderRapportId( $data['uid'] );
			} else if( $data['type'] === 'videohome' ) {
				$this->value = LinksUtils::generatePlaceHolderVideoHomeId();
			} else {
				$this->value = LinksUtils::generatePlaceHolderPostId( $data['uid'] );
			}
		} else if( $data['link_type'] === 'Media' ) {
			$this->processMedia( $data['url'], $data['name'] );
		} else if( $data['link_type'] === 'Web') {
			$url = $data['url'];
			if( str_starts_with($url, 'https://www.amnesty.fr') ) {
				$uid = basename( parse_url( $url, PHP_URL_PATH ) );
				$this->value = LinksUtils::generatePlaceHolderPostId( $uid );
			} else if( str_starts_with($url, 'https://amnestyfr.cdn.prismic.io') ) {
				$this->processMedia( $url );
			} else {
				$this->intern = false;
				$this->text = implode( array_slice( explode(': ', $paragraph['text']), 1 ) );
				$this->url = $url;
			}
		}
	}

	private function processMedia( $url, $name = null ) {
		$id = FileUploader::uploadMedia( $url, name: $name );
		if( $id ) {
			$this->value = $id;
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
			'externalUrl' => $this->url
		];
	}

	protected function getInnerBlocks(): array {
		return [];
	}

	protected function getInnerContent(): array {
		return [];
	}
}
