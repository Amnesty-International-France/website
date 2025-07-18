<?php

namespace transformers;

use blocks\MapperFactory;

class PetitionTransformer extends DocTransformer {

	public function parse($prismicDoc): array {
		$wp_post = parent::parse($prismicDoc);
		$data = $prismicDoc['data'];

		$wp_post['post_type'] = \Type::get_wp_post_type(\Type::PETITION);

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

		$wp_post['meta_input']['type'] = 'petition';
		$wp_post['meta_input']['_type'] = 'field_685aca87362cb';
		$wp_post['meta_input']['uidsf'] = $data['uidsf'] ?? '';
		$wp_post['meta_input']['_uidsf'] = 'field_685acdfe73c83';
		$wp_post['meta_input']['code_origine'] = $data['code_origine'] ?? '';
		$wp_post['meta_input']['_code_origine'] = 'field_685acdfe73c84';
		$wp_post['meta_input']['date_de_fin'] = isset($data['dateFin']) ? (new \DateTime($data['dateFin']))->format('Ymd') : '';
		$wp_post['meta_input']['_date_de_fin'] = 'field_685ace6573c85';
		$wp_post['meta_input']['objectif_signatures'] = $data['maxSignatures'] ?? null;
		$wp_post['meta_input']['_objectif_signatures'] = 'field_685acd6d73c81';
		$wp_post['meta_input']['destinataire'] = $data['destinataire'] ?? '';
		$wp_post['meta_input']['_destinataire'] = 'field_685acdfe73c82';
		$wp_post['meta_input']['pdf_petition'] = isset($data['pdf_file']['url']) ? \FileUploader::uploadMedia($data['pdf_file']['url'], name: $data['pdf_file']['name'] ?? null) : null;
		$wp_post['meta_input']['_pdf_petition'] = 'field_685ace1673c83';
		$wp_post['meta_input']['punchline'] = $data['punchline'] ?? '';
		$wp_post['meta_input']['_punchline'] = 'field_685ace4c73c84';

		$lettreBlocks = [];
		$itLettre = isset($data['lettre']) ? new \ArrayIterator( $data['lettre'] ) : new \ArrayIterator();
		while( $itLettre->valid() ) {
			$contenu = $itLettre->current();
			try {
				$mapper = MapperFactory::getInstance()->getRichTextMapper( $contenu, $itLettre );
				if( $mapper !== null ) {
					$lettreBlocks[] = $mapper->map();
				}
			} catch (\Exception $e) {
				echo $e->getMessage().PHP_EOL;
			}

			$itLettre->next();
		}

		$wp_post['meta_input']['lettre'] = wp_slash(serialize_blocks($lettreBlocks));
		$wp_post['meta_input']['_lettre'] = 'field_685acdfe73c86';

		$terms = $this->getTerms( $prismicDoc );
		$wp_post['tax_terms'] = [
			'location' => array_filter( array_column($terms['countries'], 'slug'), static fn($s) => $s !== null ),
			'combat' => array_filter( array_column($terms['combats'], 'slug'), static fn($s) => $s !== null )
		];

		return $wp_post;
	}

}
