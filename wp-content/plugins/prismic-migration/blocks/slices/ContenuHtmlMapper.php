<?php

use blocks\BlockMapper;

class ContenuHtmlMapper extends BlockMapper
{
    private $contenu;

    public function __construct($prismicBlock, $contenu)
    {
        parent::__construct($prismicBlock);
        $this->contenu = $contenu;
    }

    protected function getBlockName(): string
    {
        return 'core/html';
    }

    protected function getAttributes(): array
    {
        return [
            'content' => '<div class="wp-block-html">' . $this->contenu . '</div>',
        ];
    }

    protected function getInnerBlocks(): array
    {
        return [];
    }

    protected function getInnerContent(): array
    {
        return ['<div class="wp-block-html">' . $this->contenu . '</div>'];
    }
}
