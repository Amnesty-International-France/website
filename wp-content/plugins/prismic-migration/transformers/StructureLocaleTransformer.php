<?php

namespace transformers;

use Type;

class StructureLocaleTransformer extends DocTransformer
{
    public function parse($prismicDoc): array
    {
        $wp_post = parent::parse($prismicDoc);

        $data = $prismicDoc['data'];

        $wp_post['post_type'] = Type::get_wp_post_type(\Type::STRUCTURE_LOCALE);

        $wp_post['meta_input']['latitude'] = $data['geocode']['latitude'] ?? 0;
        $wp_post['meta_input']['_latitude'] = 'field_684aca163aa91';

        $wp_post['meta_input']['longitude'] = $data['geocode']['longitude'] ?? 0;
        $wp_post['meta_input']['_longitude'] = 'field_684aca6a3aa92';

        $wp_post['meta_input']['adresse'] = $data['adresse'] ?? '';
        $wp_post['meta_input']['_adresse'] = 'field_684aca733aa93';

        $wp_post['meta_input']['ville'] = $data['ville'] ?? '';
        $wp_post['meta_input']['_ville'] = 'field_684aca883aa94';

        $wp_post['meta_input']['telephone'] = $data['phone'] ?? '';
        $wp_post['meta_input']['_telephone'] = 'field_684acabd3aa95';

        $wp_post['meta_input']['email'] = $data['email'] ?? '';
        $wp_post['meta_input']['_email'] = 'field_684acadd3aa96';

        $this->addRelatedContent($prismicDoc, $wp_post);

        return $wp_post;
    }

}
