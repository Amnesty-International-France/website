<?php

use blocks\BlockMapper;
use utils\LinksUtils;

class ButtonMapper extends BlockMapper {

	private bool $intern;
	private string $label;
	private $id;

	private $url;

	public function __construct($prismicBlock, $label, $data) {
		parent::__construct($prismicBlock);
		$this->label = $label;
		$this->intern = true;
		if( isset($data) ) {
			if( $data['link_type'] === 'Document' ) {
				if( $data['type'] === 'broken_type') {
					$this->intern = false;
					$this->url = '#';
				} else if( $data['type'] === 'rapport' ) {
					$this->id = LinksUtils::generatePlaceHolderRapportId( $data['uid'] );
				} else if( $data['type'] === 'videohome' ) {
					$this->id = LinksUtils::generatePlaceHolderVideoHomeId();
				} else {
					$this->id = LinksUtils::generatePlaceHolderPostId( $data['uid'] );
				}
			} else if( $data['link_type'] === 'Media' ) {
				$this->processMedia( $data['url'], $data['name'] );
			} else if( $data['link_type'] === 'Web') {
				$url = $data['url'];
				if( str_starts_with($url, 'https://www.amnesty.fr') ) {
					$uid = basename( parse_url( $url, PHP_URL_PATH ) );
					$this->id = LinksUtils::generatePlaceHolderPostId( $uid );
				} else if( str_starts_with($url, 'https://amnestyfr.cdn.prismic.io') ) {
					$this->processMedia( $url );
				} else {
					$this->intern = false;
					$this->url = $url;
				}
			}
		}
	}

	private function processMedia( $url, $name = null ) {
		$id = FileUploader::uploadMedia( $url, name: $name );
		if( $id ) {
			$this->id = $id;
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
			$attrs['postId'] = $this->id;
		} else {
			$attrs['linkType'] = 'external';
			$attrs['externalUrl'] = $this->url;
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
