<?php

/**
 * Title: Page Change Their History Content Pattern
 * Description: Page content pattern for the theme
 * Slug: amnesty/page-change-their-history-content
 * Inserter: no
 */

declare(strict_types=1);

if (! trim((string) get_the_content())) {
    return;
}

?>

<!-- wp:group {"tagName":"section","className":"clh-section clh-editable"} -->
<section class="wp-block-group clh-section clh-editable">
	<div class="page-content">
		<!-- wp:post-content /-->
	</div>
</section>
<!-- /wp:group -->
