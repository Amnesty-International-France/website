<?php

/* Template Name: Create account */
get_header();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = sanitize_email($_POST['email']);

    if(has_access_to_donation_space($email)) {

        $first_name = sanitize_text_field($_POST['first-name']);
        $last_name = sanitize_text_field($_POST['last-name']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm-password'];

        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            $error_message = "Tous les champs sont requis.";
        } elseif (!is_email($email)) {
            $error_message = "L'adresse email est invalide.";
        } elseif ($password !== $confirm_password) {
            $error_message = "Les mots de passe ne correspondent pas.";
        } else {
            $userdata = array(
                'user_login'    => $email,
                'user_email'    => $email,
                'user_pass'     => $password,
                'first_name'    => $first_name,
                'last_name'     => $last_name,
                'nickname'      => $first_name . ' ' . $last_name,
                'role'          => 'subscriber');

            $user_id = wp_insert_user($userdata);

            if (!is_wp_error($user_id)) {
                wp_redirect(get_permalink(get_page_by_path('connectez-vous')));
                exit;
            } else {
                $error_message = "Une erreur est survenue";
            }
        }

    }

    $error_message = "Vous ne pouvez pas accéder à l'espace donateur";
}
?>

<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">
    <?php if (isset($error_message)) : ?>
    <div class="aif-error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <div class="container">


        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Créer mon compte</h1>
        </header>

        <p>Votre espace don vous permet de suivre facilement vos dons et adhésion. Vous pouvez y modifier vos
            coordonnées personnelles, votre RIB et éditer des duplicatas de vos reçus fiscaux. Vous n’êtes pas encore
            donateur ? <a href="<?php echo get_permalink(get_page_by_path('nous-soutenir')) ?>">Soutenez nous !</a></p>


        <form action="" method="POST" onsubmit="return checkPasswordMatch()">


            <label for="first-name">Prénom (obligatoire) :</label>
            <input type="text" id="first-name" name="first-name" placeholder="Votre prénom" autocomplete="given-name"
                required>

            <label for="last-name">Nom (obligatoire) :</label>
            <input type="text" id="last-name" name="last-name" placeholder="Votre nom" autocomplete="family-name"
                required>



            <label for="email">Adresse email (obligatoire) :</label>
            <input type="email" id="email" name="email" placeholder="Votre adresse email" autocomplete="email" required>

            <fieldset>
                <legend> Votre mot de passe doit :</legend>



                <ul id="passphraseRequirements">
                    <li id="length">Doit contenir au moins 12 caractères</li>
                    <li id="uppercase">Doit contenir au moins une lettre majuscule</li>
                    <li id="lowercase">Doit contenir au moins une lettre minuscule</li>
                    <li id="number">Doit contenir au moins un chiffre</li>
                    <li id="special">Doit contenir au moins un caractère spécial (!, @, #, $, %,
                        etc.)
                    </li>
                </ul>

                <label for="password">Mot de passe (obligatoire) :</label>
                <input type="password" id="password" name="password" required aria-required="true"
                    placeholder="Votre mot de passe" aria-describedby="passwordHelp" autocomplete="new-password"
                    required aria-required="true" oninput="checkPassphraseStrength()" onpaste="preventCopyPaste(event)>
                <small id=" passwordHelp">
                Exemple de mot de passe valide : <strong>Mon@MotDePasse123</strong> (au moins 12 caractères, une
                majuscule, un chiffre et un caractère spécial)
                </small>


                <div id="password-error-too-weak" class="invalid password-error-message">
                    Le mot de passe est trop faible
                </div>

                <br /> <br />

                <label for="confirm-password">Confirmer le mot de passe (obligatoire) :</label>
                <input type="password" id="confirm-password" required aria-required="true" name="confirm-password"
                    placeholder="Confirmer votre mot de passe" autocomplete="new-password" required
                    oninput="checkPasswordMatch()" onpaste="preventCopyPaste(event)
                    >

                <div id=" password-error-not-match" class="invalid password-error-message">Les mots de passe ne
                correspondent
                pas.
    </div>

    </fieldset>

    <button class="btn btn--dark" type="submit" id="submit-btn">S'inscrire</button>

    </div>

</main>