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
	case PETITION = 'petition';
	case ACTION_SOUTIEN = 'soutien';
	case DOCUMENT = 'rapport';
	case ACTION_MOBILISATION = 'actionmobilisation';
	case ACTION = 'action';
	case COMMUNIQUE_PRESSE = 'communiquePresse';

	public static function get_wp_post_type(Type $type): string|null {
		return match($type) {
			self::NEWS, self::ARTICLE_CHRONIQUE, self::DOSSIER => 'post',
			self::PAYS => 'fiche_pays',
			self::FOCUS => 'landmark',
			self::STRUCTURE_LOCALE => 'local-structures',
			self::PAGE_FROIDE, self::ACTION_MOBILISATION, self::ACTION => 'page',
			self::EVENEMENT => 'tribe_events',
			self::PETITION, self::ACTION_SOUTIEN => 'petition',
			self::DOCUMENT => 'document',
			self::COMMUNIQUE_PRESSE => 'press-release',
			default => null,
		};
	}
}
