<?php

use blocks\BlockMapper;
use utils\LinksUtils;
use utils\ReturnType;

class ListeDActionsMapper extends BlockMapper
{
    private array $blocks;

    public function __construct($prismicBlock)
    {
        parent::__construct($prismicBlock);
        $this->blocks = [];

        if (!empty($prismicBlock['primary']['list_title'])) {
            $this->blocks[] = [
                'blockName' => 'core/heading',
                'attrs' => ['level' => 2],
                'innerContent' => ['<h2 class="wp-block-heading">' . $this->prismicBlock['primary']['list_title'] . '</h2>'],
            ];
        }

        foreach ($prismicBlock['items'] as $item) {
            $this->blocks[] = [
                'blockName' => 'amnesty-core/card-image-text',
                'attrs' => [
                    'postId' => LinksUtils::processLink($item['action_link'], ReturnType::ID),
                    'direction' => 'horizontal',
                ],
                'innerContent' => [],
            ];
        }
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
