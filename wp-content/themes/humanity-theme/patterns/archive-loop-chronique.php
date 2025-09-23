<?php
/**
 * Title: Archive loop for the chronicle
 * Description: Template for the loop on archive chronicle page
 * Slug: amnesty/archive-loop-chronique
 * Inserter: yes
 */

?>

<!-- wp:query {"query":{"postType":"chronique","perPage":24,"offset":0}} -->
<section class="wp-block-group">
	<!-- wp:group {"tagName":"div","className":"postlist"} -->
	<div class="wp-block-group postlist">
		<!-- wp:post-template {"layout":{"type":"grid","columnCount":4}} -->
			<!-- wp:amnesty-core/chronicle-card /-->
		<!-- /wp:post-template -->

		<!-- wp:query-no-results -->
		<div class="wp-block-query-no-results">
			<p class="center">Il n'y a pas encore de magazine La Chronique de paru.</p>
		</div>
		<!-- /wp:query-no-results -->
	</div>
	<!-- /wp:group -->

	<!-- wp:query-pagination {"align":"center","className":"section section--small","paginationArrow":"none","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"nowrap"}} -->
		<!-- wp:query-pagination-numbers {"midSize":1,"className":"page-numbers"} /-->
	<!-- /wp:query-pagination -->
</section>
<!-- /wp:query -->
