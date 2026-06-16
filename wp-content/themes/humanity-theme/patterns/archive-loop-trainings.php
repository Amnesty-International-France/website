<?php
/**
 * Title: Archive loop for Trainings
 * Description: Template for the loop on archive trainings page
 * Slug: amnesty/archive-loop-trainings
 * Inserter: yes
 */

add_filter('get_the_terms', 'amnesty_limit_post_terms_results_for_archive');

global $wpdb;
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$posts_per_page = 18;
$offset = ($paged - 1) * $posts_per_page;

$filter = '';
$filter_values = [];
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
$meta_key_filter = '%session%date%de%debut';
$meta_value_filter = '%field%';

$total_query = "SELECT COUNT(1) AS count FROM ({$query}) AS combined_table";
$total_result = $wpdb->prepare($total_query, array_merge([$meta_key_filter, $meta_value_filter], $filter_values));
$total = $wpdb->get_results($total_result)[0]->count;
$max_num_page = ceil($total / $posts_per_page);
$wpdb->max_num_pages = $max_num_page;

$session_query = $wpdb->prepare(sprintf('%s LIMIT %d, %d', $query, $offset, $posts_per_page), array_merge([$meta_key_filter, $meta_value_filter], $filter_values));
$raw_sessions = $wpdb->get_results($session_query);
$sessions = [];
foreach ($raw_sessions as $raw_session) {
    $session = get_post($raw_session->post_id);
    if (!empty($raw_session->meta_value)) {
        $date_de_debut = DateTimeImmutable::createFromFormat('Ymd', $raw_session->meta_value);
        $session->session_start = $date_de_debut->format('d/m/Y');
        $session->session_end = get_field(str_replace('debut', 'fin', $raw_session->meta_key), $session);
    } else {
        $session->session_start = '';
        $session->session_end = null;
    }

    $sessions[] = $session;
}
?>

<div class="wp-block-query">
    <!-- wp:group {"tagName":"div","className":""} -->
    <div class="wp-block-group news-section section section--small section--tinted has-gutter">
        <div class="wp-block-group postlist">
			<?php if (!empty($sessions)) : ?>
			<div class="post-grid">
                <?php
                    foreach ($sessions as $session) {
                        $block = [
                            'blockName'    => 'amnesty-core/training-card',
                            'attrs'        => [
                                'direction' => 'portrait',
                                'postId' => $session->ID,
                                'session_start' => $session->session_start,
                                'session_end' => $session->session_end,
                            ],
                            'innerBlocks'  => [],
                            'innerHTML'    => '',
                            'innerContent' => [],
                        ];
                        echo render_block($block);
                    }
			    ?>
			</div>

			<?php else : ?>
			<div class="wp-block-query-no-results">
				<p>Nous n'avons pas trouvé de formations qui correspondent à vos critères de recherche.</p>
			</div>
			<?php endif; ?>
		</div>
		<?php
			            $big = 999999999;
$pagination = paginate_links([
    'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
    'format'    => '?paged=%#%',
    'current'   => max(1, get_query_var('paged')),
    'total'     => $max_num_page,
    'prev_text' => esc_html__('Previous', 'amnesty'),
    'next_text' => esc_html__('Next', 'amnesty'),
    'type'      => 'list',
]);

if ($pagination) {
    echo '<div class="wp-block-query-pagination section section--small page-numbers">';
    echo $pagination;
    echo '</div>';
}

wp_reset_postdata();
?>
	</div>
	<!-- /wp:group -->
</div>
