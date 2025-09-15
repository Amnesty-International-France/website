<?php
/**
 * Title: Trainings Loop Pattern for a simulated archive page (My Space)
 * Description: Training Loop for my space
 * Slug: amnesty/trainings-loop-my-space
 * Inserter: no
 */

$paged = get_query_var('paged') ? get_query_var('paged') : 1;

$args = [
    'post_type'      => 'training',
    'posts_per_page' => 18,
    'paged'          => $paged,
];

$meta_query = ['relation' => 'AND'];

if (isset($_GET['qcategories']) && !empty($_GET['qcategories'])) {
    $categories = explode(',', sanitize_text_field($_GET['qcategories']));
    $meta_query[] = [
        'key'     => 'categories',
        'value'   => $categories,
        'compare' => 'IN',
    ];
}

if (isset($_GET['qlieu']) && !empty($_GET['qlieu'])) {
    $locations = explode(',', sanitize_text_field($_GET['qlieu']));
    $meta_query[] = [
        'key'     => 'lieu',
        'value'   => $locations,
        'compare' => 'IN',
    ];
}

if (isset($_GET['qperiod']) && !empty($_GET['qperiod'])) {
    $periods = explode(',', sanitize_text_field($_GET['qperiod']));
    $date_or_query = ['relation' => 'OR'];
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
        $meta_query[] = $date_or_query;
    }
}

if (count($meta_query) > 1) {
    $args['meta_query'] = $meta_query;
}

$args['meta_key'] = 'date';
$args['orderby'] = 'meta_value';
$args['order'] = 'DESC';

$query = new WP_Query($args);
?>

<div class="wp-block-query">
  <div class="wp-block-group postlist">
    <div class="post-grid">
      <?php if ($query->have_posts()) : ?>
        <?php while ($query->have_posts()) : $query->the_post(); ?>
          <?php
            $permalink = home_url('/mon-espace/boite-a-outils/se-former/' . get_post_field('post_name', get_the_ID()) . '/');

            $title            = get_the_title();
            $thumbnail        = get_the_post_thumbnail(get_the_ID(), 'large');
            $lieu             = get_post_meta(get_the_ID(), 'lieu', true);
            $city             = get_post_meta(get_the_ID(), 'city', true);
            $is_members_only  = get_post_meta(get_the_ID(), 'members_only', true);
            $link             = '#';
            $icon             = '';

            $date_value = get_post_meta(get_the_ID(), 'date', true);
            $formatted_date = !empty($date_value) ? date_i18n(get_option('date_format'), strtotime($date_value)) : '';

            include(locate_template('partials/training-card.php'));
            ?>
        <?php endwhile; ?>
      <?php else : ?>
        <div class="wp-block-query-no-results">
          <p>Aucune formation trouvée.</p>
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
