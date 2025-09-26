<?php

/**
 * Title: Accueil Content Pattern
 * Description: Accueil content
 * Slug: amnesty/accueil-content
 * Inserter: no
 */

?>

<!-- wp:pattern {"slug":"amnesty/my-space-sidebar"} /-->
<main class="aif-donor-space-content">
    <!-- wp:pattern {"slug":"amnesty/my-space-header"} /-->
    <div class="aif-homepage">
        <?php the_content(); ?>
        <div class="aif-homepage-footer">
            <div class="aif-homepage-footer-links">
                <a class="aif-homepage-footer-links-project" href="/mon-espace/agir-et-se-mobiliser/militants-dossiers-projets-a-suivre/">
                    <span class="aif-homepage-footer-links-project-title">Actions et projets à suivre</span>
                    <div class="aif-homepage-footer-links-project-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="77" viewBox="0 0 80 77" >
                            <path d="M32.1455 64.686L24.4478 48.5973L21.7306 48.7429L13.0091 44.4824L10.4258 32.7217L16.9837 25.7639L38.3834 21.3517L57.2683 7.57111C57.9101 7.42859 58.4832 7.6692 58.7118 8.25922L61.5809 20.6529L65.273 21.1648L68.289 33.9477L64.9676 35.7638L67.8759 48.6601C67.6538 49.3621 66.95 49.6502 66.2266 49.4617L44.0986 44.605L42.4739 44.7215C43.7035 47.8831 45.503 49.5797 40.4964 49.9965L45.5422 61.9963L32.1455 64.6844V64.686ZM63.7021 45.3422L56.202 12.417L40.4082 24.0337L44.2048 40.9974L63.7037 45.3422H63.7021ZM39.3076 24.4827L18.8664 28.5838L22.7055 45.4357L43.1466 41.3346L39.3076 24.4827ZM21.3632 44.6204L17.9373 29.5846L14.0346 33.8006C14.6894 36.1668 14.9033 39.0128 15.679 41.3039C16.257 43.0112 19.709 43.8556 21.3632 44.6204ZM61.8781 22.1211L64.6541 34.3063L67.0627 33.3945L64.481 22.3127L63.9715 22.0169L61.8765 22.1211H61.8781ZM34.7386 46.274L28.1089 47.6042L34.1981 61.0216L40.6891 59.719L34.737 46.274H34.7386ZM41.0712 45.0311L38.3311 45.5552L39.9412 48.9084L42.4722 48.5192L41.0695 45.0311H41.0712Z" />
                        </svg>
                    </div>
                </a>
                <a class="aif-homepage-footer-links-trainings" href="/mon-espace/boite-a-outils/se-former/">
                    <span class="aif-homepage-footer-links-trainings-title">Catalogue de formations</span>
                    <div class="aif-homepage-footer-links-trainings-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="77" height="70" viewBox="0 0 77 70" >
                            <path d="M38.5 17.6225C33.2114 13.3098 26.3545 10.9286 19.25 10.9375C15.8748 10.9375 12.6344 11.4625 9.625 12.4309V53.9934C12.7166 53.0018 15.9716 52.4967 19.25 52.5C26.6452 52.5 33.3923 55.0288 38.5 59.185M38.5 17.6225C43.7884 13.3096 50.6454 10.9283 57.75 10.9375C61.1252 10.9375 64.3656 11.4625 67.375 12.4309V53.9934C64.2834 53.0018 61.0284 52.4967 57.75 52.5C50.6455 52.4911 43.7886 54.8723 38.5 59.185M38.5 17.6225V59.185" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </a>
                <a class="aif-homepage-footer-links-donations" href="/mes-dons/mes-recus-fiscaux/">
                    <span class="aif-homepage-footer-links-donations-title">Mes dons</span>
                    <div class="aif-homepage-footer-links-donations-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="82" height="80" viewBox="0 0 82 80">
                            <path d="M67.3296 18.2822C61.1534 12.5768 51.1392 12.5768 44.963 18.2822L40.7704 22.1514L36.5741 18.2822C30.3979 12.5768 20.3874 12.5768 14.2112 18.2822C7.26294 24.6975 7.26294 35.0787 14.2112 41.4939L40.7704 66.0214L67.3296 41.4939C74.2741 35.0787 74.2741 24.6938 67.3296 18.2822ZM63.3919 37.6804L40.7704 58.9223L18.1452 37.6804C15.8661 35.573 14.9832 32.8225 14.9832 29.8974C14.9832 26.9722 15.493 24.5711 17.7758 22.4674C19.789 20.6052 22.4966 19.5794 25.3963 19.5794C28.2924 19.5794 31 21.3486 33.0132 23.2145L40.7704 29.9977L48.5239 23.2107C50.5408 21.3449 53.2447 19.5757 56.1444 19.5757C59.0441 19.5757 61.7518 20.6015 63.765 22.4637C66.0478 24.5674 66.5539 26.9685 66.5539 29.8936C66.5539 32.8188 65.6747 35.573 63.3919 37.6804Z" />
                        </svg>
                    </div>
                </a>
            </div>
            <div class="aif-homepage-footer-other-sites">
                <span class="aif-homepage-footer-other-sites-title">NOS AUTRES SITES :</span>
                <div class="aif-homepage-footer-other-sites-list">
                    <a class="aif-homepage-footer-other-sites-list-item" href="https://urgent.amnesty.fr/" target="_blank">Actions Urgentes</a>
                    <a class="aif-homepage-footer-other-sites-list-item" href="https://boutique.amnesty.fr/" target="_blank">Boutique</a>
                    <a class="aif-homepage-footer-other-sites-list-item" href="https://123log.groupe-routage.fr/catalogue/amnesty" target="_blank">Commande de matériel</a>
                    <a class="aif-homepage-footer-other-sites-list-item" href="https://formation.amnesty.fr/learn" target="_blank">PLATEFORME DE FORMATIONS EN LIGNE</a>
                </div>
            </div>
        </div>
    </div>
</main>
