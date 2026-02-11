<?php

/**
 * Title: Nous contacter Pattern
 * Description: Nous contacter
 * Slug: amnesty/contact-us
 * Inserter: no
 */

if (!headers_sent()) {
    header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

$current_user = wp_get_current_user();
$sf_user_ID = get_SF_user_ID($current_user->ID);

$SF_User = get_salesforce_user_data($sf_user_ID);

$subject = '';

if (isset($_GET['subject'])) {
    $subject = $_GET['subject'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&  isset($_POST['subject']) && isset($_POST['message'])) {
    if (!isset($_POST['contact_nonce']) || !wp_verify_nonce($_POST['contact_nonce'], 'contact_form')) {
        die('Invalid nonce.');
    }

    $message = sanitize_text_field($_POST['message']);
    $subject = sanitize_text_field($_POST['subject']);

    if (create_contact_request($sf_user_ID, $message, $subject, $SF_User->Tech_Lien_Mandat_Actif__c)) {
        $success_message_title = 'Votre demande de contact à bien été prise en compte';
        $url = get_permalink(get_page_by_path('mes-demandes'));
        $success_message = "Vous pouvez voir le suivi du traitement de vos demandes sur la page  <a class='aif-link--secondary' href='{$url}'> Mes demandes. </a>";
    } else {
        $error_message = "Votre demande n'a pas pu aboutir. Veuillez réessayer plus tard.";
    }
}

?>

<!-- wp:pattern {"slug":"amnesty/my-space-sidebar"} /-->
<main class="aif-donor-space-content">
    <!-- wp:pattern {"slug":"amnesty/my-space-header"} /-->
    <section class="aif-container--form">
        <header>
            <h1>Nous contacter</h1>
        </header>

        <?php
        if (!empty($error_message)) {
            $title = 'Une erreur est survenue';
            aif_include_partial('alert', [
                'title' => $title,
                'state' => 'error',
                'content' => $error_message]);

        }

if (!empty($success_message)) {
    aif_include_partial('alert', [
        'title' => $success_message_title,
        'content' => $success_message,
        'state' => 'success']);
}
?>

        <form method="post" action="">
			<?php wp_nonce_field('contact_form', 'contact_nonce'); ?>
            <label for="subject">Objet de la demande (obligatoire)</label>
            <input placeholder="L'objet de votre demande" type="text" name="subject"
                aria-labelledby="contact-subject-help-message"
                value="<?= $subject ?>"
                class="aif-input <?= isset($has_error) && $has_error ? 'aif-input-error' : '' ?>"
                required id="subject" maxlength="255"
            />
            <?php
    $url = get_permalink(get_page_by_path('mes-demandes'));
$content = 'Indiquez le sujet de votre demande. 255 caractères maximum.';
aif_include_partial('info-message', [
    'id' => 'contact-subject-help-message',
    'content' => $content]);
?>

            <label for="message">Message (obligatoire)</label>
            <textarea name="message" aria-labelledby="contact-message-help-message"
                class="aif-input <?= isset($has_error) && $has_error ? 'aif-input-error' : '' ?>"
                required id="message"></textarea>
            <?php
$url = get_permalink(get_page_by_path('mes-demandes'));
$content = 'Décrivez en détail votre demande.';
aif_include_partial('info-message', [
    'id' => 'contact-message-help-message',
    'content' => $content]);
?>
            <button class="btn aif-mt1w aif-button--full" type="submit">Enregistrer</button>
        </form>
    </section>
</main>
