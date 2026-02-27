<?php

/**
 * Functions file
 *
 * @package Amnesty
 */

/**
 * Analytics
 */
add_action('wp_head', function () {
    ?>
	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
				new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
			'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-MBQ25R');</script>
	<!-- End Google Tag Manager -->
<?php
}, 1);

add_action('wp_body_open', function () {
    ?>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MBQ25R"
					  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
<?php
}, 1);

add_action('wp_footer', function () {
    if (isset($_GET['gtm_type'], $_GET['gtm_name'])) {
        $type = sanitize_text_field($_GET['gtm_type']);
        $name = sanitize_text_field($_GET['gtm_name']);
        ?>
		<script type="text/javascript">
			window.dataLayer.push({
				'event': 'form_submit',
				'type': '<?php echo esc_js($type);?>',
				'name': '<?php echo esc_js($name);?>',
			});

			if (window.history.replaceState) {
				const url = new URL(window.location.href);
				url.searchParams.delete('gtm_type');
				url.searchParams.delete('gtm_name');
				window.history.replaceState({path: url.href}, '', url.href);
			}
		</script>
<?php
    }
});

/**
 * Theme root includes
 */
// region root
require_once realpath(__DIR__ . '/includes/root/compatibility.php');
require_once realpath(__DIR__ . '/includes/root/caching.php');
require_once realpath(__DIR__ . '/includes/root/localisation.php');
require_once realpath(__DIR__ . '/includes/root/accessibility.php');
require_once realpath(__DIR__ . '/includes/root/permalinks.php');
// endregion helpers

/**
 * Theme helper includes
 */
// region helpers
require_once realpath(__DIR__ . '/includes/helpers/attachments.php');
require_once realpath(__DIR__ . '/includes/helpers/query-filters.php');
require_once realpath(__DIR__ . '/includes/helpers/category-rewrite.php');
require_once realpath(__DIR__ . '/includes/helpers/taxonomy-rewrite.php');
require_once realpath(__DIR__ . '/includes/helpers/class-classnames.php');
require_once realpath(__DIR__ . '/includes/helpers/class-get-image-data.php');
require_once realpath(__DIR__ . '/includes/helpers/site.php');
require_once realpath(__DIR__ . '/includes/helpers/string-manipulation.php');
require_once realpath(__DIR__ . '/includes/helpers/variable-types.php');
require_once realpath(__DIR__ . '/includes/helpers/array-manipulation.php');
require_once realpath(__DIR__ . '/includes/helpers/actions-and-filters.php');
require_once realpath(__DIR__ . '/includes/helpers/taxonomies.php');
require_once realpath(__DIR__ . '/includes/helpers/blocks.php');
require_once realpath(__DIR__ . '/includes/helpers/header.php');
require_once realpath(__DIR__ . '/includes/helpers/frontend.php');
require_once realpath(__DIR__ . '/includes/helpers/localisation.php');
require_once realpath(__DIR__ . '/includes/helpers/post-single.php');
require_once realpath(__DIR__ . '/includes/helpers/metadata.php');
require_once realpath(__DIR__ . '/includes/helpers/media.php');
require_once realpath(__DIR__ . '/includes/helpers/pagination.php');
require_once realpath(__DIR__ . '/includes/helpers/archive.php');
require_once realpath(__DIR__ . '/includes/helpers/chronicle-helper.php');
require_once realpath(__DIR__ . '/includes/helpers/archive-chronicle-helpers.php');
require_once realpath(__DIR__ . '/includes/helpers/list-alignment.php');
require_once realpath(__DIR__ . '/includes/helpers/reading-time.php');
require_once realpath(__DIR__ . '/includes/helpers/page-the-chronicle-promo-helpers.php');
// endregion helpers

/**
 * Theme multisite includes
 */
// region multisite
require_once realpath(__DIR__ . '/includes/multisite/class-core-site-list.php');
require_once realpath(__DIR__ . '/includes/multisite/helpers.php');
// endregion multisite

/**
 * Theme network includes
 */
// region network
require_once realpath(__DIR__ . '/includes/admin/network/class-network-options.php');
// endregion network

/**
 * Theme admin includes
 */
