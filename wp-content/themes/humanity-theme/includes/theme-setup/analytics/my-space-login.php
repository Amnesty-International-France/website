<?php

declare(strict_types=1);

if (! function_exists('aif_is_my_space_page_for_tracking')) {
    function aif_is_my_space_page_for_tracking(): bool
    {
        if (! is_page() || is_preview()) {
            return false;
        }

        $current_page = get_queried_object();
        $parent_page = get_page_by_path('mon-espace');

        if (
            ! $current_page ||
            ! isset($current_page->ID) ||
            ! $parent_page ||
            ! isset($parent_page->ID)
        ) {
            return false;
        }

        if ((int) $current_page->ID === (int) $parent_page->ID) {
            return true;
        }

        return in_array((int) $parent_page->ID, get_post_ancestors((int) $current_page->ID), true);
    }
}

if (! function_exists('aif_should_output_my_space_login_tracking')) {
    function aif_should_output_my_space_login_tracking(): bool
    {
        if (is_admin() || ! is_user_logged_in()) {
            return false;
        }

        return aif_is_my_space_page_for_tracking();
    }
}

if (! function_exists('aif_output_my_space_login_tracking')) {
    function aif_output_my_space_login_tracking(): void
    {
        if (! aif_should_output_my_space_login_tracking()) {
            return;
        }
        ?>
		<script type="text/javascript">
			(function () {
				const cookieName = 'aif_mon_espace_login_tracked';
				const cookieExists = document.cookie
					.split(';')
					.some((cookie) => cookie.trim().startsWith(cookieName + '='));
				const cookieAttributes = [
					cookieName + '=1',
					'max-age=1800',
					'path=/',
					'SameSite=Lax',
				];

				if (window.location.protocol === 'https:') {
					cookieAttributes.push('Secure');
				}

				document.cookie = cookieAttributes.join('; ');

				if (cookieExists) {
					return;
				}

				window.dataLayer = window.dataLayer || [];
				window.dataLayer.push({
					event: 'login',
					method: 'mon_espace',
				});
			})();
		</script>
		<?php
    }
}

add_action('wp_footer', 'aif_output_my_space_login_tracking');
