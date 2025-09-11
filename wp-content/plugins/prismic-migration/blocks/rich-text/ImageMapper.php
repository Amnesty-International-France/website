<?php

use blocks\BlockMapper;
use utils\ImageDescCaptionUtils;

class ImageMapper extends BlockMapper
{
    private int $mediaId;

    public function __construct($prismicBlock, ArrayIterator $iterator)
    {
        parent::__construct($prismicBlock);
        if (isset($prismicBlock['url'])) {
            $key = $iterator->key();
            $iterator->next();
            $url = $prismicBlock['url'];
            $alt = $prismicBlock['alt'] ?? '';
            $desc = '';
            $caption = '';
            if ($iterator->valid()) {
                $block = $iterator->current();
                if ($block['type'] === 'paragraph' && isset($block['spans'][0]) && $block['spans'][0]['type'] === 'label' && $block['spans'][0]['data']['label'] === 'imagelegende') {
                    $descCaption = ImageDescCaptionUtils::getDescAndCaption($block['text']);
                    $desc = $descCaption['description'];
                    $caption = $descCaption['caption'];
                } else {
                    $iterator->seek($key);
                }
            } else {
                $iterator->seek($key);
            }

            $this->mediaId = FileUploader::uploadMedia($url, $caption, $desc, $alt);
        }
    }

    protected function getBlockName(): string
    {
        return 'amnesty-core/image';
    }

    protected function getAttributes(): array
    {
        return isset($this->mediaId) ? [
            'mediaId' => $this->mediaId,
        ] : [];
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
