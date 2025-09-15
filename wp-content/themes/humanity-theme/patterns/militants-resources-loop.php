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

$args = [
    'post_type' => 'document',
    'posts_per_page' => 2,
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

$search = sanitize_text_field($_GET['q']) ?? null;
if ($search) {
    $args['s'] = $search;
    unset($args['paged']);
}

$query = new WP_Query($args);
?>
<form action="<?php echo esc_url($form_url); ?>">
	<input type="text" name="q" id="search-ressource" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="Rechercher une ressource...">
	<div id="results-ressource"></div>
	<button type="submit">Rechercher</button>
</form>
<?php if ($query->have_posts()) : ?>
	<?php while ($query->have_posts()) : $query->the_post(); ?>
		<?php
        $block = [
            'blockName'    => 'amnesty-core/article-card',
            'attrs'        => ['direction' => 'portrait'],
            'innerBlocks'  => [],
            'innerHTML'    => '',
            'innerContent' => [],
        ];
	    echo render_block($block);
	    ?>
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

<script>
	document.addEventListener("DOMContentLoaded", function () {
		const input = document.getElementById("search-ressource");
		const results = document.getElementById("results-ressource");

		input.addEventListener("keyup", function () {
			let searchTerm = this.value.trim();

			if (searchTerm.length < 2) {
				results.innerHTML = "";
				return;
			}

			fetch(`/wp-json/humanity/v1/search-document-militant?term=${encodeURIComponent(searchTerm)}`)
				.then(res => res.json())
				.then(data => {
					results.innerHTML = "";

					if (data.length > 0) {
						let ul = document.createElement("ul");
						data.forEach(item => {
							let li = document.createElement("li");
							li.innerHTML = `<a href="${window.location.href.split('?')[0]}?q=${item.title}">${item.title}</a>`;
							ul.appendChild(li);
						});
						results.appendChild(ul);
					} else {
						results.innerHTML = "<p>Aucun résultat</p>";
					}
				});
		});
	});
</script>
