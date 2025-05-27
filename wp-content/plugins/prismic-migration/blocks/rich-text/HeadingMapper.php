<?php


use blocks\BlockMapper;

class HeadingMapper extends BlockMapper {

	private $level;

	public function __construct( $prismicBlock ) {
		parent::__construct( $prismicBlock );
		$this->level = substr($prismicBlock['type'], strlen('heading'));
	}

    protected function getBlockName(): string {
        return 'core/heading';
    }

    protected function getAttributes(): array {
        return [
			'level' => (int) $this->level,
		];
    }

	protected function getInnerBlocks(): array {
		return [];
	}

	protected function getInnerContent(): array {
		return [
			'<h'. $this->level . ' class="wp-block-heading">' . $this->prismicBlock['text'] . '</h' . $this->level . '>'
		];
	}

	public function getLevel(): int {
		return (int) $this->level;
	}
}
