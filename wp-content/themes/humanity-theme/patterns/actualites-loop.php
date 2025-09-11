<?php
/**
 * Title: Actualities Loop Pattern
 * Description: Actualities Loop
 * Slug: amnesty/actualites-loop
 * Inserter: no
 */

$cat = get_category_by_slug('actualites');
$cat_id = $cat ? $cat->term_id : 0;

$paged = get_query_var('paged') ? get_query_var('paged') : 1;

$args = [
    'post_type' => 'post',
    'posts_per_page' => 18,
    'orderby' => 'date',
    'order' => 'DESC',
    'category__in' => [$cat_id],
    'paged' => $paged,
];

$tax_query = [];

if (isset($_GET['qlocation'])) {
    $tax_query[] = [
        'taxonomy' => 'location',
        'field'    => 'term_id',
        'terms'    => array_map('intval', explode(',', $_GET['qlocation'])),
    ];
}

if (isset($_GET['qcombat'])) {
    $tax_query[] = [
        'taxonomy' => 'combat',
        'field'    => 'term_id',
        'terms'    => array_map('intval', explode(',', $_GET['qcombat'])),
    ];
}

if (!empty($tax_query)) {
    $args['tax_query'] = $tax_query;
}

$query = new WP_Query($args);
?>

<div class="wp-block-query">
  <div class="wp-block-group postlist">
    <div class="post-grid">
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
          <p>Aucun article trouvé.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

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
</div>
