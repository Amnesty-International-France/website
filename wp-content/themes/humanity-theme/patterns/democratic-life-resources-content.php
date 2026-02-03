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
		<p>Dans cet espace, vous pouvez accéder aux ressources concernant la vie démocratique d'Amnesty International France : comptes-rendus, décisions, rapports et autres documents. Le travail des instances de gouvernance d'AIF est répertorié ici.</p>
		<!-- wp:amnesty-core/archive-filters-democratic-resources /-->
		<!-- wp:pattern {"slug":"amnesty/democratic-resources-loop"} /-->
    </div>
</main>
