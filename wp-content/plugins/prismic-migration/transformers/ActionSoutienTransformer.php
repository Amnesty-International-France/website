<?php

namespace transformers;

use blocks\MapperFactory;

class ActionSoutienTransformer extends DocTransformer {

	public function parse($prismicDoc): array {
		$wp_post = parent::parse($prismicDoc);
		$data = $prismicDoc['data'];

		$wp_post['post_type'] = \Type::get_wp_post_type(\Type::ACTION_SOUTIEN);

		$contenuBlocks = [];
		$itContenu = isset($data['contexte']) ? new \ArrayIterator( $data['contexte'] ) : new \ArrayIterator();
		while( $itContenu->valid() ) {
			$contenu = $itContenu->current();
			try {
				$mapper = MapperFactory::getInstance()->getRichTextMapper( $contenu, $itContenu );
				if( $mapper !== null ) {
					$contenuBlocks[] = $mapper->map();
				}
			} catch (\Exception $e) {
				echo $e->getMessage().PHP_EOL;
			}

			$itContenu->next();
		}

		array_splice($wp_post['post_content'], 0, 0, [$contenuBlocks]);

		$wp_post['meta_input']['type'] = 'action-soutien';
		$wp_post['meta_input']['_type'] = 'field_685aca87362cb';
		$wp_post['meta_input']['uidsf'] = $data['uidsf'] ?? '';
		$wp_post['meta_input']['_uidsf'] = 'field_685acdfe73c83';
		$wp_post['meta_input']['code_origine'] = $data['code_origine'] ?? '';
		$wp_post['meta_input']['_code_origine'] = 'field_685acdfe73c84';
		$wp_post['meta_input']['date_de_fin'] = isset($data['dateFin']) ? (new \DateTime($data['dateFin']))->format('Ymd') : '';
		$wp_post['meta_input']['_date_de_fin'] = 'field_685ace6573c85';
		$wp_post['meta_input']['objectif_signatures'] = $data['maxMessages'] ?? null;
		$wp_post['meta_input']['_objectif_signatures'] = 'field_685acd6d73c81';
		$wp_post['meta_input']['comment_max_length'] = $data['commentMaxLength'] ?? null;
		$wp_post['meta_input']['_comment_max_length'] = 'field_6867cd2430783';
		$wp_post['meta_input']['button_text'] = $data['CTAButtonText'] ?? '';
		$wp_post['meta_input']['_button_text'] = 'field_6867cd7130785';
		$wp_post['meta_input']['phone_required'] = isset($data['is_phone_required']) ? $data['is_phone_required'] === 'Oui' : false;
		$wp_post['meta_input']['_phone_required'] = 'field_6867cd3430784';
		$wp_post['meta_input']['form_contenu'] = $data['formContenu'][0]['text'] ?? '';
		$wp_post['meta_input']['_form_contenu'] = 'field_6867ccdb30782';


		$termsBlocks = [];
		$itTerms = isset($data['terms']) ? new \ArrayIterator( $data['terms'] ) : new \ArrayIterator();
		while( $itTerms->valid() ) {
			$contenu = $itTerms->current();
			try {
				$mapper = MapperFactory::getInstance()->getRichTextMapper( $contenu, $itTerms );
				if( $mapper !== null ) {
					$termsBlocks[] = $mapper->map();
				}
			} catch (\Exception $e) {
				echo $e->getMessage().PHP_EOL;
			}

			$itTerms->next();
		}

		$wp_post['meta_input']['terms'] = wp_slash(serialize_blocks($termsBlocks));
		$wp_post['meta_input']['_terms'] = 'field_6867cd7c30786';

		$terms = $this->getTerms( $prismicDoc );
		$wp_post['tax_terms'] = [
			'location' => array_filter( array_column($terms['countries'], 'slug'), static fn($s) => $s !== null ),
			'combat' => array_filter( array_column($terms['combats'], 'slug'), static fn($s) => $s !== null )
		];

		return $wp_post;
	}

}
