<?php

use blocks\BlockMapper;

class SommaireMapper extends BlockMapper
{
    private array $blocks;

    public function __construct($prismicBlock)
    {
        parent::__construct($prismicBlock);
        $this->blocks = [];

        $obj = $prismicBlock['primary'];
        if (isset($obj['titre'])) {
            $this->blocks[] = (new HeadingMapper(['type' => 'heading3', 'text' => $obj['titre']]))->map();
        }

        $columns = [];

        if (isset($obj['img']['url'])) {
            $mediaId = FileUploader::uploadMedia($obj['img']['url'], alt: $obj['img']['alt'] ?? '');
            $image = [
                'blockName' => 'amnesty-core/image',
                'attrs' => ['mediaId' => $mediaId],
                'innerContent' => [],
            ];
            $columns[] = [
                'blockName' => 'core/column',
                'attrs' => [],
                'innerBlocks' => [$image],
                'innerContent' => ['<div class="wp-block-column">', null, '</div>'],
            ];
        }

        if (isset($obj['desc'])) {
            $paragraph = (new ParagraphMapper(['text' => $obj['desc']]))->map();
            $columns[] = [
                'blockName' => 'core/column',
                'attrs' => [],
                'innerBlocks' => [$paragraph],
                'innerContent' => ['<div class="wp-block-column">', null, '</div>'],
            ];
        }

        $this->blocks[] = [
            'blockName' => 'core/columns',
            'attrs' => [],
            'innerBlocks' => $columns,
            'innerContent' => array_merge(['<div class="wp-block-columns">'], array_map(static fn ($v) => null, $columns), ['</div>']),
        ];

    }

    protected function getBlockName(): string
    {
        return 'core/group';
    }

    protected function getAttributes(): array
    {
        return [];
    }

    protected function getInnerBlocks(): array
    {
        return $this->blocks;
    }

    protected function getInnerContent(): array
    {
        return array_merge(['<div class="wp-block-group">'], array_map(static fn ($v) => null, $this->blocks), ['</div>']);
    }
}
