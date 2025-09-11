<?php

use blocks\BlockMapper;
use utils\LinksUtils;
use utils\ReturnType;

class ListeDocumentsMapper extends BlockMapper
{
    private array $docsId;

    public function __construct($prismicBlock)
    {
        parent::__construct($prismicBlock);
        $this->docsId = [];
        foreach ($prismicBlock['items'] as $item) {
            $title = $item['title'];
            $doc = $item['documentlink'];
            if ($doc['link_type'] === 'Media') {
                $url = $doc['url'];
                $name = $doc['name'] ?? null;
                $attachmentId = FileUploader::uploadMedia($url, name: $name, title: $title);
                $this->docsId[] = $attachmentId;
            } elseif ($doc['link_type'] === 'Web') {
                $url = $doc['url'];
                if (str_starts_with($url, 'https://www.amnesty.fr')) {
                    $parsed = parse_url($url, PHP_URL_PATH);
                    $uid = basename($parsed);
                    $parts = explode('/', trim($parsed, '/'));
                    $type = count($parts) > 1 ? $parts[count($parts) - 2] : 'page';
                    $this->docsId[] = LinksUtils::generatePlaceHolderDoc($type, $uid, ReturnType::ID);
                } elseif (str_starts_with($url, 'https://amnestyfr.cdn.prismic.io')) {
                    $id = \FileUploader::uploadMedia($doc['url']);
                    if ($id) {
                        $this->docsId[] = $id;
                    }
                }
            } elseif (($doc['link_type'] === 'Document') && isset($doc['type'], $doc['uid']) && $doc['type'] !== 'broken_type') {
                $this->docsId[] = LinksUtils::generatePlaceHolderDoc($doc['type'], $doc['uid'], ReturnType::ID);
            }
        }
    }

    protected function getBlockName(): string
    {
        return 'amnesty-core/download-go-further';
    }

    protected function getAttributes(): array
    {
        return [
            'title' => 'Documents et liens utiles',
            'fileIds' => $this->docsId,
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
