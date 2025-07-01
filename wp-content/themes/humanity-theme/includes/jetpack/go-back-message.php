<?php
/**
 * Modifies the "Go back" text in the Jetpack contact form.
 *
 * @param string $success_message The original HTML success message.
 * @return string The modified HTML success message.
 */
function custom_jetpack_contact_form_success_message( $success_message ) {
    $original_text = 'Go back';
    $new_text = 'Retour au formulaire';

    $modified_message = str_replace( esc_html( $original_text ), esc_html( $new_text ), $success_message );

    return $modified_message;
}
add_filter( 'grunion_contact_form_success_message', 'custom_jetpack_contact_form_success_message' );
