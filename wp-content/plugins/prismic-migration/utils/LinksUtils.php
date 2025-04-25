<?php

namespace utils;

use WP_Query;

class LinksUtils {

	public static function processLink( $data, bool $geturl = true ): string|int {
		if( $data['link_type'] === 'Document' ) {
            if( isset($data['type']) && $data['type'] === 'broken_type') {
                throw new BrokenTypeException();
            }
			if( isset($data['type']) && $data['type'] === 'rapport' && isset($data['uid']) ) {
				return $geturl ? self::generatePlaceHolderRapportUrl( $data['uid'] ) : self::generatePlaceHolderRapportId( $data['uid'] );
			}
			if( isset($data['type']) && $data['type'] === 'videohome' ) {
				return $geturl ? self::generatePlaceHolderVideoHomeUrl() : self::generatePlaceHolderVideoHomeId();
			}
			if( isset($data['uid']) ) {
				return $geturl ? self::generatePlaceHolderPostUrl( $data['uid'] ) : self::generatePlaceHolderPostId( $data['uid'] );
			}
			return '';
		} else if( $data['link_type'] === 'Media' ) {
			$name = $data['name'] ?? null;
			$id = \FileUploader::uploadMedia( $data['url'], name: $name );
			if( $id ) {
				return $geturl ? wp_get_attachment_url( $id ) : $id;
			}
		} else if( $data['link_type'] === 'Web' ) {
			$url = $data['url'];
			if( str_starts_with($url, 'https://www.amnesty.fr') ) {
				$uid = basename( parse_url( $url, PHP_URL_PATH ) );
				return $geturl ? self::generatePlaceHolderPostUrl( $uid ) : self::generatePlaceHolderPostId( $uid );
			} else if( str_starts_with($url, 'https://amnestyfr.cdn.prismic.io') ) {
				$id = \FileUploader::uploadMedia( $data['url'] );
				if( $id ) {
					return $geturl ? wp_get_attachment_url( $id ) : $id;
				}
			} else if( $geturl ) {
				return $url;
			}
		} else if( $data['link_type'] === 'Any' ) {
			return $geturl ? '' : 0;
		}
		throw new \Exception('Link-type unknowed.');
	}

	public static function generatePlaceHolderPostId( $uid ): string {
		return "%PRISMIC_IMPORT_ID_$uid%";
	}

	public static function generatePlaceHolderPostUrl( $uid ): string {
		return "%PRISMIC_IMPORT_URL_$uid%";
	}

	public static function generatePlaceHolderPostName( $uid ): string {
		return "%PRISMIC_IMPORT_NAME_$uid%";
	}

	public static function generatePlaceHolderRapportId( $uid ): string {
		return "%PRISMIC_IMPORT_RAPPORT_ID_$uid%";
	}

	public static function generatePlaceHolderRapportUrl( $uid ): string {
		return "%PRISMIC_IMPORT_RAPPORT_URL_$uid%";
	}

	public static function generatePlaceHolderRapportName( $uid ): string {
		return "%PRISMIC_IMPORT_RAPPORT_NAME_$uid%";
	}

	public static function generatePlaceHolderVideoHomeId() : string {
		return "%PRISMIC_IMPORT_VIDEOHOME_ID%";
	}

	public static function generatePlaceHolderVideoHomeUrl() : string {
		return "%PRISMIC_IMPORT_VIDEOHOME_URL%";
	}

	const PATTERN_URL = '/%PRISMIC_IMPORT_URL_([a-zA-Z0-9-_]+)%/';
	const PATTERN_ID = '/["]*%PRISMIC_IMPORT_ID_([a-zA-Z0-9-_.]+)%["]*/';

	public static function repairLinks( string &$content ): int {
		$count = 0;
		if(preg_match_all(self::PATTERN_URL, $content, $matches_url)) {
			foreach ($matches_url[0] as $placeholder) {
				$uid = trim($placeholder, '%');
				$uid = substr( $uid, strlen('PRISMIC_IMPORT_URL_') );
				$post = self::getPostByUid( $uid );

				if( $post !== false ) {
					$new_url = get_permalink( $post );
					$content = str_replace($placeholder, $new_url, $content);
					$count++;
				}
			}
		}

		if(preg_match_all(self::PATTERN_ID, $content, $matches_id)) {
			foreach ($matches_id[0] as $placeholder_quotes) {
				$placeholder = trim($placeholder_quotes, '"');
				$uid = trim($placeholder, '%');
				$uid = substr( $uid, strlen('PRISMIC_IMPORT_ID_') );
				$post = self::getPostByUid( $uid );

				if( $post !== false ) {
					$content = str_replace($placeholder_quotes, $post->ID, $content);
					$count++;
				}
			}
		}
		return $count;
	}

	private static function getPostByUid( $uid ): \WP_Post|false {
		$args = [
			'name' => $uid,
			'post_type' => 'any',
			'posts_per_page' => 1,
		];
		$query = new WP_Query( $args );

		if( $query->have_posts() ) {
			return $query->next_post();
		}

		return false;
	}
}

class BrokenTypeException extends \Exception {}
