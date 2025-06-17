<?php

use blocks\BlockMapper;
use utils\ImageDescCaptionUtils;

class ImageEtLegendeMapper extends BlockMapper {

	private int $mediaId;

	public function __construct( $prismicBlock ) {
		parent::__construct( $prismicBlock );

		$image = $prismicBlock['primary']['pic'];
		if( isset($image['url']) ) {
			$url = $image['url'];
			$alt = $image['alt'] ?? '';
			$descCaption = ImageDescCaptionUtils::getDescAndCaption( $prismicBlock['primary']['caption'] ?? '');
			$this->mediaId = FileUploader::uploadMedia($url, $descCaption['caption'], $descCaption['description'], $alt);
		}
	}

	protected function getBlockName(): string {
        return 'amnesty-core/image';
    }

    protected function getAttributes(): array {
        return isset($this->mediaId) ? [
			'mediaId' => $this->mediaId,
		] : [];
    }

    protected function getInnerBlocks(): array {
        return [];
    }

    protected function getInnerContent(): array {
        return [];
    }
}
