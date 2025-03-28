<?php

declare( strict_types = 1 );

if( !function_exists( 'calculate_reading_time' ) ) {
	function calculate_reading_time() {
		$content = get_post_field( 'post_content', get_the_ID() );
		$word_count = str_word_count( strip_tags( $content ) );
		$reading_speed = 200;
		return ceil( $word_count / $reading_speed );
	}
}
