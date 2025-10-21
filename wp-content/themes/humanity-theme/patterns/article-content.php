<?php
/**
 * Title: Article Content
 * Description: Output the content of a single post
 * Slug: amnesty/article-content
 * Inserter: no
 */

$term = amnesty_get_a_post_term(get_the_ID());
$category_class = $term ? $term->slug : '';
$post_type_class = get_post_type() ?: '';

?>

<!-- wp:group {"tagName":"section","className":"article"} -->
<section class="wp-block-group article <?php echo esc_attr($category_class); ?> <?php echo esc_attr($post_type_class); ?>">
  <!-- wp:group {"tagName":"header","className":"article-header"} -->
  <header class="wp-block-group article-header">
    <?php if ($category_class === 'dossiers' || $category_class === 'campagnes') : ?>
      <div class="files-wrapper">
        <div class="files-wrapper-left">
          <div class="yoast-breadcrumb-wrapper">
            <?php if (function_exists('yoast_breadcrumb')) {
                yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>');
            } ?>
          </div>
          <!-- wp:group {"className":"files"} -->
            <!-- wp:pattern {"slug":"amnesty/post-metadata"} /-->
          <!-- /wp:group -->
        </div>
        <div class="files-wrapper-right">
          <!-- wp:pattern {"slug":"amnesty/featured-image"} /-->
        </div>
      </div>
    <?php else : ?>
      <?php if (get_post_type() !== 'fiche_pays') : ?>
        <div class="yoast-breadcrumb-wrapper">
          <?php if (function_exists('yoast_breadcrumb')) {
              yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>');
          } ?>
        </div>
        <!-- wp:pattern {"slug":"amnesty/post-metadata"} /-->
      <?php endif; ?>
      <!-- wp:pattern {"slug":"amnesty/featured-image"} /-->
    <?php endif; ?>
  </header>
  <!-- /wp:group -->

  <?php if ($category_class === 'dossiers') : ?>
	<?php
        $display_toc = get_field('display_toc');
      ?>

	<?php if ($display_toc) : ?>
		<div id="toc-container" class="toc-container">
		<div id="toc-button" class="toc-button">
			<div class="icon-container">
			<svg id="toc-icon-closed" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
				<path d="M8 4H21V6H8V4ZM4.5 6.5C4.102 6.5 3.72 6.342 3.439 6.061 3.158 5.779 3 5.398 3 5c0-.398.158-.779.439-1.061A1.5 1.5 0 0 1 4.5 3.5c.398 0 .779.158 1.061.439.282.282.439.663.439 1.061 0 .398-.158.779-.439 1.061A1.5 1.5 0 0 1 4.5 6.5ZM4.5 13.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3ZM4.5 20.4a1.5 1.5 0 1 1 0-3c.398 0 .779.158 1.061.439.282.282.439.663.439 1.061 0 .398-.158.779-.439 1.061A1.5 1.5 0 0 1 4.5 20.4ZM8 11H21V13H8V11ZM8 18H21V20H8V18Z" fill="#FFFF00"/>
			</svg>
			<svg id="toc-icon-open" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" style="display: none;">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M12 10.586L16.95 5.63599L18.364 7.04999L13.414 12L18.364 16.95L16.95 18.364L12 13.414L7.04999 18.364L5.63599 16.95L10.586 12L5.63599 7.04999L7.04999 5.63599L12 10.586Z" fill="#FFFF00"/>
			</svg>
			</div>
			Sommaire
		</div>

		<div id="toc-dropdown" class="toc-dropdown">
			<div class="toc-arrow"></div>
			<ul id="toc-list"></ul>
		</div>
		</div>
	<?php endif; ?>
  <?php endif; ?>

  <!-- wp:group {"tagName":"article","className":"article-content"} -->
  <article class="wp-block-group article-content">
    <!-- wp:post-content /-->
  </article>
  <!-- /wp:group -->

  <?php if ($category_class === 'chroniques') : ?>
		<div class="container--large mx-auto">
			<!-- wp:amnesty-core/call-to-action {"title":"Découvrez La Chronique sans plus tarder : recevez un numéro \"découverte\" gratuit","subTitle":"Remplissez ce formulaire en indiquant votre adresse postale et recevez gratuitement votre premier numéro dans votre boîte aux lettres !", "buttonLabel":"","externalUrl":"/numero-decouverte-la-chronique/", "linkType": "external"} /-->
		</div>
	<?php endif; ?>

  <?php if (in_array($category_class, ['actualites', 'chroniques']) && get_the_ID()) : ?>
    <!-- wp:group {"tagName":"footer","className":"article-footer"} -->
    <footer class="wp-block-group article-footer">
      <!-- wp:pattern {"slug":"amnesty/post-terms"} /-->
    </footer>
    <!-- /wp:group -->
  <?php endif; ?>

	<?php if ($category_class === 'chroniques') : ?>
		<!-- wp:amnesty-core/related-posts {"title":"ET AUSSI", "nb_posts": 4, "display": "chronique"} /-->
	<?php elseif (in_array($category_class, ['actualites', 'dossiers']) || $post_type_class === 'landmark') : ?>
		<!-- wp:amnesty-core/related-posts {"title":"Voir aussi"} /-->
	<?php endif; ?>

</section>
<!-- /wp:group -->
