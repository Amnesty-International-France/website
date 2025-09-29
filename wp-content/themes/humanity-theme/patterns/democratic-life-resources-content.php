<?php

/**
 * Title: Democratic life resources Content Pattern
 * Description: Democratic life resources content
 * Slug: amnesty/democratic-life-resources-content
 * Inserter: no
 */

?>

<!-- wp:pattern {"slug":"amnesty/my-space-sidebar"} /-->
<main class="aif-donor-space-content">
	<!-- wp:pattern {"slug":"amnesty/my-space-header"} /-->
    <div class="aif-democratic-life-resources">
		<h2 class="aif-democratic-life-title"><?php the_title(); ?></h2>
		<p>Texte d’intro à prévoir un mouvement international ancré dans chaque pays. Amnesty en France est une association reconnue d’utilité publique.</p>
		<!-- wp:amnesty-core/archive-filters-democratic-resources /-->
		<!-- wp:pattern {"slug":"amnesty/democratic-resources-loop"} /-->
    </div>
</main>
