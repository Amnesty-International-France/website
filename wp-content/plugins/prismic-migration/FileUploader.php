<?php

class FileUploader {
	public static function uploadMedia( $url, $legende = '', $description = '', $alt = '', $name = null, $title = null ) {
		if(PrismicMigrationCli::$dryrun) {
			return 1;
		}

		$url = sanitize_url( $url );

		if( !filter_var($url, FILTER_VALIDATE_URL) ) {
			throw new Exception( 'Invalid URL' );
		}

		/*
		$n1 = '637d60f0-7088-4e2a-ae57-3038f21397e6_Iran_ecoles.jpg';
		$n2 = 'amnestyfr0b360cb2-69f3-4714-89f1-68af43aa29ea_aif-voila-pourquoi-on-meurt-afr6231832016.pdf';
		$n3 = 'Nigeria-they-betrayed-us-indexAFR4484152018ENGLISH.pdf';
		$n4 = 'Petition_Ibrahim_10JPS.pdf';
		 */
		$url = strtok( $url, '?' );
		$file_name = substr( sanitize_file_name( transliterator_transliterate( 'Latin-ascii',$name ?? urldecode( basename( parse_url( $url, PHP_URL_PATH ) ) ) ) ), -100 );
		echo $file_name.PHP_EOL;
		$file_title = $title ?? self::format_title($file_name);
		$id = self::media_exists( $file_title );
		if( $id > 0 ) {
			return $id;
		}

		$tmp_file = download_url( $url );

		if( is_wp_error( $tmp_file ) ) {
			throw new Exception( print_r($tmp_file, true) );
		}

		if( empty( $file_name ) ) {
			@unlink( $tmp_file );
			throw new Exception( "Don't have a filename" );
		}

		$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		if( $file_ext === 'jp2' ) {
			try {
				/** @var Imagick $imagick */
				$imagick = new Imagick();
				$imagick->readImage($tmp_file);
				$imagick->setImageFormat('jpeg');

				$new_tmp_file = $tmp_file . '.jpg';
				$imagick->writeImage($new_tmp_file);
				$imagick->clear();
				$imagick->destroy();

				@unlink($tmp_file);
				$tmp_file = $new_tmp_file;
				$file_name = preg_replace('/\.jp2$/i', '.jpg', $file_name); // on modifie aussi le nom
			} catch (Exception $e) {
				@unlink($tmp_file);
				throw new Exception('Failed to convert JP2 to JPEG: ' . $e->getMessage());
			}
		}

		$file_type = wp_check_filetype( $file_name, null );
		if ( false === $file_type['ext'] || false === $file_type['type'] ) {
			@unlink( $tmp_file );
			throw new Exception( 'File type not supported' );
		}

		$file_array = [
			'name' => $file_name,
			'tmp_name' => $tmp_file
		];

		$post_data = [
			'post_excerpt' => $legende ?? '',
			'post_content' => $description ?? '',
			'post_title' => $file_title
		];

		$attachment_id = media_handle_sideload( $file_array, post_data: $post_data );

		if( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp_file );
			throw new Exception( print_r($attachment_id, true) );
		}

		add_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt ?? '' );
		return $attachment_id;
	}

	static function media_exists( $title ): int {
		return post_exists( title: self::format_title($title), type: 'attachment' );
	}

	private static function format_title( $title ) {
		$pos = strrpos($title, '.');
		if( $pos && $pos >= 0 ) {
			$title = substr($title, 0, $pos);
		}
		return $title;
	}
}