// region admin
require_once realpath(__DIR__ . '/includes/admin/menu.php');
require_once realpath(__DIR__ . '/includes/admin/options-helpers.php');
require_once realpath(__DIR__ . '/includes/admin/cmb2-helpers.php');
require_once realpath(__DIR__ . '/includes/admin/list-table-filters.php');
require_once realpath(__DIR__ . '/includes/admin/post-filters.php');
require_once realpath(__DIR__ . '/includes/admin/theme-options.php');
require_once realpath(__DIR__ . '/includes/admin/theme-options/header.php');
require_once realpath(__DIR__ . '/includes/admin/theme-options/news.php');
require_once realpath(__DIR__ . '/includes/admin/theme-options/footer.php');
require_once realpath(__DIR__ . '/includes/admin/theme-options/social.php');
require_once realpath(__DIR__ . '/includes/admin/theme-options/pop-in.php');
require_once realpath(__DIR__ . '/includes/admin/theme-options/analytics.php');
require_once realpath(__DIR__ . '/includes/admin/theme-options/localisation.php');
require_once realpath(__DIR__ . '/includes/admin/theme-options/localisation/class-taxonomy-labels.php');
require_once realpath(__DIR__ . '/includes/admin/class-accessibility.php');
require_once realpath(__DIR__ . '/includes/admin/class-permalink-settings.php');
require_once realpath(__DIR__ . '/includes/admin/user-options.php');
require_once realpath(__DIR__ . '/includes/admin/settings-general.php');
require_once realpath(__DIR__ . '/includes/admin/landmarks-settings.php');
require_once realpath(__DIR__ . '/includes/admin/countries-settings.php');
require_once realpath(__DIR__ . '/includes/admin/block-validation.php');
require_once realpath(__DIR__ . '/includes/admin/block-display.php');
require_once realpath(__DIR__ . '/includes/admin/petitions-settings.php');
require_once realpath(__DIR__ . '/includes/admin/trainings-settings.php');
require_once realpath(__DIR__ . '/includes/admin/event-venue-auto-geocode.php');
require_once realpath(__DIR__ . '/includes/admin/event-national.php');
require_once realpath(__DIR__ . '/includes/admin/event-pagination-redirect.php');
require_once realpath(__DIR__ . '/includes/admin/local-structures-search.php');
// endregion admin

/**
 * Theme setup includes
 */
// region theme setup
require_once realpath(__DIR__ . '/includes/theme-setup/acf.php');
require_once realpath(__DIR__ . '/includes/theme-setup/text-domain.php');
require_once realpath(__DIR__ . '/includes/theme-setup/theme-json.php');
require_once realpath(__DIR__ . '/includes/theme-setup/categories.php');
require_once realpath(__DIR__ . '/includes/theme-setup/cookie-control-fix.php');
require_once realpath(__DIR__ . '/includes/theme-setup/no-js.php');
require_once realpath(__DIR__ . '/includes/theme-setup/rewrite-rules.php');
require_once realpath(__DIR__ . '/includes/theme-setup/disable-emoji-support.php');
require_once realpath(__DIR__ . '/includes/theme-setup/supports.php');
require_once realpath(__DIR__ . '/includes/theme-setup/wp-head.php');
require_once realpath(__DIR__ . '/includes/theme-setup/wp-head-cleanup.php');
require_once realpath(__DIR__ . '/includes/theme-setup/wp-body-open.php');
require_once realpath(__DIR__ . '/includes/theme-setup/body-class.php');
require_once realpath(__DIR__ . '/includes/theme-setup/media.php');
require_once realpath(__DIR__ . '/includes/theme-setup/class-desktop-nav-walker.php');
require_once realpath(__DIR__ . '/includes/theme-setup/class-mobile-nav-walker.php');
require_once realpath(__DIR__ . '/includes/theme-setup/navigation.php');
require_once realpath(__DIR__ . '/includes/theme-setup/scripts-and-styles.php');
require_once realpath(__DIR__ . '/includes/theme-setup/analytics/google-tag-manager.php');
require_once realpath(__DIR__ . '/includes/theme-setup/analytics/google-analytics.php');
require_once realpath(__DIR__ . '/includes/theme-setup/analytics/hotjar.php');
require_once realpath(__DIR__ . '/includes/theme-setup/analytics/vwo.php');
require_once realpath(__DIR__ . '/includes/theme-setup/analytics/meta-tags.php');
// endregion theme setup

/**
 * Theme KSES includes
 */
// region kses
require_once realpath(__DIR__ . '/includes/kses/checkbox-filter.php');
require_once realpath(__DIR__ . '/includes/kses/wp-kses-post.php');
// endregion kses

/**
 * Theme includes
 */
// region theme
require_once realpath(__DIR__ . '/includes/post-filters.php');
// endregion theme

/**
 * Theme block includes
 */
