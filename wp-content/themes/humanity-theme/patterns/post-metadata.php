<?php

/**
 * Title: Post Metadata
 * Description: Output contextual data for a post
 * Slug: amnesty/post-metadata
 * Inserter: no
 */

// prevent weird output in the site editor
if (! get_the_ID()) {
    return;
}

?>
<!-- wp:group {"tagName":"div","className":"article-meta-wrapper"} -->
<div class="article-meta-wrapper">

	<!-- wp:group {"tagName":"div","className":"article-meta container--md mx-auto"} -->
	<div class="wp-block-group article-meta">

		<!-- wp:group {"tagName":"div","className":"article-metaActions"} -->
		<div class="wp-block-group article-metaActions">
			<!-- wp:pattern {"slug":"amnesty/post-back-link"} /-->
		</div>
		<!-- /wp:group -->

		<!-- wp:post-title {"level":1,"className":"article-title"} /-->

		<?php
        if (get_post_type(get_the_ID()) === 'local-structures') {
            $address = get_field('adresse');

            if (! empty($address)) {
                ?>
                    <p class="address-text"><?php echo esc_html($address); ?></p>
                <?php
            }
        }
?>

		<!-- wp:group {"tagName":"div","className":"article-metaData"} -->
		<div class="wp-block-group article-metaData">
			<div class="published-updated">
				<!-- wp:pattern {"slug":"amnesty/post-published-updated-date"} /-->
			</div>
			<div class="reading-time">
				<!-- wp:pattern {"slug":"amnesty/post-reading-time"} /-->
			</div>
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"tagName":"div","className":"article-metaActions article-chip-categories"} -->
		<div class="wp-block-group article-metaActions article-chip-categories">
			<!-- wp:pattern {"slug":"amnesty/post-term-list-metadata"} /-->
		</div>
		<!-- /wp:group -->
		 
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
