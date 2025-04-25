<?php

use blocks\BlockMapper;
use blocks\MapperFactory;

class BlocInfoMapper extends BlockMapper {

	private array $blocks;

	public function __construct( $prismicBlock ) {
		parent::__construct($prismicBlock);
		$this->blocks = [];
		$it = new ArrayIterator( $prismicBlock['value'][0]['contenu'] );
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
		return 'amnesty-core/section';
	}

	protected function getAttributes(): array {
		return [
			'showTitle' => false,
			'fullWidth' => false,
			'backgroundColor' => 'grey'
		];
	}

	protected function getInnerBlocks(): array {
		return $this->blocks;
	}

	protected function getInnerContent(): array {
		$content = [];
		$content[] = '<div class="wp-block-amnesty-core-section section-block large grey"><div class="section-block-content"><div class="section-block-inner-blocks-container sm">';
		foreach ($this->blocks as $block) {
			$content[] = null;
		}
		$content[] = '</div></div></div>';
		return $content;
	}
}
