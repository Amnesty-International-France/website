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

    <form method="post" action="" data-aif-single-submit data-aif-submit-status="Envoi de votre demande en cours, veuillez patienter.">
        <?php wp_nonce_field('contact_form', 'contact_nonce'); ?>
        <input type="hidden" name="aif_donor_contact_form" value="1">
        <input type="hidden" name="submission_token" value="<?= esc_attr($submission_token) ?>">
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
        <div class="custom-button-block center">
            <button type="submit" class="custom-button" data-aif-loading-label="Envoi en cours…">
                <span class="content bg-yellow medium">
                    <span class="icon-container">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                    <span class="button-label" data-aif-button-label>Envoyer</span>
                </span>
            </button>
        </div>
        <p class="screen-reader-text" role="status" data-aif-submit-status></p>
    </form>
</section>
