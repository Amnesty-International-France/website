<?php
/**
 * Title: 404 Page Content
 * Description: Content for the 404 page.
 * Slug: amnesty/404
 * Inserter: no
 */
?>

<div class="page-404-content">
    <!-- wp:amnesty-core/chip-category {"label":"Erreur 404", "size":"large","style":"bg-black"} /-->
    <h1 class="wp-block-heading page-404-content-title">Page introuvable</h1>
    <h3 class="wp-block-heading page-404-content-subtitle">Il semble que la page que vous tentez d'afficher n'existe pas.</h3>
    <div class='custom-button-block center'>
        <a href="<?php echo home_url('/'); ?>" target="_blank" rel="noopener noreferrer" class="custom-button">
            <div class='content bg-yellow medium'>
                <div class="button-label">Retourner à l'accueil</div>
            </div>
        </a>
    </div>
</div>
