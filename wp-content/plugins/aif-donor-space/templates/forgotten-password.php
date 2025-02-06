<?php
get_header();

$disable_button = false;



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = sanitize_email($_POST['email']);
    $user = get_user_by("email", $email);


    if ($user) {
        $token = aif_generate_random_hash();
        store_email_token($user->ID, $token);

        $url = add_query_arg([
            "user" =>  $email,
            "token" => $token
        ], get_permalink(get_page_by_path('espace-don/modifier-mon-mot-de-passe')));

        $htmlMessage = "Pour réinitialiser votre mot de passe, veuillez vous rendre sur <a href='".$url."'>". $url  ."</a> ";
        $textMessae = "Pour réinitialiser votre mot de passe, veuillez vous rendre sur" . $url;
        send_reset_password_email($user->user_email, $htmlMessage, $textMessae);

        $success_title = "Votre demande à bien été prise en compte";
        $success_message = "Si votre adresse est reconnue vous allez recevoir un email pour pouvoir réinitialiser votre mot de passe";


    } else {

        $url = get_permalink(get_page_by_path('espace-don/creer-votre-compte'));
        $error_title = "Votre utilisateur nous est inconnu";
        $error_message = "Vous pouvez créer votre compte en allant sur <a class='aif-link--primary' href='{$url}'> Créer mon compte </a>";
    }
}

?>


<main class="aif-container--main">


<header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="aif-mb1w">Mon espace Don</h1>
        </header>
<div class="aif-container--form">
<h2> Mot de passe oublié </h2>
        <?php

        if (isset($error_message)) {
            aif_include_partial("alert", [
            "state" => "error",
            "title" => $error_title,
            "content" => $error_message]);

        }

if (isset($success_message)) {

    aif_include_partial("alert", [
    "state" => "success",
    "title" => $success_title,
    "content" => $success_message,
        ]);

}

?>



        <section>
            <form class="aif-form-container" action="" method="POST">
                <label for="email">Votre email (obligatoire) :</label>
                <input placeholder="adresse@mail.fr" type="email" class="aif-input" id="email" name="email" required aria-required="true">
                <button class="btn aif-mt1w aif-button--full" type="submit" id="submit-btn">Réinitialiser mon mot de passe</button>

            </form>
        </section>



    </div>

</main>

<?php
get_footer()
?>