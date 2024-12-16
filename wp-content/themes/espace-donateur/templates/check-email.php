<?php
/* Template Name: Check email */
get_header();

$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {

    $data = get_salesforce_user_data($_POST['email']);

    if ($data->hasEspaceDon) {

        wp_redirect(get_permalink(get_page_by_path('creer-un-compte')));
        exit;
    } else {
        $error_message = "Vous n'avez pas accès à l'espace don";
    }

}
?>




<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">
    <?php if (isset($error_message)) : ?>
    <div class="error-message"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <div class="container">


        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Qui êtes vous ?</h1>
        </header>

        <p>Nous avons besoin de votre email pour déterminer si vous avez êtes déja connu</p>

        <form role="form" method="POST" action="">
            <label>Votre adresse email</label>
            <div>
                <input placeholder="" value="" type="email" name="email" required="true">
                <button aria-label="Rechercher" class="btn btn--dark" type="submit">Vérifier votre email</button>
            </div>
        </form>


    </div>

</main>