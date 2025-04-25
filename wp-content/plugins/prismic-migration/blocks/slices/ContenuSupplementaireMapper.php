<?php

use blocks\BlockMapper;
use blocks\MapperFactory;

class ContenuSupplementaireMapper extends BlockMapper {

	private array $blocks;

	public function __construct( $block ) {
		parent::__construct( $block );
		$this->blocks = [];
		$it = new ArrayIterator( $block['value'] );
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
