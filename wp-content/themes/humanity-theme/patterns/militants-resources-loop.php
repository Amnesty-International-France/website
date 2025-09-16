<?php

/**
 * Title: Militants resources loop Pattern
 * Description: Militants resources loop
 * Slug: amnesty/militants-resources-loop
 * Inserter: no
 */

global $wp;
$form_url = home_url(add_query_arg([], $wp->request));
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

$meta_query = [
    [
        'key' => 'document_private',
        'value' => true,
        'compare' => '=',
    ],
];

$search = sanitize_text_field($_GET['q']) ?? null;
$posts_per_page = $search ? -1 : 2;

$args = [
    'post_type' => 'document',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'meta_query' => $meta_query,
];

$tax_query = [
    [
        'taxonomy' => 'document_militant_type',
        'operator' => 'EXISTS',
    ],
];

$type_filter = $_GET['qdocument_militant_type'] ?? null;
if ($type_filter) {
    $tax_query[] = [
        [
            'taxonomy' => 'document_militant_type',
            'field'    => 'term_id',
            'terms'    => array_map('intval', explode(',', $type_filter)),
        ],
    ];
}

$args['tax_query'] = $tax_query;

if ($search) {
    $args['s'] = $search;
    unset($args['paged']);
}

$query = new WP_Query($args);
?>
<?php if ($query->have_posts()) : ?>
	<?php while ($query->have_posts()) : $query->the_post(); ?>
		<?php require locate_template('partials/document/card.php'); ?>
	<?php endwhile; ?>
<?php else : ?>
	<div class="wp-block-query-no-results">
		<p>Aucune ressources trouvées.</p>
	</div>
<?php endif; ?>
<?php
$big = 999999999;
$pagination = paginate_links([
    'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
    'format'    => '?paged=%#%',
    'current'   => max(1, get_query_var('paged')),
    'total'     => $query->max_num_pages,
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
