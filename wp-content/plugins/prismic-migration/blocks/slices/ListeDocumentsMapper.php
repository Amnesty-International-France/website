<?php

use blocks\BlockMapper;
use utils\LinksUtils;

class ListeDocumentsMapper extends BlockMapper {

	private array $docsId;

	public function __construct( $prismicBlock ) {
		parent::__construct($prismicBlock);
		$this->docsId = [];
		foreach ( $prismicBlock['items'] as $item ) {
			$title = $item['title'];
			$doc = $item['documentlink'];
			if( $doc['link_type'] === 'Media' || $doc['link_type'] === 'Web' ) {
				$url = $doc['url'];
				$name = $doc['name'] ?? null;
				$attachmentId = FileUploader::uploadMedia( $url, name: $name, title: $title );
				$this->docsId[] = $attachmentId;
			} else if( $doc['link_type'] === 'Document' ) {
				if( $doc['type'] === 'rapport' ) {
					$this->docsId[] = LinksUtils::generatePlaceHolderRapportId( $doc['uid'] );
				} else if( $doc['type'] !== 'broken_type' ) {
					$this->docsId[] = LinksUtils::generatePlaceHolderPostId( $doc['uid'] );
				}
			}
		}
	}

	protected function getBlockName(): string {
        return 'amnesty-core/download-go-further';
    }

    protected function getAttributes(): array {
        return [
			'title' => 'Documents et liens utiles',
			'fileIds' => $this->docsId
		];
    }

    protected function getInnerBlocks(): array {
        return [];
    }

    protected function getInnerContent(): array {
		return [];
    }
}
