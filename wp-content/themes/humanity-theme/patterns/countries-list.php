<?php

/**
 * Title: Countries List
 * Description: Countries list for archive page.
 * Slug: amnesty/countries-list
 * Inserter: no
 */

$args = array(
    'post_type' => 'fiche_pays',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
);
$all_countries = new WP_Query($args);

$doc_id = get_option('countries_global_document_id');
$doc_url = $doc_id ? wp_get_attachment_url($doc_id) : '';

?>

<div class="az-filter">
    <h2 class="title">Pays de A à Z</h2>
    <div class="az-index">
        <?php foreach (range('A', 'Z') as $letter) : ?>
            <div class="az-letter<?php echo $letter === 'A' ? ' active' : ''; ?>" data-letter="<?php echo $letter; ?>">
                <?php echo $letter; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="az-display">
        <div class="az-letter-display">A</div>
        <div class="country-grid">
            <?php if ($all_countries->have_posts()) : ?>
                <?php while ($all_countries->have_posts()) : $all_countries->the_post();
                    $title = get_the_title();
                    $first_letter = strtoupper(mb_substr($title, 0, 1));
                ?>
                    <div class="country" data-letter="<?php echo esc_attr($first_letter); ?>">
                        <a href="<?php the_permalink(); ?>"><?php echo esc_html($title); ?></a>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="get-report">
        <h2 class="title">OBTENIR LE RAPPORT D'AMNESTY INTERNATIONAL 2024/25</h2>
        <h3 class="subtitle">DOCUMENTER LA SITUATION DES DROITS DE L'HOMME DANS 150 PAYS EN 2024</h3>
        <div class="download">
            <div class="icon-container">
                <img src="<?php echo esc_url(amnesty_asset_uri('images')); ?>/icon-download-arrow-dark.svg" alt="Download Icon" />
            </div>
            <p class="label">
                <?php if ($doc_url) : ?>
                    <a href="<?php echo esc_url($doc_url); ?>" target="_blank">Télécharger</a>
                <?php else : ?>
                    Télécharger
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>
