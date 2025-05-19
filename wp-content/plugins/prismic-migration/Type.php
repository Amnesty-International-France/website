<?php

enum Type: string {
	case ALL = '';
	case NEWS = 'news';
	case PAYS = 'pays';

	public static function get_wp_post_type(Type $type): string|null {
		return match($type) {
			self::NEWS => 'post',
			self::PAYS => 'fiche_pays',
			default => null,
		};
	}
}
