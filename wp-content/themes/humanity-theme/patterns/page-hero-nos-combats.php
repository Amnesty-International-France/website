<?php
/**
 * Title: Page Hero Nos Combats
 * Description: Outputs the page's hero, if any
 * Slug: amnesty/page-hero-nos-combats
 * Inserter: no
 */

$page_title = get_the_title();
$has_chapo = has_block('amnesty-core/chapo');
?>

    <section class="page-hero-block no-featured-image nos-combats-hero">
        <div class="yoast-breadcrumb-wrapper">
            <?php if (function_exists('yoast_breadcrumb')) {
                yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>');
            } ?>
        </div>
        <div class="page-hero-title-wrapper">
            <div class="container">
                <h1 class="page-hero-title"><?php echo esc_html($page_title); ?></h1>
            </div>
        </div>
		<?php if ($has_chapo && !is_admin()) {
		    echo '<div class="chapo-wrapper">';
		    echo render_block(amnesty_get_chapo_data());
		    echo '</div>';
		}
?>
    </section>


<?php
if ($has_chapo && !is_admin()) {
    add_filter('the_content', 'amnesty_remove_chapo_from_content', 0);
}
