<?php

use blocks\BlockMapper;
use blocks\MapperFactory;

class MiseAJourMapper extends BlockMapper {

	private array $blocks;

	public function __construct($prismicBlock) {
		parent::__construct($prismicBlock);
		$this->blocks = [];

		foreach ($prismicBlock['items'] as $item) {
			if( isset($item['timelinedate']) ) {
				$this->blocks[] = (new HeadingMapper(['type' => 'heading4', 'text' => $item['timelinedate']]))->map();
			}

			if( isset($item['timelinetitle']) ) {
				$this->blocks[] = (new HeadingMapper(['type' => 'heading3', 'text' => $item['timelinetitle']]))->map();
			}

			$itContent = new ArrayIterator($item['timelinedescription']);
			while( $itContent->valid() ) {
				$mapper = MapperFactory::getInstance()->getRichTextMapper($itContent->current(), $itContent);
				if( $mapper !== null ) {
					$this->blocks[] = $mapper->map();
				}
				$itContent->next();
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
		return array_merge(['<div class="wp-block-group">'], array_map(static fn($v) => null, $this->blocks), ['</div>']);;
    }
}
