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
$posts_per_page = AIF_TRAININGS_PER_PAGE;
$offset = ($paged - 1) * $posts_per_page;

[$query, $query_args] = aif_get_trainings_session_query();
$max_num_page = aif_get_trainings_max_num_pages($posts_per_page);

$session_query = $wpdb->prepare(sprintf('%s LIMIT %d, %d', $query, $offset, $posts_per_page), $query_args);
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
