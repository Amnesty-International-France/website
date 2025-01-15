<?php
/* Template Name: Check email */
get_header();


if (!isset($_GET['ID']) || !isset($_GET['token'])) {
    $error_title = "Une erreur est survenue";
    $error_message = "Nous ne pouvons récupérer l'utilisateur associé à l'identifiant.";

}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && isset($_POST['confirm-password']) && isset($_GET['ID']) && isset($_GET['token'])) {

    $ID = $_GET['ID'];
    $token = $_GET['token'];
    $password = $_POST['password'];

    $stored_token = get_email_token($ID);
    $user = get_user_by("ID", $ID);
    if ($token === $stored_token) {
        if ($user) {
            if ($password == $_POST['confirm-password']) {

                $userdata = [
                    'ID'        => $ID,
                    'user_pass' => $password
                ];
                wp_update_user($userdata);



                $url = add_query_arg([
                    "email" => $user->user_email,
                ], get_permalink(get_page_by_path('espace-don/connectez-vous')));


                wp_redirect($url);

                exit;

            } else {
                $error_title = "Une erreur est survenue.";
                $error_message = "Les mots de passe ne correspondent pas";

            }
        } else {


            $error_title = "Votre utilisateur nous est inconnu";
            $error_message = 'Devenez donateur en <a>réalisant un don </a> ou <a
                          class="aif-text-underline aif-text-underline--orange" href="mailto:smd@amnesty.fr">contacter le Service membres et donateurs</a> si vous pensez que c’est une erreur.';
        }
    } else {

        $error_title = "Une erreur est survenue";
        $error_message = 'Les informations fournies sont erronnées';
    }
}
?>


<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">

    <div class="container">

<?php

        if (isset($error_message)) {
            $error_title = "Une erreur est survenue.";
            aif_include_partial("alert", [
            "title" => $error_title,
            "content" => $error_message]);

        }

?>


        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
        <h1 class="aif-mb1w">Mon espace Don</h1>
    
        </header>
   
<section>

<form class="aif-form-container" action="" method="POST">


<label for="password">Votre nouveau mot de passe (obligatoire) :</label>
<input type="password" id="password" name="password"
    aria-describedby="passwordHelp passphraseRequirements" autocomplete="new-password" required
    aria-required="true" oninput="checkPassphraseStrength()">
<small id="passwordHelp">
    Exemple de mot de passe valide : <strong>Mon@MotDePasse123</strong> (au moins 6 caractères, une
    majuscule, un chiffre et un caractère spécial)
</small>

<div id="password-error-too-weak" class="aif-text-red aif-hide">
    Le mot de passe est trop faible
</div>

<div id="passphraseRequirements">
    <p class=" aif-m0 aif-mt1w ">Votre mot de passe doit </p>
    <ul>
        <li id=" length">Doit contenir au moins 6 caractères</li>
        <li id="uppercase">Doit contenir au moins une lettre majuscule</li>
        <li id="lowercase">Doit contenir au moins une lettre minuscule</li>
        <li id="number">Doit contenir au moins un chiffre</li>
        <li id="special">Doit contenir au moins un caractère spécial (!, @, #, $, %,
            etc.)
        </li>
    </ul>
</div>

<label for="confirm-password">Confirmer le mot de passe (obligatoire) :</label>
<input type="password" id="confirm-password" required aria-required="true" name="confirm-password"
    placeholder="Confirmer votre mot de passe" autocomplete="new-password" required
    oninput="checkPasswordMatch()">

<div id="password-error-not-match" class="aif-text-red aif-hide">Les mots de passe ne
    correspondent
    pas.
</div>

<button class="btn aif-mt1w" type="submit" id="submit-btn">Créer mon compte</button>


</form>

</section>

  

    </div>

</main>

<?php
get_footer()
?>