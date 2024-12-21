<?php

/* Template Name: Espace Donateur - 2FA Check */
get_header();



$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['2FA-code']) && isset($_POST['2FA_nonce'])) {

    $email = sanitize_text_field($_POST['email']);
    $code = $_POST['2FA-code'] ;

    if (!isset($_POST['2FA_nonce']) || !wp_verify_nonce($_POST['2FA_nonce'], '2FA_check')) {
        die('Invalid nonce.');
    }

    $user = get_user_by('email', $email);

    if($user) {

        $stored_code = get_2fa_code($user->ID);

        if($stored_code &&  $stored_code === $code) {

            store_email_is_verified($user->ID);
            wp_redirect(get_permalink(get_page_by_path('connectez-vous')));
            exit;
        }

    }
    $error_message = "Utilisateur inconnu ou code invalide";

}


?>




<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">
    <?php if (isset($error_message)) : ?>
    <div class="aif-error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>


    <div class="container">

        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Vérification votre email</h1>
        </header>

        <p>Vérifié votre email en renseignant le code reçu par mail</p>

        <form role="form" method="POST" action="">
            <?php wp_nonce_field('2FA_check', '2FA_nonce'); ?>
            <label for="email">Votre adresse email (obligatoire)</label>
            <input placeholder="" value="" type="email" name="email" id="email" autocomplete="email" required="true">
            <label for="2FA-code">Votre code de vérification (obligatoire)</label>
            <input placeholder="" id="2FA-code" value="" type="text" name="2FA-code" required="true">

            <button class="btn btn--dark" type="submit">Vérifier le code</button>
        </form>


    </div>

</main>