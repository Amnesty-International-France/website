<?php

$disable_button = false;
$display_form = true;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_email($_POST['email']);
    $user = get_user_by('email', $email);

    if (!isset($_POST['forgotten_password_nonce']) || !wp_verify_nonce($_POST['forgotten_password_nonce'], 'forgotten_password_form')) {
        die('Invalid nonce.');
    }

    if ($user) {
        $token = aif_generate_random_hash();
        store_email_token($user->ID, $token);

        $url = add_query_arg([
            'user' =>  $email,
            'token' => $token,
        ], get_permalink(get_page_by_path('modifier-mon-mot-de-passe')));

        send_reset_password_email($user->user_email, $url);

        $success_title = 'Votre demande à bien été prise en compte';
        $success_message = 'Si votre adresse est reconnue vous allez recevoir un email pour pouvoir réinitialiser votre mot de passe';
        $display_form = false;
    } else {
        $url = get_permalink(get_page_by_path('creer-votre-compte'));
        $error_title = 'Votre utilisateur nous est inconnu';
        $error_message = "Vous pouvez créer votre compte en allant sur <a class='aif-link--primary' href='{$url}'> Créer mon compte </a>";
    }
}

?>

<?php get_header(); ?>

<main class="aif-container--main">
    <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
        <h1 class="aif-mb1w">Mon espace</h1>
    </header>
    <div class="aif-container--form">
        <h2>Mot de passe oublié</h2>
        <?php
        if (isset($error_message)) {
            aif_include_partial('alert', [
                'state' => 'error',
                'title' => $error_title,
                'content' => $error_message]);
        }

if (isset($success_message)) {
    aif_include_partial('alert', [
        'state' => 'success',
        'title' => $success_title,
        'content' => $success_message,
    ]);
}
?>

        <?php if ($display_form) : ?>

        <section class="aif-forgotten-password">
            <form class="aif-form-container" action="" method="POST">
                <?php wp_nonce_field('forgotten_password_form', 'forgotten_password_nonce'); ?>
                <label for="email">Votre email (obligatoire) :</label>
                <input
                    value="<?= $email ? $email : '' ?>"
                    placeholder="adresse@mail.fr" type="email" class="aif-input" id="email" name="email" required
                    aria-required="true">
                <button class='custom-button-block center' type="submit" id="submit-btn">
                    <div class="custom-button">
                        <div class='content bg-yellow medium'>
                            <div class="button-label">Réinitialiser mon mot de passe</div>
                        </div>
                    </div>
                </button>
            </form>
        </section>
        <?php endif ?>
    </div>
</main>

<?php get_footer(); ?>
