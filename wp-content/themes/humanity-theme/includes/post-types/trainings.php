<?php

/**
 * Register Custom Post-Type: Training
 */
function amnesty_register_trainings_cpt(): void
{
    register_post_type(
        'training',
        [
            'labels'              => [
                'name'               => 'Formations',
                'singular_name'      => 'Formation',
                'add_new'            => 'Ajouter une Formation',
                'add_new_item'       => 'Ajouter une nouvelle Formation',
                'edit_item'          => 'Modifier la Formation',
                'new_item'           => 'Nouvelle Formation',
                'view_item'          => 'Voir la Formation',
                'search_items'       => 'Rechercher une Formation',
                'not_found'          => 'Aucune Formation trouvée',
                'not_found_in_trash' => 'Aucune Formation dans la corbeille',
            ],
            'public'              => true,
            'has_archive'         => true,
            'rewrite'             => [ 'slug' => 'formations' ],
            'supports'            => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
            'menu_icon'           => 'dashicons-welcome-learn-more',
            'show_in_rest'        => true,
            'publicly_queryable'  => true,
            'exclude_from_search' => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 20,
        ]
    );
}

add_action('init', 'amnesty_register_trainings_cpt');

/**
 * Filters the main query on the 'training' CPT archive page based on ACF fields.
 */
function my_project_training_filters($query)
{
    if (! $query->is_main_query() || ! is_post_type_archive('training')) {
        return;
    }

    $meta_query       = [ 'relation' => 'AND' ];
    $conditions_added = false;

    if (isset($_GET['qcategories']) && ! empty($_GET['qcategories'])) {
        $categories       = explode(',', sanitize_text_field($_GET['qcategories']));
        $meta_query[]     = [
            'key'     => 'categories',
            'value'   => $categories,
            'compare' => 'IN',
        ];
        $conditions_added = true;
    }

    if (isset($_GET['qlieu']) && ! empty($_GET['qlieu'])) {
        $locations        = explode(',', sanitize_text_field($_GET['qlieu']));
        $meta_query[]     = [
            'key'     => 'lieu',
            'value'   => $locations,
            'compare' => 'IN',
        ];
        $conditions_added = true;
    }

    if (isset($_GET['qperiod']) && ! empty($_GET['qperiod'])) {
        $periods       = explode(',', sanitize_text_field($_GET['qperiod']));
        $date_or_query = [ 'relation' => 'OR' ];
        foreach ($periods as $period) {
            if (preg_match('/^\d{4}-\d{2}$/', $period)) {
                $date_or_query[] = [
                    'key'     => 'date',
                    'value'   => $period,
                    'compare' => 'LIKE',
                ];
            }
        }
        if (count($date_or_query) > 1) {
            $meta_query[]     = $date_or_query;
            $conditions_added = true;
        }
    }

    if ($conditions_added) {
        $query->set('meta_query', $meta_query);
    }
}
if (!is_admin()) {
    add_action('pre_get_posts', 'my_project_training_filters');
}

add_action(
    'template_redirect',
    function () {
        if (is_singular('training')) {
            global $post;

            $isPrivate = get_field('members_only', $post->ID);

            if ($isPrivate) {
                if (! is_user_logged_in()) {
                    wp_redirect(home_url('/mon-espace/'));
                    exit;
                }

                $user = wp_get_current_user();

                if (! in_array('administrator', (array) $user->roles) && ! in_array('subscriber', (array) $user->roles)) {
                    wp_die('⚠️ Vous n’avez pas les permissions nécessaires pour accéder à cette page.');
                }
            }
        }
    }
);

/**
 * Ajoute les règles de réécriture et le "flag" pour les formations "Mon Espace"
 */
function aif_myspace_training_rewrite_rules()
{
    add_filter('query_vars', function ($vars) {
        $vars[] = 'is_my_space_training';
        return $vars;
    });

    add_rewrite_rule(
        '^mon-espace/boite-a-outils/militants-se-former/([^/]+)/?$',
        'index.php?post_type=training&name=$matches[1]&is_my_space_training=1',
        'top'
    );
}
add_action('init', 'aif_myspace_training_rewrite_rules');

