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
                value="<?= esc_attr($subject) ?>"
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
