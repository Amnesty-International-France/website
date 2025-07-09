<?php
/**
 * Title: Petition Hero
 * Description: Outputs the petition's hero, if any
 * Slug: amnesty/petition-hero
 * Inserter: no
 */

$featured_image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );

?>

<?php if ( ! is_front_page() ) : ?>
    <section class="petition-hero-block">
        <!-- wp:pattern {"slug":"amnesty/featured-image"} /-->
        <div class="petition-hero-wrapper">
            <div class="petition-hero-content">
                <div class="yoast-breadcrumb-wrapper">
                    <?php if ( function_exists('yoast_breadcrumb') ) yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>'); ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>


<?php

if ( ! is_admin() ) {
    add_filter( 'the_content', 'amnesty_remove_first_hero_from_content', 0 );
}