function aif_myspace_training_template_include($template)
{
    if (get_query_var('is_my_space_training') && is_singular('training')) {
        $new_template = get_stylesheet_directory() . '/patterns/single-training-my-space.php';
        if ('' !== $new_template) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'aif_myspace_training_template_include', 99);

function aif_formation_archive_query($query)
{
    if (!is_admin() && $query->is_main_query() && $query->is_post_type_archive('training')) {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $query->set('paged', $paged);
        $query->set('posts_per_page', AIF_TRAININGS_PER_PAGE);
    }
}
add_action('pre_get_posts', 'aif_formation_archive_query');

/**
 * Number of trainings displayed per archive page.
 *
 * Must stay in sync with the archive-loop-trainings pattern, which renders the
 * sessions with its own custom query.
 */
const AIF_TRAININGS_PER_PAGE = 18;

/**
 * Build the SQL query (and its prepared arguments) listing the training sessions
 * matching the current request filters (qperiod / qlieu / qcategories).
 *
 * Shared between the archive loop pattern (to fetch a page of sessions) and the
 * pagination fix below (to count the total), so both always agree.
 *
 * @return array{0: string, 1: array<int, string>} The SQL query and its prepared arguments.
 */
function aif_get_trainings_session_query(): array
{
    global $wpdb;

    $get_training_filter = static function ($key) {
        if (!isset($_GET[$key])) {
            return null;
        }

        $value = wp_unslash($_GET[$key]);
        if (!is_scalar($value)) {
            return null;
        }

        return sanitize_text_field((string) $value);
    };

    $filter        = '';
    $filter_values = [];

    $periode_filter = $get_training_filter('qperiod');
    if ($periode_filter) {
        $periodes = array_filter(array_map(static function ($periode) {
            return str_replace('-', '', trim($periode));
        }, explode(',', $periode_filter)));

        if (\count($periodes) > 1) {
            $filter .= ' AND (' . implode(' OR ', array_fill(0, \count($periodes), 'm.meta_value LIKE %s')) . ')';
            foreach ($periodes as $periode) {
                $filter_values[] = $wpdb->esc_like($periode) . '%';
            }
        } elseif (\count($periodes) === 1) {
            $filter .= ' AND m.meta_value LIKE %s';
            $filter_values[] = $wpdb->esc_like(reset($periodes)) . '%';
        }
    }

    $lieu_filter = $get_training_filter('qlieu');
    if ($lieu_filter) {
        $lieux = array_filter(array_map('trim', explode(',', $lieu_filter)));
        if (\count($lieux) > 1) {
            $filter .= ' AND m2.meta_value IN (' . implode(', ', array_fill(0, \count($lieux), '%s')) . ')';
            $filter_values = array_merge($filter_values, $lieux);
        } elseif (\count($lieux) === 1) {
            $filter .= ' AND m2.meta_value = %s';
            $filter_values[] = reset($lieux);
        }
    }

    $categories_filter = $get_training_filter('qcategories');
    if ($categories_filter) {
        $categories = array_filter(array_map('trim', explode(',', $categories_filter)));
        if (\count($categories) > 1) {
            $filter .= ' AND m3.meta_value IN (' . implode(', ', array_fill(0, \count($categories), '%s')) . ')';
            $filter_values = array_merge($filter_values, $categories);
        } elseif (\count($categories) === 1) {
            $filter .= ' AND m3.meta_value = %s';
            $filter_values[] = reset($categories);
        }
    }

    $query = "SELECT p.ID as post_id, m.meta_key, m.meta_value, m2.meta_value AS lieu, m3.meta_value AS categorie
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} m2 ON p.ID = m2.post_id AND m2.meta_key = 'lieu'
        JOIN {$wpdb->postmeta} m3 ON p.ID = m3.post_id AND m3.meta_key = 'categories'
        LEFT JOIN {$wpdb->postmeta} m ON p.ID = m.post_id AND m.meta_key LIKE %s AND m.meta_value != '' AND m.meta_value NOT LIKE %s
        WHERE p.post_type = 'training'
        AND p.post_status = 'publish'
        {$filter}
        ORDER BY CAST(m.meta_value AS DATE) ASC";

    $meta_key_filter   = '%session%date%de%debut';
    $meta_value_filter = '%field%';

    return [$query, array_merge([$meta_key_filter, $meta_value_filter], $filter_values)];
}

/**
 * Compute the number of pages for the trainings archive, based on the same query
 * used to render the sessions.
 */
function aif_get_trainings_max_num_pages(int $posts_per_page = AIF_TRAININGS_PER_PAGE): int
{
    global $wpdb;

    [$query, $query_args] = aif_get_trainings_session_query();

    $total_query = "SELECT COUNT(1) AS count FROM ({$query}) AS combined_table";
    $total       = (int) $wpdb->get_results($wpdb->prepare($total_query, $query_args))[0]->count;

    if ($posts_per_page < 1) {
        return 0;
    }

    return (int) ceil($total / $posts_per_page);
}

/**
 * Align the main query pagination with the trainings actually displayed.
 *
 * The archive is rendered by a custom query (see the archive-loop-trainings
 * pattern), so WordPress' main query — which feeds Yoast's "Page X of Y" title —
 * would otherwise report the wrong number of pages.
 */
function aif_formation_archive_fix_pagination(): void
{
    if (is_admin() || !is_post_type_archive('training')) {
        return;
    }

    global $wp_query;
    $wp_query->max_num_pages = aif_get_trainings_max_num_pages();
}
add_action('wp', 'aif_formation_archive_fix_pagination');
