<?php

/* Template Name: Espace Donateur - Login */
get_header();



$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['login_nonce'])) {

    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']) ;

    if (!isset($_POST['login_nonce']) || !wp_verify_nonce($_POST['login_nonce'], 'login_form')) {
        die('Invalid nonce.');
    }
    $stored_user = get_user_by('email', $email);


    if ($stored_user) {
        if (get_email_is_verified(user_id: $stored_user->ID)) {

            $creds = array(
                'user_login'    => $email,
                'user_password' => $password,
                'remember'      => true
            );

            $user = wp_signon($creds, true);



            if (!is_wp_error($user)) {
                wp_set_current_user($user->ID);

                $verification_url = get_permalink(get_page_by_path('espace-donateur'));
                wp_redirect($verification_url);
                exit;
            } else {
                $error_message = "Mauvais email ou mot de passe";
            }


        } else {
            $url = get_permalink(get_page_by_path('espace-donateur/verifier-votre-email'));
            $error_message = "Votre email ne semble pas encore vérifié. Pour le faire veuillez vous rendre sur  <a class='aif-text-underline 
aif-text-underline--orange' href='" . $url . "'>" . $url . "</a>.";
        }

    } else {

        $url = get_permalink(get_page_by_path('espace-donateur/creer-votre-compte'));
        $error_message = "Votre compte n'existe pas. Pour le créer, veuillez vous rendre sur  <a class='aif-text-underline 
aif-text-underline--orange' href='" . $url . "'>" . $url . "</a>.";
    }

}
?>




<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">
    <div class="container">
        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="aif-mb1w">Mon espace Don</h1>
            <h2> Je me connecte </h2>
        </header>

        <?php
        if (!empty($error_message)) {
            $title = "Une erreur est survenue";
        }
aif_include_partial("alert", [
    "title" => $title,
"content" => $error_message])

?>
      


        <form role="form" method="POST" action="">
            <?php wp_nonce_field('login_form', 'login_nonce'); ?>
            <label for="email">Votre adresse email (obligatoire)</label>
            <input placeholder="" value="" type="email" name="email" id="email" autocomplete="email" required="true">
            <label for="email">Votre mot de passe (obligatoire)</label>
            <input placeholder="" id="password" value="" type="password" name="password" autocomplete="password"
                required="true">

            <button class="btn aif-mt1w" type="submit">Se connecter</button>

        </form>

        <section class="aif-mt2w aif-mb1w">
            <h3>Première connexion</h3>

            <p> La création de votre espace don n’est pas automatique lorsque vous faites un don.Si vous n’avez
                jamais
                créé votre espace, merci de cliquer sur “Créer votre compte”. </p>

            <a href="<?php echo get_permalink(get_page_by_path('espace-donateur/creer-votre-compte')) ?>"
                class="btn">Créer
                votre compte</a>

        </section>


    </div>

</main>

<?php
get_footer();
?>