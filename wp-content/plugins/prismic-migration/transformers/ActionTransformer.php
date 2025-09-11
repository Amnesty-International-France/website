<?php

namespace transformers;

use Type;

class ActionTransformer extends DocTransformer
{
    public function existsQueryParams(): array
    {
        $parent_page = get_or_create_page('page', 'actions', 'Actions');
        if (! $parent_page) {
            return [];
        }

        if (is_int($parent_page)) {
            return ['post_parent' => $parent_page];
        } else {
            return ['post_parent' => $parent_page->ID];
        }
    }

    public function parse($prismicDoc): array
    {
        $wp_post = (new PageFroideTransformer())->parse($prismicDoc);

        $wp_post['post_type'] = \Type::get_wp_post_type(Type::ACTION);

        $parent_page = get_or_create_page('page', 'actions', 'Actions');
        if (is_int($parent_page)) {
            $wp_post['post_parent'] = $parent_page;
        } elseif (is_object($parent_page)) {
            $wp_post['post_parent'] = $parent_page->ID;
        }

        return $wp_post;
    }

}
