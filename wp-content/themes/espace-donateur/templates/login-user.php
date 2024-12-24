<?php

/* Template Name: Espace Donateur - Login */
get_header();



$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['login_nonce'])) {

    $email = sanitize_text_field($_POST['email']);
    $password = $_POST['password'] ;


    if (!isset($_POST['login_nonce']) || !wp_verify_nonce($_POST['login_nonce'], 'login_form')) {
        die('Invalid nonce.');
    }


    $stored_user = get_user_by('email', $email);

    if($stored_user) {
        if(get_email_is_verified(user_id: $stored_user->ID)) {

            $creds = array(
                'user_login'    => $email,
                'user_password' => $password,
                'remember'      => true
            );

            $user = wp_signon($creds, true);

            if (!is_wp_error($user)) {
                wp_set_current_user($user->ID);
                wp_redirect(home_url());
            } else {
                $error_message = "Mauvais email ou mot de passe";
            }


        } else {
            $url = get_permalink(get_page_by_path('verification-email'));
            $error_message = "Votre email ne semble pas encore vérifié. Pour le faire veuillez vous rendre sur  <a href='" . $url . "'>" . $url . "</a>.";
        }

    } else {

        $url = get_permalink(get_page_by_path('qui-etes-vous'));
        $error_message = "Votre compte n'existe pas. Pour le créer, veuillez vous rendre sur  <a href='" . $url . "'>" . $url . "</a>.";
    }




}
?>




<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">
    <?php if (!empty($error_message)) : ?>
    <div class="aif-error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>


    <div class="container">

        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Connectez-vous !</h1>
        </header>

        <p>Connectez-vous pour accéder à votre espace donateur</p>

        <form role="form" method="POST" action="">

            <?php wp_nonce_field('login_form', 'login_nonce'); ?>
            <label for="email">Votre adresse email (obligatoire)</label>
            <input placeholder="" value="" type="email" name="email" id="email" autocomplete="email" required="true">
            <label for="email">Votre mot de passe (obligatoire)</label>
            <input placeholder="" id="password" value="" type="password" name="password" autocomplete="password"
                required="true">

            <button class="btn btn--dark" type="submit">Se connecter</button>

        </form>

        <p>Pas encore de compte ?<a href="<?php echo get_permalink(get_page_by_path('qui-etes-vous')) ?>"> Rendez-vous
                ici
                pour créer votre compte</a></p>


    </div>

</main>

<?php
// Appel du pied de page
get_footer();
?>