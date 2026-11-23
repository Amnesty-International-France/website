<?php

declare(strict_types=1);

/**
 * Google Tag Manager container of the site.
 *
 * The snippet carries data-cfasync="false", so Cloudflare Rocket Loader leaves it in
 * place and it runs in document order, after the page_type push of the head.
 */

if (! function_exists('aif_output_gtm_container_script')) {
    function aif_output_gtm_container_script(): void
    {
        ?>
		<!-- Google Tag Manager -->
		<script data-cfasync="false">(function (w, d, s, l, i) {
				w[l] = w[l] || [];
				w[l].push({
					'gtm.start':
						new Date().getTime(), event: 'gtm.js'
				});
				var f = d.getElementsByTagName(s)[0],
					j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
				j.async = true;
				j.src =
					'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
				f.parentNode.insertBefore(j, f);
			})(window, document, 'script', 'dataLayer', 'GTM-MBQ25R');</script>
		<!-- End Google Tag Manager -->
		<?php
    }
}

if (! function_exists('aif_output_gtm_container_noscript')) {
    function aif_output_gtm_container_noscript(): void
    {
        ?>
		<!-- Google Tag Manager (noscript) -->
		<noscript>
			<iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MBQ25R"
			        height="0" width="0" style="display:none;visibility:hidden"></iframe>
		</noscript>
		<!-- End Google Tag Manager (noscript) -->
		<?php
    }
}

add_action('wp_head', 'aif_output_gtm_container_script', 1);
add_action('wp_body_open', 'aif_output_gtm_container_noscript', 1);
