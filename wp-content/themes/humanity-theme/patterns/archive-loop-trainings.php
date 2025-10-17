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
$periode_filter = isset($_GET['qperiod']) ? $_GET['qperiod'] : null;
if ($periode_filter) {
    $periodes = explode(',', $periode_filter);
    if (\count($periodes) > 1) {
        $filter .= 'AND (';
        foreach ($periodes as $periode) {
            $periode = str_replace('-', '', $periode);
            $filter .= "m.meta_value LIKE '{$periode}%' OR ";
        }
        $filter = substr($filter, 0, -4) . ')';
    } else {
        $periode_filter = str_replace('-', '', $periode_filter);
        $filter .= " AND m.meta_value LIKE '{$periode_filter}%'";
    }
}

$lieu_filter = isset($_GET['qlieu']) ? $_GET['qlieu'] : null;
if ($lieu_filter) {
    $lieux = explode(',', $lieu_filter);
    if (\count($lieux) > 1) {
        $filter .= ' AND m2.meta_value IN (';
        foreach ($lieux as $lieu) {
            $filter .= "'{$lieu}',";
        }
        $filter = substr($filter, 0, -1) . ')';
    } else {
        $filter .= " AND m2.meta_value = '{$lieu_filter}'";
    }
}

$categories_filter = isset($_GET['qcategories']) ? $_GET['qcategories'] : null;
if ($categories_filter) {
    $categories = explode(',', $categories_filter);
    if (\count($categories) > 1) {
        $filter .= ' AND m3.meta_value IN (';
        foreach ($categories as $category) {
            $filter .= "'{$category}',";
        }
        $filter = substr($filter, 0, -1) . ')';
    } else {
        $filter .= " AND m3.meta_value = '{$categories_filter}'";
    }
}

$query = "SELECT m.post_id, m.meta_key, m.meta_value, m2.meta_value AS lieu, m3.meta_value AS categorie FROM {$wpdb->postmeta} m
	JOIN {$wpdb->postmeta} m2
	ON m.post_id = m2.post_id
	JOIN {$wpdb->postmeta} m3
	ON m.post_id = m3.post_id
	JOIN {$wpdb->posts} p
	ON p.ID = m.post_id
	WHERE m2.meta_key = 'lieu' AND m3.meta_key = 'categories' AND p.post_type = 'training' AND m.meta_key LIKE %s AND m.meta_value != '' AND m.meta_value NOT LIKE %s{$filter}";
$meta_key_filter = '%session%date%de%debut';
$meta_value_filter = '%field%';

$total_query = "SELECT COUNT(1) AS count FROM ({$query}) AS combined_table";
$total_result = $wpdb->prepare($total_query, $meta_key_filter, $meta_value_filter);
$total = $wpdb->get_results($total_result)[0]->count;
$max_num_page = ceil($total / $posts_per_page);
$wpdb->max_num_pages = $max_num_page;

$session_query = $wpdb->prepare(sprintf('%s LIMIT %d, %d', $query, $offset, $posts_per_page), $meta_key_filter, $meta_value_filter);
$raw_sessions = $wpdb->get_results($session_query);
$sessions = [];
foreach ($raw_sessions as $raw_session) {
    $session = get_post($raw_session->post_id);
    $date_de_debut = DateTimeImmutable::createFromFormat('Ymd', $raw_session->meta_value);
    $session->session_start = $date_de_debut->format('d/m/Y');
    $session->session_end = get_field(str_replace('debut', 'fin', $raw_session->meta_key), $session);
    $sessions[] = $session;
}
?>

<div class="wp-block-query">
    <!-- wp:group {"tagName":"div","className":""} -->
    <div class="wp-block-group news-section section section--small section--tinted has-gutter">
        <div class="wp-block-group postlist">
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

			<!-- wp:query-no-results -->
			<div class="wp-block-query-no-results">
				<p>Nous n'avons pas trouvé de formations qui correspondent à vos critères de recherche.</p>
			</div>
			<!-- /wp:query-no-results -->
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
