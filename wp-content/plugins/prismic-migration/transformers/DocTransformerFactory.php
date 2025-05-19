<?php

namespace transformers;

use Type;

class DocTransformerFactory
{

	public static function getTransformer( Type $type ): DocTransformer {
		return match ($type) {
			Type::NEWS => new NewsTransformer(),
			Type::PAYS => new PaysTransformer(),
			default => throw new \Exception("Not found transformer for type : $type->value")
		};
	}
}
