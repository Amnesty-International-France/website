<?php

use blocks\BlockMapper;

class AgirLegacyMapper extends BlockMapper
{
    private ReadAlsoMapper $readAlsoMapper;

    public function __construct($prismicBlock, $data)
    {
        parent::__construct($prismicBlock);
        $this->readAlsoMapper = new ReadAlsoMapper($prismicBlock, $data);
    }

    protected function getBlockName(): string
    {
        return 'amnesty-core/agir-legacy';
    }

    protected function getAttributes(): array
    {
        return $this->readAlsoMapper->getAttributes();
    }

    protected function getInnerBlocks(): array
    {
        return [];
    }

    protected function getInnerContent(): array
    {
        return [];
    }
}
