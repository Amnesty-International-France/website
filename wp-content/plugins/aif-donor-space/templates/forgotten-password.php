<?php
get_header();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {

    $email = sanitize_email($_POST['email']);
    $user = get_user_by("email", $email);


    if ($user) {
        $token = aif_generate_random_hash();
        store_email_token($user->ID, $token);

        $url = add_query_arg([
            "ID" => $user->ID,
            "token" => $token
        ], get_permalink(get_page_by_path('espace-don/modifier-mon-mot-de-passe')));

        $emailBody = "Pour réinitialiser votre mot de passe, veuillez vous rendre sur " . $url;
        send_reset_password_email($user->user_email, $emailBody);

        $success_title = "Un email vient de vous être envoyé";
        $success_message = "Veuillez consulter votre boîte email pour réinitialiser votre mot de passe";


    } else {

        $url = get_permalink(get_page_by_path('espace-don/creer-votre-compte'));
        $error_title = "Votre utilisateur nous est inconnu";
        $error_message = 'Vous pouvez créer votre compte en allant sur ' .'<a class="aif-underline aif-aif-text-underline--orange" href="'. $url.
        '"> Créer mon compte </a>';
    }
}

?>


<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">
    <div class="container">
        <?php

        if (isset($error_message)) {
            aif_include_partial("alert", [
            "title" => $error_title,
            "content" => $error_message]);

        }

if (isset($success_message)) {

    aif_include_partial("alert", [
    "title" => $success_title,
    "content" => $success_message,
    "success" => true,
        ]);

}

?>


        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="aif-mb1w">Mon espace Don</h1>
        </header>

        <section>
            <form class="aif-form-container" action="" method="POST">
                <label for="email">Votre email (obligatoire) :</label>
                <input type="email" id="email" name="email" required aria-required="true">
                <button class="btn aif-mt1w" type="submit" id="submit-btn">Réinitialiser mon mot de passe</button>

            </form>
        </section>



    </div>

</main>

<?php
get_footer()
?>