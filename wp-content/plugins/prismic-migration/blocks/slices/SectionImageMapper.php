<?php

use blocks\BlockMapper;
use blocks\MapperFactory;

class SectionImageMapper extends BlockMapper
{
    private array $blocks;

    public function __construct($prismicBlock)
    {
        parent::__construct($prismicBlock);
        $this->blocks = [];

        $obj = $prismicBlock['primary'];
        if (isset($obj['section_label'])) {
            $this->blocks[] = (new HeadingMapper(['type' => 'heading5', 'text' => $obj['section_label']]))->map();
        }
        if (isset($obj['section_title'])) {
            $this->blocks[] = (new HeadingMapper(['type' => 'heading5', 'text' => $obj['section_title']]))->map();
        }

        $columns = [];

        if (isset($obj['picture']['url'])) {
            $mediaId = FileUploader::uploadMedia($obj['picture']['url'], legende: $obj['picture']['copyright'] ?? '', alt: $obj['picture']['alt'] ?? '');
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

        if (isset($obj['content'])) {
            $itContenu = new ArrayIterator($obj['content']);
            $contenu = [];
            while ($itContenu->valid()) {
                $mapper = MapperFactory::getInstance()->getRichTextMapper($itContenu->current(), $itContenu);
                if ($mapper !== null) {
                    $contenu[] = $mapper->map();
                }
                $itContenu->next();
            }
            $columns[] = [
                'blockName' => 'core/column',
                'attrs' => [],
                'innerBlocks' => $contenu,
                'innerContent' => array_merge(['<div class="wp-block-column">'], array_map(static fn ($v) => null, $contenu), ['</div>']),
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
