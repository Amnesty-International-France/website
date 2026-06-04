<?php

function amnesty_register_petitions_cpt()
{
    $labels = [
        'name' => 'Pétitions',
        'singular_name' => 'Pétition',
        'add_new' => 'Ajouter une Pétition',
        'add_new_item' => 'Ajouter une nouvelle Pétition',
        'edit_item' => 'Modifier la Pétition',
        'new_item' => 'Nouvelle Pétition',
        'view_item' => 'Voir la Pétition',
        'search_items' => 'Rechercher une Pétition',
        'not_found' => 'Aucune Pétition trouvée',
        'not_found_in_trash' => 'Aucune Pétition dans la corbeille',
    ];
    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'petitions'],
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'menu_icon' => 'dashicons-pressthis',
        'show_in_rest' => true,
    ];

    register_post_type('petition', $args);
}
add_action('init', 'amnesty_register_petitions_cpt');

function amnesty_signature_count_permission_check($allowed, $meta_key, $post_id, $user_id, $cap, $caps)
{
    return user_can($user_id, 'edit_post', $post_id);
}

function amnesty_register_petition_signature_count_meta()
{
    register_post_meta('petition', '_amnesty_signature_count', [
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'integer',
        'default'       => 0,
        'sanitize_callback' => 'absint',
        'auth_callback'     => 'amnesty_signature_count_permission_check',
    ]);
}
add_action('init', 'amnesty_register_petition_signature_count_meta');

function amnesty_register_petition_type_meta()
{
    register_post_meta('petition', 'type', [
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => 'amnesty_signature_count_permission_check',
    ]);
}
add_action('init', 'amnesty_register_petition_type_meta');

function amnesty_get_petition_signature_count($post_id): int
{
    $count = get_post_meta($post_id, '_amnesty_signature_count', true);
    return absint($count);
}

if (!function_exists('amnesty_get_clh_petition_tunnel_page')) {
    function amnesty_get_clh_petition_tunnel_page(): ?WP_Post
    {
        // get_page_by_path() issues a DB query every call and is not object-cached by WordPress.
        // This function is called from amnesty_get_clh_petition_tunnel_url(), which is itself called
        // on every petition page load (signature redirect) and on every skip/sign handler.
        // A static variable with a false sentinel (false = not yet evaluated, null = evaluated & not found)
        // ensures the two DB queries run at most once per request regardless of how many call sites invoke it.
        static $cache = false;

        if ($cache !== false) {
            return $cache;
        }

        $clh_page = get_page_by_path('nous-connaitre/nos-combats/changez-leur-histoire/merci-tunnel-clh');

        if ($clh_page instanceof WP_Post) {
            return $cache = $clh_page;
        }

        return $cache = null;
    }
}

if (!function_exists('amnesty_get_clh_petition_tunnel_url')) {
    function amnesty_get_clh_petition_tunnel_url(): string
    {
        $clh_page = amnesty_get_clh_petition_tunnel_page();

        if ($clh_page) {
            $clh_permalink = get_permalink($clh_page);

            return $clh_permalink ?: home_url('/nous-connaitre/nos-combats/changez-leur-histoire/merci-tunnel-clh/');
        }

        return home_url('/nous-connaitre/nos-combats/changez-leur-histoire/merci-tunnel-clh/');
    }
}

if (!function_exists('amnesty_get_clh_petition_campaign_page')) {
    function amnesty_get_clh_petition_campaign_page(): ?WP_Post
    {
        // Same caching rationale as amnesty_get_clh_petition_tunnel_page(): get_page_by_path() is an
        // uncached DB query. This function is now called unconditionally on every petition page render
        // (via amnesty_is_clh_petition_tunnel_active() in aside-petition-sticky.php), so without a cache
        // it issues up to 2 DB queries per request regardless of whether a CLH campaign is active.
        // false = not yet evaluated; null = evaluated, page not found in DB.
        static $cache = false;

        if ($cache !== false) {
            return $cache;
        }

        $clh_page = get_page_by_path('nous-connaitre/nos-combats/changez-leur-histoire');

        if ($clh_page instanceof WP_Post) {
            return $cache = $clh_page;
        }

        $clh_page = get_page_by_path('changez-leur-histoire');

        if ($clh_page instanceof WP_Post) {
            return $cache = $clh_page;
        }

        return $cache = null;
    }
}

