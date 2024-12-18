<?php

/* Template Name: Espace Donateur - Login */
get_header();



$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {

    $user = get_user_by('email', $_POST['email']);
    if($user) {
        $password_match = wp_check_password($_POST['password'], $user->user_pass, $user->ID);
        if($password_match) {
            wp_redirect(get_permalink(get_page_by_path('accueil')));
            exit;
        }
    }

    $error_message = "Utilisateur inconnu ou mauvais mot de passe";

}


?>




<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">
    <?php if (isset($error_message)) : ?>
    <div class="aif-error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>


    <div class="container">

        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Connectez-vous !</h1>
        </header>

        <p>Connectez-vous pour accéder à votre espace donateur</p>

        <form role="form" method="POST" action="">
            <label>Votre adresse email (obligatoire)</label>
            <input placeholder="" value="" type="email" name="email" required="true">
            <label>Votre mot de passe (obligatoire)</label>
            <input placeholder="" value="" type="password" name="password" required="true">

            <button class="btn btn--dark" type="submit">Se connecter</button>

        </form>


    </div>

</main>