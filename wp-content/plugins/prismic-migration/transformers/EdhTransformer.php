<?php

declare(strict_types=1);

namespace transformers;

class EdhTransformer extends DocTransformer
{
    public function parse($prismicDoc): array
    {
        $wp_post = parent::parse($prismicDoc);

        $wp_post['post_type'] = \Type::get_wp_post_type(\Type::EDH);

        $wp_post['meta_input']['theme']  = $prismicDoc['data']['accroche'];
        $wp_post['meta_input']['_theme'] = 'field_689210a11445a';

        return $wp_post;
    }
}
