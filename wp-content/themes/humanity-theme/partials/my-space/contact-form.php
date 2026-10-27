<section class="aif-container--form">
    <header>
        <h1>Nous contacter</h1>
    </header>

    <?php
    if ($error_message) {
        aif_include_partial('alert', [
            'title' => 'Une erreur est survenue',
            'state' => 'error',
            'content' => $error_message,
        ]);
    }
    if ($success_message) {
        aif_include_partial('alert', [
            'title' => $success_message_title,
            'state' => 'success',
            'content' => $success_message,
        ]);
    }
    ?>

    <form method="post" action="">
        <?php wp_nonce_field('contact_form', 'contact_nonce'); ?>
        <input type="hidden" name="aif_donor_contact_form" value="1">
        <label for="subject">Objet de la demande (obligatoire)</label>
        <input placeholder="L'objet de votre demande" type="text" name="subject"
            aria-describedby="contact-subject-help-message"
            aria-invalid="<?= isset($errors['subject']) ? 'true' : 'false' ?>"
            value="<?= esc_attr($subject) ?>"
            class="aif-input <?= isset($errors['subject']) ? 'aif-input-error' : '' ?>"
            required id="subject" maxlength="255"
        />
        <?php
        aif_include_partial('info-message', [
            'id' => 'contact-subject-help-message',
            'content' => $errors['subject'] ?? 'Indiquez le sujet de votre demande. 255 caractères maximum.',
        ]);
    ?>

        <label for="message">Message (obligatoire)</label>
        <textarea name="message" aria-describedby="contact-message-help-message"
            aria-invalid="<?= isset($errors['message']) ? 'true' : 'false' ?>"
            class="aif-input <?= isset($errors['message']) ? 'aif-input-error' : '' ?>"
            required id="message"><?= esc_textarea($message) ?></textarea>
        <?php
    aif_include_partial('info-message', [
        'id' => 'contact-message-help-message',
        'content' => $errors['message'] ?? 'Décrivez en détail votre demande.',
    ]);
    ?>
        <button class="btn aif-mt1w aif-button--full" type="submit">Enregistrer</button>
    </form>
</section>
