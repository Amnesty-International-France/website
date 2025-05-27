<?php

use blocks\BlockMapper;

class ChiffreClefMapper extends BlockMapper {

	private array $blocks;

	public function __construct( $prismicBlock ) {
		parent::__construct($prismicBlock);
		$this->blocks = [];

		foreach ( $prismicBlock['items'] as $item ) {
			$number = $item['number'];
			$suffix = $item['suffix'];
			if( $number !== null && $suffix !== null ) {
				$title = (string)$number . ' ' . $suffix;
			} else if( $number !=null ) {
				$title = (string)$number;
			} else if( $suffix != null ) {
				$title = $suffix;
			} else {
				$title = '';
			}
			$text = $item['label'];
			$this->blocks[] = [
				'blockName' => 'core/column',
				'attrs' => [],
				'innerBlocks' => [[
					'blockName' => 'amnesty-core/key-figure',
					'attrs' => [
						'title' => $title,
						'text' => $text
					],
					'innerBlocks' => [],
					'innerContent' => ['<div class="wp-block-amnesty-core-key-figure key-figure"><p class="title">' . $title . '</p><p class="text">' . $text . '</p></div>']
				]],
				'innerContent' => ['<div class="wp-block-column">', null, '</div>']
			];
		}
	}

	protected function getBlockName(): string {
        return 'amnesty-core/section';
    }

    protected function getAttributes(): array {
        return [
			'sectionSize' => 'small',
			'showTitle' => false,
			'fullWidth' => false
		];
    }

    protected function getInnerBlocks(): array {
		$content = [];
		$content[] = '<div class="wp-block-columns">';
		foreach ($this->blocks as $block) {
			$content[] = null;
		}
		$content[] = '</div>';
        return [[
			'blockName' => 'core/columns',
			'attrs' => [],
			'innerBlocks' => $this->blocks,
			'innerContent' => $content
		]];
    }

    protected function getInnerContent(): array {
		return [null];
    }
}
