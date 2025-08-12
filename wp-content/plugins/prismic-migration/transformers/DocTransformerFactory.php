<?php

namespace transformers;

use Type;

class DocTransformerFactory {

	public static function getTransformer( Type $type ): DocTransformer {
		return match ($type) {
			Type::NEWS => new NewsTransformer(),
			Type::PAYS => new PaysTransformer(),
			Type::ARTICLE_CHRONIQUE => new ArticleChroniqueTransformer(),
			Type::FOCUS => new FocusTransformer(),
			Type::DOSSIER => new DossierTransformer(),
			Type::STRUCTURE_LOCALE => new StructureLocaleTransformer(),
			Type::PAGE_FROIDE => new PageFroideTransformer(),
			Type::EVENEMENT => new EvenementTransformer(),
			Type::PETITION => new PetitionTransformer(),
			Type::ACTION_SOUTIEN => new ActionSoutienTransformer(),
			Type::DOCUMENT => new RapportTransformer(),
			default => throw new \Exception("Not found transformer for type : $type->value")
		};
	}
}
