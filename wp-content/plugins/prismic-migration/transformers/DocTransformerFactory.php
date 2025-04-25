<?php

namespace transformers;

class DocTransformerFactory
{

	public static function getTransformer($type): DocTransformer
	{
		return match ($type) {
			'news' => new NewsTransformer(),
			default => throw new \Exception("Not found transformer for type : $type")
		};
	}
}
