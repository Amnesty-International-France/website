<?php

use blocks\BlockMapper;

class ListMapper extends BlockMapper
{
    private array $items;

    public function __construct($rich_text, $items)
    {
        parent::__construct($rich_text);
        $this->items = $items;
    }

    protected function getBlockName(): string
    {
        return 'core/list';
    }

    protected function getAttributes(): array
    {
        return [];
    }

    protected function getInnerBlocks(): array
    {
        $blocks = [];
        foreach ($this->items as $item) {
            $blocks[] = [
                'blockName' => 'core/list-item',
                'attrs' => [],
                'innerContent' => [
                    '<li>' . $item['text'] . '</li>',
                ],
            ];
        }
        return $blocks;
    }

    protected function getInnerContent(): array
    {
        $content = [];
        $content[] = '<ul class="wp-block-list">';
        foreach ($this->items as $item) {
            $content[] = null;
        }
        $content[] = '</ul>';
        return $content;
    }
}
