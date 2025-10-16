<?php
/**
 * Title: Petitions Loop Pattern
 * Description: Petitions Loop
 * Slug: amnesty/petitions-loop
 * Inserter: no
 */
$GLOBALS['is_my_space_petitions_loop'] = true;

$paged = get_query_var('paged') ? get_query_var('paged') : 1;

$args = [
    'post_type' => 'petition',
    'posts_per_page' => 18,
    'orderby' => 'date',
    'order' => 'DESC',
    'paged' => $paged,
];

$query = new WP_Query($args);
?>
<h2 class="aif-petitions-title"><?php the_title(); ?></h2>
<div class="wp-block-query">
  <div class="wp-block-group postlist">
    <div class="post-grid">
      <?php if ($query->have_posts()) : ?>
        <?php while ($query->have_posts()) : $query->the_post(); ?>
          <?php
          $block = [
              'blockName'    => 'amnesty-core/petition-card',
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
          <p>Aucune pétition trouvée.</p>
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

unset($GLOBALS['is_my_space_petitions_loop']);
?>
</div>
