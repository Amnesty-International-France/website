<?php

use blocks\BlockMapper;
use blocks\MapperFactory;

class AccordionMapper extends BlockMapper {

	private $blocks;

	public function __construct( $prismicBlock ) {
		parent::__construct($prismicBlock);
		$this->blocks = [];
		foreach ( $prismicBlock['items'] as $item ) {
			$block = [];
			$block['blockName'] = 'core/details';
			$block['attrs'] = [];

			$innerBlocks = [];
			$it = new ArrayIterator( $item['reponse'] );
			while ( $it->valid() ) {
				$contenu = $it->current();
				try {
					$mapper = MapperFactory::getInstance()->getRichTextMapper( $contenu, $it );
					if( $mapper !== null ) {
						$innerBlocks[] = $mapper->map();
					}
				} catch (\Exception $e) {
					echo $e->getMessage().PHP_EOL;
				}

				$it->next();
			}

			$block['innerBlocks'] = $innerBlocks;

			$content = [];
			$content[] = '<details class="wp-block-details"><summary>' . ( $item['question'][0]['text'] ?? '' ) . '</summary>';
			foreach ( $innerBlocks as $innerBlock ) {
				$content[] = null;
			}
			$content[] = '</details>';
			$block['innerContent'] = $content;
			$this->blocks[] = $block;
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
		foreach ( $this->blocks as $block ) {
			$content[] = null;
		}
		$content[] = '</div>';
		return $content;
    }
}
