<?php

use blocks\BlockMapper;
use blocks\MapperFactory;

class SectionMapper extends BlockMapper
{
    private array $blocks;

    public function __construct($prismicBlock, ArrayIterator $iterator)
    {
        parent::__construct($prismicBlock);

        $this->blocks = [];

        while ($iterator->valid()) {
            $current = $iterator->current();
            $contenuIt = new ArrayIterator($current['primary']['contenu']);
            while ($contenuIt->valid()) {
                $currentContenu = $contenuIt->current();
                try {
                    $mapper = MapperFactory::getInstance()->getRichTextMapper($currentContenu, $contenuIt);
                    if ($mapper !== null) {
                        $this->blocks[] = $mapper->map();
                    }
                } catch (Exception $e) {
                    echo $e->getMessage().PHP_EOL;
                }
                $contenuIt->next();
            }

            $iterator->next();
            if ($iterator->valid() && $iterator->current()['slice_type'] !== 'section') {
                $iterator->seek($iterator->key() - 1);
                break;
            }
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