// region blocks
require_once realpath(__DIR__ . '/includes/blocks/block-category.php');
require_once realpath(__DIR__ . '/includes/blocks/meta.php');
require_once realpath(__DIR__ . '/includes/blocks/register.php');
require_once realpath(__DIR__ . '/includes/blocks/remove-stale-metadata.php');
require_once realpath(__DIR__ . '/includes/blocks/render-header-on-single.php');
// endregion blocks

// region fse-blocks
require_once realpath(__DIR__ . '/includes/full-site-editing/blocks/register.php');
// endregion fse-blocks

/**
 * Theme core block modification includes
 */
// region coreblocks
require_once realpath(__DIR__ . '/includes/core-blocks/image/filters.php');
require_once realpath(__DIR__ . '/includes/core-blocks/button/styles.php');
require_once realpath(__DIR__ . '/includes/core-blocks/post-content/render.php');
require_once realpath(__DIR__ . '/includes/core-blocks/query/pagination/next.php');
require_once realpath(__DIR__ . '/includes/core-blocks/query/pagination/numbers.php');
require_once realpath(__DIR__ . '/includes/core-blocks/query/pagination/previous.php');
require_once realpath(__DIR__ . '/includes/core-blocks/social-icons/styles.php');
// endregion coreblocks

/**
 * Theme block pattern includes
 */
// region patterns
require_once realpath(__DIR__ . '/includes/block-patterns/pattern-category.php');
require_once realpath(__DIR__ . '/includes/block-patterns/exclude-page-content-pattern-for-page.php');
// endregion patterns

/**
 * Theme post type includes
 */
// region post types
require_once realpath(__DIR__ . '/includes/post-types/post-type-helpers.php');
require_once realpath(__DIR__ . '/includes/post-types/abstract-class-post-type.php');
require_once realpath(__DIR__ . '/includes/post-types/pop-in.php');
require_once realpath(__DIR__ . '/includes/post-types/sidebar.php');
require_once realpath(__DIR__ . '/includes/post-types/countries.php');
require_once realpath(__DIR__ . '/includes/post-types/landmarks.php');
require_once realpath(__DIR__ . '/includes/post-types/local-structures.php');
require_once realpath(__DIR__ . '/includes/post-types/petitions.php');
require_once realpath(__DIR__ . '/includes/post-types/press-release.php');
require_once realpath(__DIR__ . '/includes/post-types/portraits.php');
require_once realpath(__DIR__ . '/includes/post-types/trainings.php');
require_once realpath(__DIR__ . '/includes/post-types/document.php');
require_once realpath(__DIR__ . '/includes/post-types/newsletter.php');
require_once realpath(__DIR__ . '/includes/post-types/edh.php');
require_once realpath(__DIR__ . '/includes/post-types/chronicle.php');
require_once realpath(__DIR__ . '/includes/post-types/actualities-my-space.php');
// endregion post types

/**
 * Theme taxonomy includes
 */
// region taxonomies
require_once realpath(__DIR__ . '/includes/taxonomies/taxonomy-filters.php');
require_once realpath(__DIR__ . '/includes/taxonomies/taxonomy-descriptions.php');
require_once realpath(__DIR__ . '/includes/taxonomies/abstract-class-taxonomy.php');
require_once realpath(__DIR__ . '/includes/taxonomies/class-taxonomy-content-types.php');
require_once realpath(__DIR__ . '/includes/taxonomies/class-taxonomy-landmark-categories.php');
require_once realpath(__DIR__ . '/includes/theme-setup/landmark-categories.php');
require_once realpath(__DIR__ . '/includes/taxonomies/class-taxonomy-locations.php');
require_once realpath(__DIR__ . '/includes/theme-setup/countries.php');
require_once realpath(__DIR__ . '/includes/taxonomies/class-taxonomy-keywords.php');
require_once realpath(__DIR__ . '/includes/theme-setup/keywords.php');
require_once realpath(__DIR__ . '/includes/taxonomies/class-taxonomy-combats.php');
require_once realpath(__DIR__ . '/includes/theme-setup/combats.php');
require_once realpath(__DIR__ . '/includes/taxonomies/class-taxonomy-document-types.php');
require_once realpath(__DIR__ . '/includes/taxonomies/class-taxonomy-document-democratic-types.php');
require_once realpath(__DIR__ . '/includes/taxonomies/class-taxonomy-document-instance-types.php');
require_once realpath(__DIR__ . '/includes/taxonomies/class-taxonomy-document-militant-types.php');
require_once realpath(__DIR__ . '/includes/theme-setup/document-types.php');
require_once realpath(__DIR__ . '/includes/theme-setup/document-democratic-types.php');
require_once realpath(__DIR__ . '/includes/theme-setup/document-instance-types.php');
require_once realpath(__DIR__ . '/includes/theme-setup/document-militant-types.php');
require_once realpath(__DIR__ . '/includes/taxonomies/custom-fields/precedence.php');
require_once realpath(__DIR__ . '/includes/taxonomies/yoast_breadcrumb_taxonomies.php');
// endregion taxonomies