if (!function_exists('amnesty_get_active_clh_campaign_for_page')) {
    function amnesty_get_active_clh_campaign_for_page(int $page_id): ?WP_Post
    {
        // This function makes up to 4 get_field() calls (highlight_clh, campaign_clh,
        // start_date_highligth_clh, end_date_highlight_clh). ACF caches field values per post ID
        // within a request, but the function itself is invoked from multiple call sites on the same
        // page (aside-petition-sticky, slider block render, petition-card partial, tunnel content pattern).
        // A per-page-id static cache avoids redundant ACF lookups and keeps the hot path cheap.
        // false = not yet evaluated for this page_id; null = evaluated, no active campaign found.
        static $cache = [];

        if (array_key_exists($page_id, $cache)) {
            return $cache[$page_id];
        }

        if (!get_field('highlight_clh', $page_id)) {
            return $cache[$page_id] = null;
        }

        $campaign = get_field('campaign_clh', $page_id);

        if (!$campaign instanceof WP_Post) {
            return $cache[$page_id] = null;
        }

        $timestamp_now   = time();
        $start_date      = get_field('start_date_highligth_clh', $campaign->ID);
        $end_date        = get_field('end_date_highlight_clh', $campaign->ID);
        $timestamp_start = $start_date ? strtotime((string) $start_date) : 0;
        $timestamp_end   = $end_date ? strtotime((string) $end_date) : 0;

        if ($timestamp_start > $timestamp_now) {
            return $cache[$page_id] = null;
        }

        if ($timestamp_end <= $timestamp_now) {
            return $cache[$page_id] = null;
        }

        return $cache[$page_id] = $campaign;
    }
}

if (!function_exists('amnesty_is_clh_petition_tunnel_active')) {
    function amnesty_is_clh_petition_tunnel_active(): bool
    {
        $clh_page = amnesty_get_clh_petition_campaign_page();

        return $clh_page !== null && amnesty_get_active_clh_campaign_for_page($clh_page->ID) !== null;
    }
}

if (!function_exists('amnesty_get_petition_signature_redirect_url')) {
    function amnesty_get_petition_signature_redirect_url(int $petition_id, array $query_args = [], bool $from_tunnel = false): string
    {
        $redirect_url = $from_tunnel && get_field('clh_petition', $petition_id) && amnesty_is_clh_petition_tunnel_active()
            ? amnesty_get_clh_petition_tunnel_url()
            : trailingslashit(get_permalink($petition_id)) . 'merci/';

        if (! empty($query_args)) {
            $redirect_url = add_query_arg($query_args, $redirect_url);
        }

        return $redirect_url;
    }
}

if (!function_exists('amnesty_is_clh_tunnel_form_submission')) {
    function amnesty_is_clh_tunnel_form_submission(): bool
    {
        return !empty($_POST['from_tunnel']) || !empty($_POST['form_tunnel']);
    }
}

if (!function_exists('amnesty_get_petition_form_fallback_url')) {
    function amnesty_get_petition_form_fallback_url(int $petition_id = 0): string
    {
        $referer = wp_get_referer();

        if ($referer) {
            return $referer;
        }

        if ($petition_id) {
            $permalink = get_permalink($petition_id);

            if ($permalink) {
                return $permalink;
            }
        }

        return home_url('/');
    }
}

/**
 * Ajoute les règles de réécriture et le "flag" pour les pétitions "Mon Espace"
 */
