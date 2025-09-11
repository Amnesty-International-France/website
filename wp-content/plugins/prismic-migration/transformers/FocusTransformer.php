<?php

namespace transformers;

use Type;

class FocusTransformer extends DocTransformer
{
    public function parse($prismicDoc): array
    {
        $wp_post = (new NewsTransformer())->parse($prismicDoc);
        unset($wp_post['meta_input']['editorial_category'],
            $wp_post['meta_input']['_editorial_category'],
            $wp_post['post_category']);

        $wp_post['post_type'] = Type::get_wp_post_type(\Type::FOCUS);

        return $wp_post;
    }

}
