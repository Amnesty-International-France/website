<?php

declare(strict_types=1);

use Yoast\WP\SEO\Values\Open_Graph\Images;

if (! function_exists('amnesty_default_opengraph_image')) {

    function amnesty_default_opengraph_image(Images $images)
    {
        if (! is_singular()) {
            return;
        }

        $has_yoast_image = get_post_meta(get_the_ID(), '_yoast_wpseo_opengraph-image', true);
        if (has_post_thumbnail() || ! empty($has_yoast_image)) {
            return;
        }

        $default_image_url = get_template_directory_uri() . '/assets/images/default-press-release.png';
        $images->add_image($default_image_url);
    }
}

add_filter('wpseo_add_opengraph_images', 'amnesty_default_opengraph_image');
