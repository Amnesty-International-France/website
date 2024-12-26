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

                $verifaction_url = get_permalink(get_page_by_path('accueil'));
                wp_redirect($verifaction_url);
                exit;
            } else {
                $error_message = "Mauvais email ou mot de passe";
            }


        } else {
            $url = get_permalink(get_page_by_path('verifier-votre-email'));
            $error_message = "Votre email ne semble pas encore vérifié. Pour le faire veuillez vous rendre sur  <a class='aif-text-underline 
aif-text-underline--orange' href='" . $url . "'>" . $url . "</a>.";
        }

    } else {

        $url = get_permalink(get_page_by_path('creer-votre-compte'));
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

        <?php if (!empty($error_message)) : ?>
        <div class="aif-bg-grey--lighter aif-p1w aif-mb1w">

            <div class="aif-flex aif-gap-single">
                <div>
                    <svg width="4" height="14" viewBox="0 0 4 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M2 14C1.45 14 0.979167 13.8477 0.5875 13.5431C0.195833 13.2384 0 12.8722 0 12.4444C0 12.0167 0.195833 11.6505 0.5875 11.3458C0.979167 11.0412 1.45 10.8889 2 10.8889C2.55 10.8889 3.02083 11.0412 3.4125 11.3458C3.80417 11.6505 4 12.0167 4 12.4444C4 12.8722 3.80417 13.2384 3.4125 13.5431C3.02083 13.8477 2.55 14 2 14ZM0 9.33333V0H4V9.33333H0Z"
                            fill="#D51118" />
                    </svg>
                </div>
                <div>
                    <p class="aif-color-red aif-text-bold">Une erreur est survenue</p>

                    <p class="aif-mb0">
                        <?php echo $error_message  ?>
                    </p>

                </div>
            </div>

        </div>

        <?php endif; ?>

        <form role="form" method="POST" action="">
            <?php wp_nonce_field('login_form', 'login_nonce'); ?>
            <label for="email">Votre adresse email (obligatoire)</label>
            <input placeholder="" value="" type="email" name="email" id="email" autocomplete="email" required="true">
            <label for="email">Votre mot de passe (obligatoire)</label>
            <input placeholder="" id="password" value="" type="password" name="password" autocomplete="password"
                required="true">

            <button class="btn aif-mt1w" type="submit">Se connecter</button>

        </form>

        <section class="aif-mt2w">
            <h3>Première connexion</h3>

            <p> La création de votre espace don n’est pas automatique lorsque vous faites un don.Si vous n’avez
                jamais
                créé votre espace, merci de cliquer sur “Créer votre compte”. </p>

            <a href="<?php echo get_permalink(get_page_by_path('creer-votre-compte')) ?>" class="btn">Créer
                votre compte</a>

        </section>


    </div>

</main>

<?php
get_footer();
?>