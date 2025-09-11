<?php
/* Template Name: Check email */
get_header();

$success_message = '';



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {

    $email = sanitize_email($_POST['email']);

    if (has_access_to_donation_space($email)) {

        $user = get_user_by('email', $email);

        if ($user) {
            wp_redirect(get_permalink(get_page_by_path('connectez-vous')));
            exit;
        } else {
            wp_redirect(get_permalink(get_page_by_path('connectez-vous')));
            exit;
        }

    } else {
        $error_message = "Vous n'avez pas accès à l'espace don";
    }

}
?>




<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">
    <?php if (isset($error_message)) : ?>
    <div class="aif-error-message"><?php echo $error_message; ?>
    </div>
    <?php endif; ?>
    <div class="container">


        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Qui êtes vous ?</h1>
        </header>

        <p>Nous avons besoin de votre email pour déterminer si vous êtes déja connu</p>

        <form class="aif-form-container" role="form" method="POST" action="">
            <label>Votre adresse email</label>
            <div>
                <input placeholder="adresse@mail.fr" value="" type="email" name="email" required="true">
                <button aria-label="Rechercher" class="btn btn--dark" type="submit">Vérifier votre email</button>
            </div>
        </form>


    </div>

</main>

<?php
get_footer()
?>