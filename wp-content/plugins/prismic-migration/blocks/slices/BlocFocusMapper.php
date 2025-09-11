<?php

use blocks\BlockMapper;
use blocks\MapperFactory;

class BlocFocusMapper extends BlockMapper
{
    private array $blocks;
    private bool $showTitle = false;

    private string $title = '';

    public function __construct($prismicBlock)
    {
        parent::__construct($prismicBlock);
        $this->blocks = [];
        $it = isset($prismicBlock['items'][0]['texte']) ? new ArrayIterator($prismicBlock['items'][0]['texte']) : new ArrayIterator();
        while ($it->valid()) {
            $currentKey = $it->key();
            $contenu = $it->current();
            try {
                $mapper = MapperFactory::getInstance()->getRichTextMapper($contenu, $it);
                if ($mapper !== null) {
                    if ($currentKey === 0 && $mapper instanceof HeadingMapper && $mapper->getLevel() === 2) {
                        $this->showTitle = true;
                        $this->title = $mapper->prismicBlock['text'];
                    } else {
                        $this->blocks[] = $mapper->map();
                    }
                }
            } catch (\Exception $e) {
                echo $e->getMessage().PHP_EOL;
            }

            $it->next();
        }
    }

    protected function getBlockName(): string
    {
        return 'amnesty-core/section';
    }

    protected function getAttributes(): array
    {
        return array_merge([
            'showTitle' => $this->showTitle,
            'fullWidth' => false,
            'backgroundColor' => 'grey',
        ], ($this->showTitle ? ['title' => $this->title] : []));
    }

    protected function getInnerBlocks(): array
    {
        return $this->blocks;
    }

    protected function getInnerContent(): array
    {
        return array_map(static fn ($v) => null, $this->blocks);
    }
}
