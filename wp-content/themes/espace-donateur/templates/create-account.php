<?php

/* Template Name: Create account */
get_header();

print_r(wp_script_is('check-password', 'registered'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    print_r($first_name);

    // Récupération des données du formulaire
    $first_name = sanitize_text_field($_POST['first-name']);
    $last_name = sanitize_text_field($_POST['last-name']);
    $civility = sanitize_text_field($_POST['civility']);
    $email = sanitize_email($_POST['email']);
    $address = sanitize_text_field($_POST['civility']);
    $postal_code = sanitize_text_field($_POST['postal-code']);
    $city = sanitize_text_field($_POST['city']);

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];




    if (empty($first_name) || empty($last_name) || empty($civility) || empty($email) ||  empty($address) || empty($postal_code) || empty($city) ||  empty($address) || empty($password) || empty($password)) {
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
            'nickname'      => $first_name . ' ' . $last_name, // Optionnel
            'role'          => 'subscriber', // Par défaut, un abonné
        );

        // Insérer l'utilisateur dans WordPress
        // $user_id = wp_insert_user($userdata);

        // Vérification si l'utilisateur a bien été ajouté
        if (!is_wp_error($user_id)) {
            echo "L'utilisateur a été créé avec succès.";
        } else {
            $error_message = "Une erreur est survenue";
        }
    }
}
?>

<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">
    <?php if (isset($error_message)) : ?>
    <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <div class="container">


        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Créer votre compte</h1>
        </header>

        <p>Il semble que vous n'ayez pas encore de compte donateur. Remplissez le formulaire pour en créer un</p>


        <form action="" method="POST" onsubmit="return checkPasswordMatch()">

            <fieldset>
                <legend>Choissiez votre civilité (obligatoire) : </legend>

                <input type="radio" id="mr" name="civility" value="mr" required>
                <label for="mr">Monsieur</label>
                <input type="radio" id="mrs" name="civility" value="mrs">
                <label for="mrs">Madame</label>
                <input type="radio" id="other" name="civility" value="other">
                <label for="other">Autre</label>
            </fieldset>


            <label for="last-name">Nom (obligatoire) :</label>
            <input type="text" id="last-name" name="last-name" placeholder="Votre nom" autocomplete="family-name"
                required>

            <label for="first-name">Prénom (obligatoire) :</label>
            <input type="text" id="first-name" name="first-name" placeholder="Votre prénom" autocomplete="given-name"
                required>


            <label for="email">Adresse email (obligatoire) :</label>
            <input type="email" id="email" name="email" placeholder="Votre adresse email" autocomplete="email" required>

            <fieldset>
                <legend>Adresse</legend>

                <label for="address">Adresse (obligatoire) :</label>
                <input type="text" id="address" name="address" placeholder="Votre adresse" autocomplete="street-address"
                    required>


                <label for="postal-code">Code Postal (obligatoire) :</label>
                <input type="text" id="postal-code" name="postal-code" placeholder="Votre code postal"
                    autocomplete="postal-code" required>

                <label for="city">Ville :</label>
                <input type="text" id="city" name="city" placeholder="Votre ville" autocomplete="address-level2"
                    required>
            </fieldset>


            <label for="password">Mot de passe (obligatoire) :</label>
            <input type="password" id="password" name="password" placeholder="Votre mot de passe"
                autocomplete="new-password" required onpaste="preventCopyPaste(event)"><br><br>


            <label for="confirm-password">Confirmer le mot de passe (obligatoire) :</label>
            <input type="password" id="confirm-password" name="confirm-password"
                placeholder="Confirmer votre mot de passe" autocomplete="new-password" required
                onpaste="preventCopyPaste(event)" oninput="checkPasswordMatch()"><br><br>


            <div id="password-error" style="color: red; display: none;">Les mots de passe ne correspondent pas.</div>


            <button class="btn btn--dark" type="submit" id="submit-btn">S'inscrire</button>

    </div>

</main>