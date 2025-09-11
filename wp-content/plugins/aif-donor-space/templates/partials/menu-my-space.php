<?php
if (!isset($sf_member)) {
    echo '<!-- Données utilisateur non disponibles -->';
    return;
}

$is_member = !empty($sf_member->isMembre);
?>

<nav class="aif-donor-space-menu">
    <h3>Mon Espace</h3>
    <ul>
        <!-- Liens pour tous -->
        <li><a href="/mon-espace/">Accueil</a></li>

        <!-- Liens pour les adhérents (isMembre = true) -->
        <?php if ($is_member): ?>
            <li><a href="#">Actualités</a></li>
            <li class="has-submenu">
                <a href="#">Boite à outils</a>
                <ul class="submenu">
                    <li><a href="#">Se former</a></li>
                    <li><a href="#">Fiches pratiques</a></li>
                </ul>
            </li>
            <li><a href="#">Vie démocratique</a></li>
        <?php endif; ?>

        <!-- Liens pour tous -->
        <li><a href="/mon-espace/mes-dons/">Mes dons</a></li>
        <li><a href="/mon-espace/nous-contacter/">Nous contacter</a></li>

        <hr>

        <li><a href="/mon-espace/mes-informations-personnelles/">Mon compte</a></li>
    </ul>
</nav>