function aif_myspace_petition_rewrite_rules(): void
{
    add_filter('query_vars', function ($vars) {
        $vars[] = 'is_my_space_petition';
        return $vars;
    });

    add_rewrite_rule(
        '^mon-espace/agir-et-se-mobiliser/nos-petitions/([^/]+)/?$',
        'index.php?post_type=petition&name=$matches[1]&is_my_space_petition=1',
        'top'
    );
}
add_action('init', 'aif_myspace_petition_rewrite_rules');

function aif_myspace_petition_template_include($template)
{
    if (get_query_var('is_my_space_petition') && is_singular('petition')) {
        $new_template = get_stylesheet_directory() . '/patterns/single-petition-my-space.php';
        if ('' !== $new_template) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'aif_myspace_petition_template_include', 99);

function amnesty_start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    if (headers_sent()) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_ssl(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();
}

function amnesty_set_clh_signer_email(string $email): void
{
    amnesty_start_secure_session();

    if (empty($_SESSION['clh_last_signer_email'])) {
        $data = $_SESSION;
        session_regenerate_id(true);
        $_SESSION = $data;
    }

    $_SESSION['clh_last_signer_email'] = $email;
}

function amnesty_get_clh_skipped_petitions(): array
{
    return $_SESSION['clh_skipped_petitions'] ?? [];
}

function amnesty_set_clh_skipped_petition(int $petition_id): void
{
    amnesty_start_secure_session();
    $skipped = amnesty_get_clh_skipped_petitions();
    $skipped[] = $petition_id;
    $_SESSION['clh_skipped_petitions'] = array_unique($skipped);
}

function amnesty_get_clh_tunnel_end_url(): string
{
    // Sera mis à jour dans l'issue "page fin de parcours CLH"
    $campaign_page = amnesty_get_clh_petition_campaign_page();
    return $campaign_page ? (string) get_permalink($campaign_page) : home_url('/');
}

function amnesty_is_clh_petition_tunnel_page(?WP_Post $post = null): bool
{
    if (!is_page()) {
        return false;
    }

    $post = $post ?: get_post();

    if (!$post instanceof WP_Post) {
        return false;
    }

    $template_slug = get_page_template_slug($post->ID);
    $tunnel_templates = [
        'page-merci-tunnel-clh.html',
    ];

    if (in_array($template_slug, $tunnel_templates, true)) {
        return true;
    }

    $tunnel_page = amnesty_get_clh_petition_tunnel_page();

    return $tunnel_page instanceof WP_Post && (int) $tunnel_page->ID === (int) $post->ID;
}

function amnesty_get_clh_tunnel_context(?WP_Post $post = null): array
{
    static $contexts = [];

    $post = $post ?: get_post();

    if (!$post instanceof WP_Post) {
        return [];
    }

    if (isset($contexts[$post->ID])) {
        return $contexts[$post->ID];
    }

    amnesty_start_secure_session();

    $campaign_page_id = (int) $post->post_parent;

    if (!$campaign_page_id) {
        $campaign_page = amnesty_get_clh_petition_campaign_page();
        $campaign_page_id = $campaign_page instanceof WP_Post ? (int) $campaign_page->ID : 0;
    }

    $active_campaign = $campaign_page_id ? amnesty_get_active_clh_campaign_for_page($campaign_page_id) : null;
    $raw_email = $_SESSION['clh_last_signer_email'] ?? $_COOKIE['clh_user_email'] ?? null;
    $last_signer_email = ($raw_email && is_email($raw_email)) ? sanitize_email($raw_email) : null;
    $current_user = $last_signer_email ? get_local_user($last_signer_email) : false;
    $list_petitions_clh = $active_campaign ? (get_field('list_petition_clh', $active_campaign->ID) ?: []) : [];
    $selected_posts = [];
    $skipped_petitions = amnesty_get_clh_skipped_petitions();

    foreach ($list_petitions_clh as $petition) {
        $goal = get_field('objectif_signatures', $petition->ID) ?: 200000;
        $current = amnesty_get_petition_signature_count($petition->ID) ?: 0;

        $selected_posts[] = [
            'id' => $petition->ID,
            'title' => $petition->post_title,
            'description' => get_field('short_description', $petition->ID) ?: get_the_excerpt($petition->ID),
            'image_id' => get_post_thumbnail_id($petition->ID),
            'letter' => get_field('lettre', $petition->ID),
            'goal' => $goal,
            'current' => $current,
            'percentage' => ($goal > 0) ? min(($current / $goal) * 100, 100) : 0,
            'already_signed' => $last_signer_email && $current_user && have_signed($petition->ID, $current_user->id),
            'active' => amnesty_is_petition_not_expired($petition->ID),
            'already_skipped' => in_array($petition->ID, $skipped_petitions, true),
        ];
    }

    $signed_count = count(array_filter($selected_posts, fn ($petition) => $petition['already_signed'] === true));
    $not_signed = array_filter(
        $selected_posts,
        static fn ($petition) =>
        $petition['already_signed'] === false &&
        $petition['already_skipped'] === false &&
        $petition['active'] === true
    );

    $next_petition = null;

    if ($signed_count < 10 && !empty($not_signed)) {
        $random_key = array_rand($not_signed);
        $next_petition = $not_signed[$random_key];
    }

    $contexts[$post->ID] = [
        'active_campaign' => $active_campaign,
        'last_signer_email' => $last_signer_email,
        'list_petitions_clh' => $list_petitions_clh,
        'selected_posts' => $selected_posts,
        'signed_count' => $signed_count,
        'not_signed' => $not_signed,
        'next_petition' => $next_petition,
    ];

    return $contexts[$post->ID];
}

function amnesty_redirect_clh_petition_tunnel_page(): void
{
    if (!amnesty_is_clh_petition_tunnel_page()) {
        return;
    }

    $context = amnesty_get_clh_tunnel_context();

    if (empty($context['active_campaign'])) {
        wp_redirect('/');
        exit;
    }

    if (empty($context['list_petitions_clh']) || empty($context['next_petition'])) {
        wp_redirect(amnesty_get_clh_tunnel_end_url());
        exit;
    }
}
add_action('template_redirect', 'amnesty_redirect_clh_petition_tunnel_page');

function amnesty_handle_petition_skip(): void
{
    if (!isset($_POST['skip_petition'], $_POST['petition_id'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['clh_skip_nonce'] ?? '', 'clh_skip_petition')) {
        wp_die('Action non autorisée.', '', ['response' => 403]);
    }

    $petition_id = absint($_POST['petition_id']);
    if (!$petition_id) {
        wp_redirect(amnesty_get_clh_petition_tunnel_url());
        exit;
    }

    amnesty_set_clh_skipped_petition($petition_id);

    if (isset($_POST['user_email'])) {
        $email = sanitize_email($_POST['user_email']);
        if (is_email($email)) {
            amnesty_set_clh_signer_email($email);
        }
    }

    wp_redirect(amnesty_get_clh_petition_tunnel_url());
    exit;
}
add_action('template_redirect', 'amnesty_handle_petition_skip');

function amnesty_is_petition_not_expired($petition_id): bool
{
    $end_date = get_field('date_de_fin', $petition_id);
    $end_date_timestamp = !empty($end_date) ? strtotime($end_date) : false;

    return $end_date_timestamp === false || $end_date_timestamp > time();
}

function amnesty_handle_petition_signature(): void
{
    if (!isset($_POST['sign_petition'], $_POST['petition_id'])) {
        return;
    }

    $petition_id = absint($_POST['petition_id']);
    $is_clh_petition = amnesty_is_clh_petition_tunnel_active()
        && get_field('clh_petition', $petition_id);
    $is_clh_tunnel_submission = $is_clh_petition
        && (amnesty_is_clh_tunnel_form_submission() || amnesty_is_clh_petition_tunnel_page());

    $raw_email = $_POST['user_email'] ?? null;
    if ((!$raw_email && !$is_clh_tunnel_submission) || !$petition_id) {
        wp_redirect(add_query_arg('signature_status', 'invalid', amnesty_get_petition_form_fallback_url($petition_id)));
        exit;
    }

    $turnstile_error = verify_turnstile();
    if ($turnstile_error !== null) {
        if ($is_clh_tunnel_submission) {
            wp_redirect(add_query_arg('signature_status', 'turnstile', amnesty_get_clh_petition_tunnel_url()));
            exit;
        }

        $GLOBALS['petition_turnstile_error_message'] = turnstile_friendly_error($turnstile_error);
        return;
    }

    if (!$raw_email && $is_clh_tunnel_submission) {
        amnesty_start_secure_session();
        $raw_email = $_SESSION['clh_last_signer_email'] ?? null;
    }

    if (!$raw_email || !is_email($raw_email)) {
        wp_redirect(add_query_arg('signature_status', 'invalid', amnesty_get_petition_form_fallback_url($petition_id)));
        exit;
    }

    $user_email = sanitize_email($raw_email);

    $type = get_field('type', $petition_id)['value'] ?? 'petition';

    if (!amnesty_is_petition_not_expired($petition_id)) {
        wp_redirect(add_query_arg('signature_status', 'expired', amnesty_get_petition_form_fallback_url($petition_id)));
        exit;
    }

    $local_user = get_local_user($user_email);

    if ($local_user !== false) {
        $user_id = $local_user->id;
        if (have_signed($petition_id, $user_id)) {
            if ($is_clh_petition) {
                amnesty_set_clh_signer_email($user_email);
            }

            $redirect_url = amnesty_get_petition_signature_redirect_url(
                $petition_id,
                [
                    'alreadysigned' => '',
                ],
                $is_clh_tunnel_submission
            );
            wp_redirect($redirect_url);
            exit;
        }
    } else {
        $civility    = $_POST['civility']       ?? '';
        $firstname   = $_POST['user_firstname'] ?? '';
        $lastname    = $_POST['user_lastname']  ?? '';
        $postal_code = $_POST['user_zipcode']   ?? '';
        $country     = $_POST['user_country']   ?? '';
        $phone       = $_POST['user_phone']     ?? '';

        $user_id = insert_user($civility, $firstname, $lastname, $user_email, $country, $postal_code, $phone);

        if ($user_id === false) {
            wp_redirect(add_query_arg('signature_status', 'error', amnesty_get_petition_form_fallback_url($petition_id)));
            exit;
        }
    }

    $code_origine = isset($_POST['code_origine']) && !empty($_POST['code_origine']) ? $_POST['code_origine'] : get_field('code_origine', $petition_id) ?? '';
    $message = $type === 'action-soutien' && isset($_POST['user_message']) && !empty($_POST['user_message']) ? sanitize_textarea_field($_POST['user_message']) : '';

    if (insert_petition_signature($petition_id, $user_id, date('Y-m-d'), $code_origine, $message) === false) {
        wp_redirect(add_query_arg('signature_status', 'error', amnesty_get_petition_form_fallback_url($petition_id)));
        exit;
    }

    $current_signatures = amnesty_get_petition_signature_count($petition_id);
    $new_signatures = $current_signatures + 1;
    update_post_meta($petition_id, '_amnesty_signature_count', $new_signatures);

    $gtm_type = 'petition';
    $gtm_name = get_the_title($petition_id);

    if ($is_clh_petition) {
        amnesty_set_clh_signer_email($user_email);

        $cookie_signed = [];
        if (!empty($_COOKIE['clh_signed_petitions'])) {
            $decoded = json_decode(stripslashes($_COOKIE['clh_signed_petitions']), true);
            if (is_array($decoded)) {
                $cookie_signed = array_map('intval', $decoded);
            }
        }
        $cookie_signed[] = $petition_id;
        setcookie('clh_signed_petitions', wp_json_encode(array_unique($cookie_signed)), [
            'expires'  => time() + 30 * DAY_IN_SECONDS,
            'path'     => '/',
            'secure'   => is_ssl(),
            'samesite' => 'Strict',
        ]);

        setcookie('clh_user_email', $user_email, [
            'expires'  => time() + 30 * DAY_IN_SECONDS,
            'path'     => '/',
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }

    $redirect_url = amnesty_get_petition_signature_redirect_url(
        $petition_id,
        [
            'gtm_type' => $gtm_type,
            'gtm_name' => urlencode($gtm_name),
        ],
        $is_clh_tunnel_submission
    );

    wp_redirect($redirect_url);
    exit;
}
add_action('template_redirect', 'amnesty_handle_petition_signature');

function filter_petition_archive(WP_Query $query)
{
    if (! $query->is_main_query() || ! is_post_type_archive('petition')) {
        return;
    }

    $meta_query_args = [
        [
            'key' => 'date_de_fin',
            'value' => date('Y-m-d'),
            'compare' => '>=',
            'type' => 'DATE',
        ],
    ];
    $query->set('meta_query', $meta_query_args);


    $query->set('meta_key', 'date_de_fin');
    $query->set('meta_type', 'DATE');
    $query->set('orderby', 'meta_value');
    $query->set('order', 'ASC');
}

if (!is_admin()) {
    add_action('pre_get_posts', 'filter_petition_archive');
}

add_filter('manage_petition_posts_columns', function ($columns) {
    $columns['type_petition'] = 'Type de pétition';
    return $columns;
});

add_action('manage_petition_posts_custom_column', function ($column, $post_id) {
    if ($column === 'type_petition') {
        echo get_field('field_685aca87362cb', $post_id)['label'];
    }
}, 10, 2);

add_action('restrict_manage_posts', function ($post_type) {
    if ($post_type !== 'petition') {
        return;
    }
    $selected = $_GET['type_petition'] ?? '';
    ?>
	<select name="type_petition">
		<option value="">Toutes les pétitions</option>
		<option value="petition" <?php selected($selected, 'petition'); ?>>Pétition</option>
		<option value="action-soutien" <?php selected($selected, 'action-soutie'); ?>>Action de soutien</option>
	</select>
	<?php
});

if (is_admin()) {
    add_action('pre_get_posts', function ($query) {
        global $pagenow;

        if (
            $pagenow !== 'edit.php' ||
            !$query->is_main_query() ||
            $query->get('post_type') !== 'petition'
        ) {
            return;
        }

        $petitionTypeFilter = $_GET['type_petition'] ?? '';
        if ($petitionTypeFilter !== '') {
            $query->set('meta_query', [
                [
                    'key'   => 'type',
                    'value' => $petitionTypeFilter,
                ],
            ]);
        }
    });
}

add_action('acf/include_fields', function () {
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_685aca878b4d7',
        'title' => 'Attributs Pétition',
        'fields' => [
            [
                'key' => 'field_685aca87362cb',
                'label' => 'Type',
                'name' => 'type',
                'aria-label' => '',
                'type' => 'select',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'choices' => [
                    'petition' => 'Pétition',
                    'action-soutien' => 'Action de soutien',
                ],
                'default_value' => 'petition',
                'return_format' => 'array',
                'multiple' => 0,
                'allow_null' => 0,
                'allow_in_bindings' => 0,
                'ui' => 0,
                'ajax' => 0,
                'placeholder' => '',
                'create_options' => 0,
                'save_options' => 0,
            ],
            [
                'key' => 'field_6a154b47c81a1',
                'label' => 'CLH',
                'name' => 'clh_petition',
                'aria-label' => '',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'petition',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 0,
                'allow_in_bindings' => 0,
                'ui' => 1,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ],
            [
                'key' => 'field_6a155b0a87p9c',
                'label' => 'Titre court',
                'name' => 'short_title',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'petition',
                        ],
                        [
                            'field' => 'field_6a154b47c81a1',
                            'operator' => '==',
                            'value' => 1,
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'rows' => '',
                'placeholder' => '',
                'new_lines' => '',
            ],
            [
                'key' => 'field_6a155b0a8709c',
                'label' => 'Description courte',
                'name' => 'short_description',
                'aria-label' => '',
                'type' => 'textarea',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'petition',
                        ],
                        [
                            'field' => 'field_6a154b47c81a1',
                            'operator' => '==',
                            'value' => 1,
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'rows' => '',
                'placeholder' => '',
                'new_lines' => '',
            ],
            [
                'key' => 'field_685acdfe73c83',
                'label' => 'ID SF',
                'name' => 'uidsf',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_685acdfe73c84',
                'label' => 'Code origine',
                'name' => 'code_origine',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_685ace6573c85',
                'label' => 'Date de fin',
                'name' => 'date_de_fin',
                'aria-label' => '',
                'type' => 'date_picker',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'display_format' => 'd/m/Y',
                'return_format' => 'd.m.Y',
                'first_day' => 1,
                'allow_in_bindings' => 0,
            ],
            [
                'key' => 'field_685acd6d73c81',
                'label' => 'Objectif signatures',
                'name' => 'objectif_signatures',
                'aria-label' => '',
                'type' => 'number',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'min' => 1,
                'max' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'step' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_685acdfe73c82',
                'label' => 'Destinataire',
                'name' => 'destinataire',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'petition',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_685ace1673c83',
                'label' => 'PDF pétition',
                'name' => 'pdf_petition',
                'aria-label' => '',
                'type' => 'file',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'petition',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'return_format' => 'id',
                'library' => 'all',
                'min_size' => '',
                'max_size' => '',
                'mime_types' => 'pdf',
                'allow_in_bindings' => 0,
            ],
            [
                'key' => 'field_685ace4c73c84',
                'label' => 'Punchline',
                'name' => 'punchline',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'petition',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_68f4b1a4a9c10',
                'label' => 'Sous-titre CLH',
                'name' => 'subtitle_clh',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_685acdfe73c86',
                'label' => 'Lettre',
                'name' => 'lettre',
                'aria-label' => '',
                'type' => 'wysiwyg',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'petition',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'allow_in_bindings' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 0,
                'delay' => 0,
            ],
            [
                'key' => 'field_685acf1b73c86',
                'label' => 'Autoriser message utilisateur',
                'name' => 'autoriser_message_utilisateur',
                'aria-label' => '',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'action-soutien',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 1,
                'allow_in_bindings' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ],
            [
                'key' => 'field_6867cd3430784',
                'label' => 'Téléphone requis',
                'name' => 'phone_required',
                'aria-label' => '',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'action-soutien',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 0,
                'allow_in_bindings' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ],
            [
                'key' => 'field_6867ccdb30782',
                'label' => 'Phrase Formulaire',
                'name' => 'form_contenu',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'action-soutien',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_6867cd7130785',
                'label' => 'Texte du bouton',
                'name' => 'button_text',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'action-soutien',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_6867cd2430783',
                'label' => 'Longueur max commentaire',
                'name' => 'comment_max_length',
                'aria-label' => '',
                'type' => 'number',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'action-soutien',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => 1000,
                'min' => 0,
                'max' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'step' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_6867cd7c30786',
                'label' => 'Terms',
                'name' => 'terms',
                'aria-label' => '',
                'type' => 'wysiwyg',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'action-soutien',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'allow_in_bindings' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 0,
                'delay' => 0,
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'petition',
                ],
            ],
        ],
        'menu_order' => 0,
        'position' => 'acf_after_title',
        'style' => 'default',
        'label_placement' => 'left',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 1,
    ]);
});
