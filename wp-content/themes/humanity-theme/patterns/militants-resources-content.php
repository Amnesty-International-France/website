<?php

/**
 * Title: Militants resources Content Pattern
 * Description: Militants resources content
 * Slug: amnesty/militants-resources-content
 * Inserter: no
 */
?>

<!-- wp:pattern {"slug":"amnesty/my-space-sidebar"} /-->
<main class="aif-donor-space-content">
	<!-- wp:pattern {"slug":"amnesty/my-space-header"} /-->
    <div class="aif-militants-resources">
		<h2 class="aif-militants-resources-title"><?php the_title(); ?></h2>
		<p>Retrouvez tous les documents liés à la vie militante : administratif, guide, fiches pratiques, matériel à télécharger etc.</p>
		<!-- wp:amnesty-core/archive-filters-militants-resources /-->
		<!-- wp:pattern {"slug":"amnesty/militants-resources-loop"} /-->
	</div>
</main>
