<?php

use blocks\BlockMapper;
use utils\LinksUtils;
use utils\ReturnType;

class EncadreLienMapper extends BlockMapper
{
    private bool $intern;
    private $value;
    private $text;

    public function __construct($prismicBlock)
    {
        parent::__construct($prismicBlock);
        $this->text = $prismicBlock['primary']['content'] ?? '';
        $this->intern = true;
        $data = $prismicBlock['primary']['link'];
        try {
            $this->value = LinksUtils::processLink($data, ReturnType::ID);
            if ($data['link_type'] === 'Web' && !str_starts_with($data['url'], 'https://www.amnesty.fr') && !str_starts_with($data['url'], 'https://amnestyfr.cdn.prismic.io')) {
                $this->intern = false;
            }
        } catch (Exception $e) {
            $this->intern = false;
            $this->value = '#';
        }
    }

    protected function getBlockName(): string
    {
        return 'amnesty-core/read-also';
    }

    protected function getAttributes(): array
    {
        if ($this->intern) {
            return [
                'postId' => $this->value,
            ];
        }
        return [
            'linkType' => 'external',
            'externalLabel' => $this->text,
            'externalUrl' => $this->value,
        ];
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
