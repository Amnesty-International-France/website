<?php
/* Template Name: Espace Donateur - Home */
get_header();

if (is_user_logged_in()) {
    // L'utilisateur est connecté
    echo '<p>Welcome, ' . wp_get_current_user()->user_login . '!</p>';
    // Vous pouvez ajouter ici tout contenu ou fonctionnalité spécifique pour les utilisateurs connectés
} else {
    // L'utilisateur n'est pas connecté
    echo "l'utilisateur n'est pas connecté";
    // Vous pouvez ajouter ici tout contenu ou fonctionnalité spécifique pour les utilisateurs non connectés
}

?>




<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">


    <div class="container">


        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Mon espace donateur</h1>
        </header>



    </div>

</main>