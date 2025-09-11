<?php

get_header();

the_post();

$html_file = get_query_var('html_template_file');

if ($html_file && file_exists($html_file)) {
	echo do_blocks( file_get_contents($html_file) );
} else {
	the_content();
}

get_footer();
