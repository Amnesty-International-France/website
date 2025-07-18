<?php

use blocks\BlockMapper;
use blocks\MapperFactory;
use utils\ImageDescCaptionUtils;

class WrapImageMapper extends BlockMapper {

	private array $blocks;
	public function __construct($prismicBlock) {
		parent::__construct($prismicBlock);
		$this->blocks = [];
		$obj = $prismicBlock['primary'];
		if( isset( $obj['picture']['url'] ) ) {
			$image = $obj['picture'];
			$url = $image['url'];
			$alt = $image['alt'] ?? '';
			$descCaption = ImageDescCaptionUtils::getDescAndCaption( $obj['image_legend'] ?? '' );
			$id = FileUploader::uploadMedia($url, $descCaption['caption'], $descCaption['description'], $alt);
			if( $id ) {
				$this->blocks[] = [
					'blockName' => 'amnesty-core/image',
					'attrs' => ['mediaId' => $id],
					'innerContent' => []
				];
			}
		}
		if( isset( $obj['content']) ) {
			$it = new ArrayIterator( $obj['content'] );
			while( $it->valid() ) {
				$contenu = $it->current();
				try {
					$mapper = MapperFactory::getInstance()->getRichTextMapper( $contenu, $it );
					if( $mapper !== null ) {
						$this->blocks[] = $mapper->map();
					}
				} catch (\Exception $e) {
					echo $e->getMessage().PHP_EOL;
				}

				$it->next();
			}
		}
	}

	protected function getBlockName(): string {
        return 'core/group';
    }

    protected function getAttributes(): array {
		return [];
    }

    protected function getInnerBlocks(): array {
		return $this->blocks;
    }

    protected function getInnerContent(): array {
		$content = [];
		$content[] = '<div class="wp-block-group">';
		foreach( $this->blocks as $block ) {
			$content[] = null;
		}
		$content[] = '</div>';
		return $content;
    }
}
