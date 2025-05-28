<?php

enum Type: string {
	case ALL = '';
	case NEWS = 'news';
	case PAYS = 'pays';
	case ARTICLE_CHRONIQUE = 'articlechronique';

	case FOCUS = 'index';
	case DOSSIER = 'dossier';

	public static function get_wp_post_type(Type $type): string|null {
		return match($type) {
			self::NEWS, self::ARTICLE_CHRONIQUE, self::DOSSIER => 'post',
			self::PAYS => 'fiche_pays',
			self::FOCUS => 'landmark',
			default => null,
		};
	}
}