/**
 * Theme query filter includes
 */
// region query filters
require_once realpath(__DIR__ . '/includes/query-filters/posts-where.php');
require_once realpath(__DIR__ . '/includes/query-filters/sort-order.php');
require_once realpath(__DIR__ . '/includes/query-filters/sticky-posts.php');
require_once realpath(__DIR__ . '/includes/query-filters/taxonomy-filters.php');
require_once realpath(__DIR__ . '/includes/query-filters/taxonomy-location-filters.php');
// endregion query filters

/**
 * Salesforce connector
 */
require_once realpath(__DIR__ . '/includes/salesforce/authentification.php');
require_once realpath(__DIR__ . '/includes/salesforce/data.php');
require_once realpath(__DIR__ . '/includes/salesforce/petition.php');
require_once realpath(__DIR__ . '/includes/salesforce/user.php');
require_once realpath(__DIR__ . '/includes/salesforce/case.php');
require_once realpath(__DIR__ . '/includes/salesforce/newsletter.php');

/**
 * Document module
 */
require_once realpath(__DIR__ . '/includes/document/rest_endpoint.php');

/**
 * Petition module
 */
require_once realpath(__DIR__ . '/includes/petitions/tables.php');
require_once realpath(__DIR__ . '/includes/petitions/create-petition.php');
require_once realpath(__DIR__ . '/includes/petitions/rest_endpoint.php');

/**
 * Urgent Action module
 */
require_once realpath(__DIR__ . '/includes/urgent-action/tables.php');

if (defined('WP_CLI') && WP_CLI) {
    require_once realpath(__DIR__ . '/includes/petitions/syncs.php');
    require_once realpath(__DIR__ . '/includes/urgent-action/syncs.php');
    require_once realpath(__DIR__ . '/includes/document/broken_list.php');
}

/**
 * Theme search includes
 */
// region search
require_once realpath(__DIR__ . '/includes/features/search/helpers.php');
require_once realpath(__DIR__ . '/includes/features/search/permalink.php');
require_once realpath(__DIR__ . '/includes/features/search/post-excerpt.php');
require_once realpath(__DIR__ . '/includes/features/search/query-vars.php');
require_once realpath(__DIR__ . '/includes/features/search/class-search-page.php');
require_once realpath(__DIR__ . '/includes/features/search/class-search-filters.php');
require_once realpath(__DIR__ . '/includes/features/search/class-search-results.php');
// endregion search

/**
 * Theme REST API includes
 */
// region rest api
require_once realpath(__DIR__ . '/includes/rest-api/class-category-list.php');
require_once realpath(__DIR__ . '/includes/rest-api/class-fetch-menus.php');
require_once realpath(__DIR__ . '/includes/rest-api/class-wordpress-seo.php');
require_once realpath(__DIR__ . '/includes/rest-api/post-list.php');
require_once realpath(__DIR__ . '/includes/rest-api/post-data.php');
require_once realpath(__DIR__ . '/includes/rest-api/users.php');
// endregion rest api

/**
 * Theme RSS Feed includes
 */
// region rss
require_once realpath(__DIR__ . '/includes/rss/filter-feed-by-term.php');
// endregion rss

/**
 * Theme SEO includes
 */
// region seo
require_once realpath(__DIR__ . '/includes/seo/base.php');
require_once realpath(__DIR__ . '/includes/seo/canonical.php');
require_once realpath(__DIR__ . '/includes/seo/language.php');
require_once realpath(__DIR__ . '/includes/seo/opengraph.php');
require_once realpath(__DIR__ . '/includes/seo/primary-term.php');
require_once realpath(__DIR__ . '/includes/seo/schema-breadcrumbs.php');
// endregion seo

/**
 * Theme User includes
 */
// region users
require_once realpath(__DIR__ . '/includes/users/class-users-controller.php');
require_once realpath(__DIR__ . '/includes/users/contact-methods.php');
require_once realpath(__DIR__ . '/includes/users/meta.php');
// endregion users

/**
 * Theme Jetpack includes
 */
