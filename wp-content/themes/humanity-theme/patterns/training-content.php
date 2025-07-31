<?php

declare(strict_types=1);

/**
 * Title: Training Content
 * Description: Output the content of a single training post
 * Slug: amnesty/training-content
 * Inserter: no
 */

?>


<!-- wp:group {"tagName":"section","className":"training"} -->
<section class="wp-block-group training-single">
	<!-- wp:group {"tagName":"header","className":"fo-header"} -->
	<header class="wp-block-group training-header">
		<div class="yoast-breadcrumb-wrapper">
			<?php if (function_exists('yoast_breadcrumb')) {
				yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>');
			} ?>
		</div>
		<!-- wp:group {"className":"files"} -->
        <!-- wp:pattern {"slug":"amnesty/post-training-metadata"} /-->

		<!-- /wp:group -->
	</header>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"training","className":"training-content"} -->
	<article class="wp-block-group training-content">
		<!-- wp:pattern {"slug":"amnesty/featured-image"} /-->
		<!-- wp:post-content /-->

		<div class="member-only">
			<h3 class="member-only-title">Accès réservé aux membres</h3>
			<p class="member-only-content">Vous êtes membre d'Amnesty International ?
				<a href="" class="member-only-link">Connectez-vous</a>
				pour vous inscrire à cette formation.
			</p>
			<p class="member-only-content">Vous voulez devenir membre ?
				<a href="" class="member-only-link">C’est très simple, ça se passe ici.</a>
			</p>
		</div>

		<div class="link">
            <a href="https://amnestyfrance.typeform.com/to/udD4LL#titre=xxxxx&prenom=xxxxx&nom=xxxxx"
               class="ask-formation">
                DEMANDER LA FORMATION EN REGION
            </a>
        </div>
		<!-- wp:group {"tagName":"footer","className":"article-footer"} -->
		<footer class="wp-block-group article-footer">
			<!-- wp:pattern {"slug":"amnesty/post-terms"} /-->
		</footer>
		<!-- /wp:group -->
	</article>
	<!-- /wp:group -->

</section>
<!-- /wp:group -->
