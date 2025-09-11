<?php


use blocks\BlockMapper;

class ParagraphMapper extends BlockMapper
{
    protected function getBlockName(): string
    {
        return 'core/paragraph';
    }

    protected function getAttributes(): array
    {
        return [];
    }

    protected function getInnerBlocks(): array
    {
        return [];
    }

    protected function getInnerContent(): array
    {
        return [
            '<p>' . $this->prismicBlock['text'] . '</p>',
        ];
    }
}
