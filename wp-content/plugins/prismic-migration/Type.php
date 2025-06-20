<?php

enum Type: string {
	case ALL = '';
	case NEWS = 'news';
	case PAYS = 'pays';
	case ARTICLE_CHRONIQUE = 'articlechronique';
	case FOCUS = 'index';
	case DOSSIER = 'dossier';
	case STRUCTURE_LOCALE = 'structureMilitante';
	case PAGE_FROIDE = 'page';
	case EVENEMENT = 'evenement';

	public static function get_wp_post_type(Type $type): string|null {
		return match($type) {
			self::NEWS, self::ARTICLE_CHRONIQUE, self::DOSSIER => 'post',
			self::PAYS => 'fiche_pays',
			self::FOCUS => 'landmark',
			self::STRUCTURE_LOCALE => 'local-structures',
			self::PAGE_FROIDE => 'page',
			self::EVENEMENT => 'tribe_events',
			default => null,
		};
	}
}
