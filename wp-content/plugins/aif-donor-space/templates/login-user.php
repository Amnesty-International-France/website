<?php

/* Template Name: Espace Donateur - Login */
get_header();

$email = "";

$reset_email_url = "";

if (!isset($_GET['user'])) {
    $error_title = "Une erreur est survenue";
    $error_message = "Nous ne pouvons récupérer l'utilisateur associé à l'identifiant.";

} else {
    $user = get_user_by("email", $_GET['user']);
    if ($user) {
        $email = $user->user_email;
    }

}

$error_message = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {

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

            $sf_member = get_salesforce_member_data($email);

            store_SF_user_ID($stored_user->ID, $sf_member->Id);

            $user = wp_signon($creds, true);

            if (!is_wp_error($user)) {
                wp_set_current_user($user->ID);

                $verification_url = get_permalink(get_page_by_path('espace-don'));
                wp_redirect($verification_url);
                exit;
            } else {

                $title = "Une erreur est survenue";
                $error_message = "Mauvais email ou mot de passe";
            }


        } else {
            $verification_url = add_query_arg([
                "user" => $email,
            ], get_permalink(get_page_by_path('verifier-votre-email')));

            $title = "Votre email ne semble pas encore vérifié";
            $error_message = "Pour le faire veuillez vous rendre sur la page <a class='aif-link--primary' href='{$verification_url}'> Vérifier mon email</a>.";
        }
    } else {
        $url = get_permalink(get_page_by_path('creer-votre-compte'));

        $title = "L'adresse email renseignée ne trouve pas de correspondance dans notre système";
        $error_message = "Votre compte n'existe pas. Pour le créer, veuillez vous rendre sur la page  <a class='aif-link--primary' href='{$url}> Créer mon compte </a>.";
    }

}
?>




<main class="aif-container--main">

    <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
        <h1 class="aif-mb1w">Mon espace Don</h1>

    </header>
    <div class="aif-container--form">
        <h2> Je me connecte </h2>

        <section>
            <h3 class="aif-sr-only"> Formulaire de connexion </h3>
            <form class="aif-form-container" role="form" method="POST" action="">
            <?php wp_nonce_field('login_form', 'login_nonce'); ?>
                <label for="email">Votre adresse email (obligatoire)</label>
                <input placeholder="adresse@mail.fr"
                    value="<?= $email ? $email : '' ?>"
                    class="aif-input" type="email" name="email" id="email" autocomplete="email" required="true">
                <div class="aif-password-container">
                    <label class="aif-password-container__label" for="password">Votre mot de passe (obligatoire)</label>
                    <div class="aif-password-container__input-wrapper">
                        <input placeholder="Mot de passe" class="aif-password-container__input aif-input"
                            name="password" aria-describedby="passwordHelp passphraseRequirements" type="password"
                            id="password" autocomplete="new-password" required aria-required="true"
                            oninput="checkPassphraseStrength()">
                        <button class="aif-password-container__button" type="button" id="toggle-password"
                            data-target="password" aria-label="Afficher ou masquer le mot de passe">
                            Afficher
                        </button>
                    </div>

                    <?php
        if (!empty($error_message)) {
            aif_include_partial("alert", [
                "state" => "error",
                "title" => $title,
            "content" => $error_message]);

        };

?>

                    <div class="aif-flex aif-justify-end">
                        <div>
                        <a class="aif-link--primary aif-mt1w aif-block"
                                href="<?= get_permalink(get_page_by_path('mot-de-passe-oublie')) ?>">
                                Mot de passe oublié ? </a>
                        <a class="aif-link--primary aif-mt05w aif-block"
                                href="<?= get_permalink(get_page_by_path('foire-aux-questions')) ?>">
                                Un problème de connexion ? </a>
                        </div>
                   

                    </div>

                    <button class="btn aif-mt1w aif-button--full" type="submit">Se connecter</button>

            </form>




        </section>


        <section class="aif-mt1w aif-mb1w">
            <h3>Première connexion ?</h3>

            <p> La création de votre espace don n’est pas automatique lorsque vous faites un don.Si vous n’avez
                jamais
                créé votre espace, merci de cliquer sur “Créer votre compte”. </p>

            <a href="<?=  get_permalink(get_page_by_path('creer-votre-compte')) ?>"
                class="btn aif-button--full btn--white">Créer
                votre compte</a>

        </section>


    </div>

</main>

<?php
get_footer();
?>