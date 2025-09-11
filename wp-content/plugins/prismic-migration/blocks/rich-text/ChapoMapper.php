<?php


use blocks\BlockMapper;

class ChapoMapper extends BlockMapper
{
    protected function getBlockName(): string
    {
        return 'amnesty-core/chapo';
    }

    protected function getAttributes(): array
    {
        return [
            'text' => $this->prismicBlock['text'],
        ];
    }

    protected function getInnerBlocks(): array
    {
        return [];
    }

    protected function getInnerContent(): array
    {
        return ['<div class="wp-block-amnesty-core-chapo chapo"><p class="text">' . $this->prismicBlock['text'] . '</p></div>'];
    }
}
