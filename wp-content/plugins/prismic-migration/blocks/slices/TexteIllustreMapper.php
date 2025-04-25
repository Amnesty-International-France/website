<?php

use blocks\BlockMapper;
use blocks\MapperFactory;

class TexteIllustreMapper extends BlockMapper {

	private array $blocks;

	public function __construct($prismicBlock) {
		parent::__construct($prismicBlock);
		$this->blocks = [];
		$obj = $prismicBlock['primary'];

		$itTitle = isset($obj['title1']) ? new ArrayIterator( $obj['title1'] ) : new ArrayIterator();
		while ( $itTitle->valid() ) {
			$mapper = MapperFactory::getInstance()->getRichTextMapper( $itTitle->current(), $itTitle );
			if( $mapper !== null ) {
				$this->blocks[] = $mapper->map();
			}
			$itTitle->next();
		}

		$cols = [];
		if( isset( $obj['illustration']['url'] ) ) {
			$id = FileUploader::uploadMedia( $obj['illustration']['url'], alt: $obj['illustration']['alt'] ?? '' );
			$image = [
				'blockName' => 'amnesty-core/image',
				'attrs' => ['mediaId' => $id],
				'innerContent' => []
			];
			$cols[] = [
				'blockName' => 'core/column',
				'attrs' => [],
				'innerBlocks' => [$image],
				'innerContent' => ['<div class="wp-block-column">', null, '</div>']
			];
		}

		$content = [];
		$itContent = isset($obj['content']) ? new ArrayIterator( $obj['content'] ) : new ArrayIterator();
		while ( $itContent->valid() ) {
			$mapper = MapperFactory::getInstance()->getRichTextMapper( $itContent->current(), $itContent );
			if( $mapper !== null ) {
				$content[] = $mapper->map();
			}
			$itContent->next();
		}
		$cols[] = [
			'blockName' => 'core/column',
			'attrs' => [],
			'innerBlocks' => $content,
			'innerContent' => array_merge( ['<div class="wp-block-column">'], array_map(static fn($v) => null, $content), ['</div>'] )
		];

		$this->blocks[] = [
			'blockName' => 'core/columns',
			'attrs' => [],
			'innerBlocks' => $cols,
			'innerContent' => array_merge(['<div class="wp-block-columns">'], array_map(static fn($v) => null, $cols), ['</div>']),
		];

		$this->blocks[] = (new ButtonMapper($prismicBlock, $obj['button'] ?? '', $obj['link']))->map();
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
		return array_merge( ['<div class="wp-block-group">'], array_map(static fn($v) => null, $this->blocks), ['</div>']);
    }
}
