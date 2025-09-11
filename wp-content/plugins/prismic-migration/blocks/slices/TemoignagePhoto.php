<?php

use blocks\BlockMapper;

class TemoignagePhoto extends BlockMapper
{
    private $attributes;

    public function __construct($prismic_block)
    {
        parent::__construct($prismic_block);
        $item = $this->prismicBlock['primary'];
        if (isset($item['image']['id'])) {
            $id = FileUploader::uploadMedia($item['image']['url']);
            $image = [
                'showImage' => true,
                'imageId' => $id,
            ];
        }
        $base = [
            'quoteText' => $item['texte'],
            'author' => $item['nom'],
            'bgColor' => 'white',
        ];
        $this->attributes = isset($image) ? array_merge($base, $image) : $base;
    }

    protected function getBlockName(): string
    {
        return 'amnesty-core/blockquote';
    }

    protected function getAttributes(): array
    {
        return $this->attributes;
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
