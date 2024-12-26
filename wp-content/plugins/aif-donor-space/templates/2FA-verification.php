<?php

/* Template Name: Espace Donateur - 2FA Check */
get_header();

$success_message = "";
$disable_input = false;

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
            reset_login_attempts($user->ID);
            wp_redirect(get_permalink(get_page_by_path('connectez-vous')));
            exit;
        } else {

            $disable_input = false;

            if(limit_login_attempts($user->ID)) {
                $error_message = "Le code ne correspond pas";

            } else {
                $error_message = "La vérification du code à échoué. Veuillez recommencer dans quelques secondes";

            }


        }

    } else {
        $error_message = "Utilisateur inconnu ou code invalide";

    }


}


?>




<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">
    <?php if (!empty($error_message)) : ?>
    <div class="aif-error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>


    <div class="container">

        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Votre email n'a pas encore été vérifier ?</h1>
        </header>

        <p>Vérifier votre email en renseignant le code reçu par mail</p>

        <form role="form" method="POST" action="">
            <?php wp_nonce_field('2FA_check', '2FA_nonce'); ?>
            <label class="<?php echo !empty($error_message) ? 'aif-input-error' : "" ?>" for="email">Votre
                adresse email
                (obligatoire)</label>
            <input class="<?php echo !empty($error_message) ? "aif-input-error" : "" ?>" placeholder="" value=""
                type="email" name="email" id="email" autocomplete="email" required="true">
            <label class="<?php echo !empty($error_message) ? "aif-input-error" : "" ?>" for="2FA-code">Votre
                code de
                vérification (obligatoire)</label>
            <input pattern="\d{6}" title="rentrer ici votre code de 6 chiffres reçu par email"
                class="<?php echo !empty($error_message) ? "aif-input-error" : "" ?>" placeholder="" id=" 2FA-code"
                value="" type="text" name="2FA-code" required="true">

            <button class="btn btn--dark" type="submit">Vérifier le code</button>
        </form>


    </div>

</main>