// region jetpack
require_once realpath(__DIR__ . '/includes/jetpack/contact-form.php');
require_once realpath(__DIR__ . '/includes/jetpack/go-back-message.php');
require_once realpath(__DIR__ . '/includes/jetpack/sitemap.php');
// endregion jetpack

/**
 * Theme My Space includes
 */
// region my-space
require_once realpath(__DIR__ . '/includes/my-space/breadcrumb.php');
require_once realpath(__DIR__ . '/includes/my-space/template.php');
// endregion my-space

/**
 * Theme WooCommerce includes
 */
// region woocommerce
if (class_exists('\WooCommerce', false)) {
    // disable WooCommerce block templates -- it breaks lots of things in hybrid
    add_filter('woocommerce_has_block_template', '__return_false', 999);

    require_once realpath(__DIR__ . '/includes/admin/woo/theme-options.php');

    require_once realpath(__DIR__ . '/includes/woo/helpers.php');
    require_once realpath(__DIR__ . '/includes/woo/cart.php');
    require_once realpath(__DIR__ . '/includes/woo/checkout.php');
    require_once realpath(__DIR__ . '/includes/woo/emails.php');
    require_once realpath(__DIR__ . '/includes/woo/form-fields.php');
    require_once realpath(__DIR__ . '/includes/woo/menus.php');
    require_once realpath(__DIR__ . '/includes/woo/order.php');
    require_once realpath(__DIR__ . '/includes/woo/product.php');
    require_once realpath(__DIR__ . '/includes/woo/select-element.php');
    require_once realpath(__DIR__ . '/includes/woo/templates.php');
}

// endregion woocommerce

/**
 * Theme MultilingualPress includes
 */
// region multilingualpress
require_once realpath(__DIR__ . '/includes/mlp/helpers.php');
require_once realpath(__DIR__ . '/includes/mlp/language-selector.php');
if (is_multilingualpress_enabled()) {
    require_once realpath(__DIR__ . '/includes/mlp/polyfills.php');
    require_once realpath(__DIR__ . '/includes/mlp/metadata.php');
    require_once realpath(__DIR__ . '/includes/mlp/rest-api.php');
    require_once realpath(__DIR__ . '/includes/mlp/scheduled-posts.php');
}
// endregion multilingualpress

/**
 * Plugin The Events Calendar
 *
 * Remove post_tag, event categories, unused box
 */
add_action(
    'init',
    function () {
        unregister_taxonomy_for_object_type('post_tag', 'tribe_events');
        unregister_taxonomy_for_object_type('tribe_events_cat', 'tribe_events');
    }
);

add_action(
    'add_meta_boxes',
    function () {
        remove_meta_box('tribe_events_event_options', 'tribe_events', 'side');
        remove_meta_box('tec-events-qr-code', 'tribe_events', 'side');
        remove_meta_box('tribe-events-status', 'tribe_events', 'side');
    },
    20
);

/*
 * Added Longitude and Latitude for Venue in API results /wp-json/tribe/events/v1/venues
 */
add_filter(
    'tribe_rest_venue_data',
    function ($data) {
        $data['longitude'] = get_post_meta($data['id'], '_VenueLongitude', true);
        $data['latitude']  = get_post_meta($data['id'], '_VenueLatitude', true);
        return $data;
    },
    10,
    2
);

add_filter('big_image_size_threshold', '__return_false');

add_filter('block_editor_settings_all', function ($settings, $context) {
    if (current_user_can('edit_theme_options')) {
        return $settings;
    }

    $settings['supportsTemplateMode'] = false;
    return $settings;
}, 10, 2);

/**
 * Cloudflare Turnstile
 */
add_action('wp_enqueue_scripts', function () {
    if (!getenv('TURNSTILE_SITE_KEY')) {
        return;
    }

    wp_enqueue_script('cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], null, true);
});

add_filter('script_loader_tag', function ($tag, $handle) {
    if ('cf-turnstile' !== $handle) {
        return $tag;
    }

    return str_replace(' src', ' async defer src', $tag);
}, 10, 2);

function verify_turnstile(): bool
{
    $response = $_POST['cf-turnstile-response'] ?? '';
    if (empty($response)) {
        return false;
    }

    $result = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'body' => [
            'secret' => getenv('TURNSTILE_SECRET_KEY'),
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ],
    ]);

    if (is_wp_error($result)) {
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($result), true);
    return !empty($body['success']);
}

// phpcs:enable Squiz.Commenting.InlineComment.WrongStyle,PEAR.Commenting.InlineComment.WrongStyle
