<?php

namespace transformers;

use Type;

class PortraitTransformer extends DocTransformer
{
    public function existsQueryParams(): array
    {
        $parent_page = get_or_create_page('page', 'personnes', 'Personnes');
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

        $wp_post['post_type'] = \Type::get_wp_post_type(Type::PORTRAIT);

        $parent_page = get_or_create_page('page', 'personnes', 'Personnes');
        if (is_int($parent_page)) {
            $wp_post['post_parent'] = $parent_page;
        } elseif (is_object($parent_page)) {
            $wp_post['post_parent'] = $parent_page->ID;
        }

        $data = $prismicDoc['data'];
        $wp_post['meta_input']['shorttitle'] = $data['shortTitle'];
        $wp_post['meta_input']['_shorttitle'] = 'field_68a488d0573ca';
        $wp_post['meta_input']['enable10jps'] = isset($data['enable10jps']) && $data['enable10jps'] === 'oui' ? '1' : '0';
        $wp_post['meta_input']['_enable10jps'] = 'field_68a488d0573cb';
        $wp_post['meta_input']['title10jps'] = $data['title10jps'] ?? '';
        $wp_post['meta_input']['_title10jps'] = 'field_68a48a21573cc';
        $wp_post['meta_input']['resume10jps'] = $data['resume10jps'] ?? '';
        $wp_post['meta_input']['_resume10jps'] = 'field_68a48a78573cd';

        if (isset($data['image10jps']['url'])) {
            $alt = $data['image10jps']['alt'] ?? '';
            $legend = $data['image10jps']['copyright'] ?? '';
            try {
                $wp_post['meta_input']['image10jps'] = \FileUploader::uploadMedia($data['image10jps']['url'], alt: $alt, legende: $legend);
            } catch (\Exception $e) {
            }
            $wp_post['meta_input']['_image10jps'] = 'field_68a48b25573ce';
        }

        return $wp_post;
    }

}
