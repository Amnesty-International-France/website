<?php

/* Template Name: Espace Donateur - Login */
get_header();

$email = "";



if (!isset($_GET['ID'])) {
    $error_title = "Une erreur est survenue";
    $error_message = "Nous ne pouvons récupérer l'utilisateur associé à l'identifiant.";

} else {
    $user = get_user_by("ID", $_GET['ID']);
    if ($user) {
        $email = $user->user_email;
    }

}

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

                $verification_url = get_permalink(get_page_by_path('espace-don'));
                wp_redirect($verification_url);
                exit;
            } else {
                $error_message = "Mauvais email ou mot de passe";
            }


        } else {
            $url = get_permalink(get_page_by_path('espace-don/verifier-votre-email'));
            $error_message = "Votre email ne semble pas encore vérifié. Pour le faire veuillez vous rendre sur  <a class='aif-text-underline 
aif-text-underline--orange' href='" . $url . "'>" . $url . "</a>.";
        }

    } else {

        $url = get_permalink(get_page_by_path('espace-don/creer-votre-compte'));
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

            aif_include_partial("alert", [
                "title" => $title,
            "content" => $error_message]);

        }

?>

        <section>
            <h3 class="aif-sr-only"> Formulaire de connexion </h3>
            <form class="aif-form-container" role="form" method="POST" action="">
                <?php wp_nonce_field('login_form', 'login_nonce'); ?>
                <label for="email">Votre adresse email (obligatoire)</label>
                <input placeholder=""
                    value="<?= $email ? $email : '' ?>"
                    type="email" name="email" id="email" autocomplete="email" required="true">

                 <label for="password">Votre mot de pase (obligatoire)</label>
                 <div class="aif-password-container">
                    <input  id="password" type="password" id="password" class="aif-password-container__input">
                    <svg id="togglePassword" class="aif-password-container__toggle" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/>
                    </svg>
            </div>

                <button class="btn aif-mt1w" type="submit">Se connecter</button>

            </form>

            <a class="aif-text-underline aif-text-underline--orange aif-mt1w aif-block"
                href="<?=  get_permalink(get_page_by_path('espace-don/mot-de-passe-oublie')) ?>">
                Mot de passe oublié ? </a>

        </section>


        <section class="aif-mt2w aif-mb1w">
            <h3>Première connexion</h3>

            <p> La création de votre espace don n’est pas automatique lorsque vous faites un don.Si vous n’avez
                jamais
                créé votre espace, merci de cliquer sur “Créer votre compte”. </p>

            <a href="<?=  get_permalink(get_page_by_path('espace-don/creer-votre-compte')) ?>"
                class="btn">Créer
                votre compte</a>

        </section>


    </div>

</main>

<script>

document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');

            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                this.setAttribute('aria-pressed', type === 'password' ? 'false' : 'true');
            });
        });

</script>

<?php
get_footer();
?>