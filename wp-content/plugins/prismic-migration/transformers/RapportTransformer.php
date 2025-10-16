<?php

namespace transformers;

use utils\LinksUtils;
use utils\ReturnType;

class RapportTransformer extends DocTransformer
{
    public function parse($prismicDoc): array
    {
        $wp_post = parent::parse($prismicDoc);

        $data = $prismicDoc['data'];

        $wp_post['post_type'] = \Type::get_wp_post_type(\Type::DOCUMENT);
        $wp_post['post_status'] = 'publish';

        $wp_post['post_excerpt'] = $data['contenu'] ?? $data['accroche'] ?? '';
        $wp_post['meta_input']['upload_du_document'] = isset($data['document']) ? LinksUtils::processLink($data['document'], ReturnType::ID) : null;
        $wp_post['meta_input']['_upload_du_document'] = 'field_688c7478cfe59';
        $wp_post['meta_input']['type_libre'] = $data['typeLibre'] ?? '';
        $wp_post['meta_input']['_type_libre'] = 'field_689a05696c83f';
        $wp_post['meta_input']['ai_index'] = $data['aiIndex'] ?? '';
        $wp_post['meta_input']['_ai_index'] = 'field_689a05696c84f';
        $wp_post['meta_input']['document_private'] = isset($data['visibility']) && $data['visibility'] === 'member' ? '1' : '0';
        $wp_post['meta_input']['_document_private'] = 'field_27dmxqoc8t';

        if (isset($data['typeResource'])) {
            $wp_post['tax_terms']['document_type'] = match ($data['typeResource']) {
                'rapport' => 'rapport',
                'document' => 'document',
                "kit d'activisme" => 'kit-activisme',
                'fiche pédagogique' => 'fiche-pedagogique',
            };
        }

        return $wp_post;
    }
}
