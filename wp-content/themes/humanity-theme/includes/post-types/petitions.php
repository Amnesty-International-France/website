<?php

function amnesty_register_petitions_cpt() {
    $labels = [
        'name' => 'Pétitions',
        'singular_name' => 'Pétition',
        'add_new' => 'Ajouter une Pétition',
        'add_new_item' => 'Ajouter une nouvelle Pétition',
        'edit_item' => 'Modifier la Pétition',
        'new_item' => 'Nouvelle Pétition',
        'view_item' => 'Voir la Pétition',
        'search_items' => 'Rechercher une Pétition',
        'not_found' => 'Aucune Pétition trouvée',
        'not_found_in_trash' => 'Aucune Pétition dans la corbeille'
    ];
    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'petitions'),
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'menu_icon' => 'dashicons-admin-page',
        'show_in_rest' => true,
    ];

    register_post_type( 'petition', $args);
}
add_action( 'init', 'amnesty_register_petitions_cpt' );

function amnesty_register_petition_signature_count_meta() {
    register_post_meta( 'petition', '_amnesty_signature_count', array(
        'show_in_rest'  => false,
        'single'        => true,
        'type'          => 'integer',
        'default'       => 0,
        'auth_callback' => 'amnesty_signature_count_auth_callback',
        'sanitize_callback' => 'absint',
    ) );
}
add_action( 'init', 'amnesty_register_petition_signature_count_meta' );

function amnesty_signature_count_auth_callback( $allowed, $meta_key, $post_id, $user_id, $cap, $scm_cap ) {
    return current_user_can( 'manage_options' );
}

function amnesty_get_petition_signature_count( $post_id ) {
    $count = get_post_meta( $post_id, '_amnesty_signature_count', true );
    return absint( $count );
}

function amnesty_handle_petition_signature() {
    if ( isset( $_POST['sign_petition'] ) && isset( $_POST['user_email'] ) && isset( $_POST['petition_id'] ) ) {

        if ( ! isset( $_POST['amnesty_petition_nonce'] ) || ! wp_verify_nonce( $_POST['amnesty_petition_nonce'], 'amnesty_sign_petition' ) ) {
            wp_die( 'Security check failed. Please try again.' );
        }

        $petition_id = absint( $_POST['petition_id'] );
        $user_email = sanitize_email( $_POST['user_email'] );

        if ( ! is_email( $user_email ) || ! $petition_id ) {
            wp_redirect( add_query_arg( 'signature_status', 'invalid', wp_get_referer() ) );
            exit;
        }

        $current_signatures = amnesty_get_petition_signature_count( $petition_id );
        $new_signatures = $current_signatures + 1;
        $updated = update_post_meta( $petition_id, '_amnesty_signature_count', $new_signatures );

        if ( $updated ) {
            $petition_permalink = get_permalink( $petition_id );

            $redirect_url = trailingslashit( $petition_permalink ) . 'merci';

            wp_redirect( $redirect_url );
            exit;
        } else {
            wp_redirect( add_query_arg( 'signature_status', 'error', wp_get_referer() ) );
            exit;
        }
    }
}
add_action( 'template_redirect', 'amnesty_handle_petition_signature' );


add_action( 'acf/include_fields', function() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

	acf_add_local_field_group( array(
		'key' => 'group_685aca878b4d7',
		'title' => 'Attributs Pétition',
		'fields' => array(
			array(
				'key' => 'field_685aca87362cb',
				'label' => 'Type',
				'name' => 'type',
				'aria-label' => '',
				'type' => 'select',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array(
					'petition' => 'Pétition',
					'action-soutien' => 'Action de soutien',
				),
				'default_value' => 'petition',
				'return_format' => 'array',
				'multiple' => 0,
				'allow_null' => 0,
				'allow_in_bindings' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
				'create_options' => 0,
				'save_options' => 0,
			),
			array(
				'key' => 'field_685acdfe73c83',
				'label' => 'ID SF',
				'name' => 'uidsf',
				'aria-label' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'maxlength' => '',
				'allow_in_bindings' => 0,
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array(
				'key' => 'field_685acdfe73c84',
				'label' => 'Code origine',
				'name' => 'code_origine',
				'aria-label' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'maxlength' => '',
				'allow_in_bindings' => 0,
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array(
				'key' => 'field_685ace6573c85',
				'label' => 'Date de fin',
				'name' => 'date_de_fin',
				'aria-label' => '',
				'type' => 'date_picker',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'display_format' => 'd/m/Y',
				'return_format' => 'd.m.Y',
				'first_day' => 1,
				'allow_in_bindings' => 0,
			),
			array(
				'key' => 'field_685acd6d73c81',
				'label' => 'Objectif signatures',
				'name' => 'objectif_signatures',
				'aria-label' => '',
				'type' => 'number',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'min' => 1,
				'max' => '',
				'allow_in_bindings' => 0,
				'placeholder' => '',
				'step' => '',
				'prepend' => '',
				'append' => '',
			),
			array(
				'key' => 'field_685acdfe73c82',
				'label' => 'Destinataire',
				'name' => 'destinataire',
				'aria-label' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'maxlength' => '',
				'allow_in_bindings' => 0,
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array(
				'key' => 'field_685ace1673c83',
				'label' => 'PDF pétition',
				'name' => 'pdf_petition',
				'aria-label' => '',
				'type' => 'file',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_685aca87362cb',
							'operator' => '==contains',
							'value' => 'petition',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'id',
				'library' => 'all',
				'min_size' => '',
				'max_size' => '',
				'mime_types' => 'pdf',
				'allow_in_bindings' => 0,
			),
			array(
				'key' => 'field_685ace4c73c84',
				'label' => 'Punchline',
				'name' => 'punchline',
				'aria-label' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_685aca87362cb',
							'operator' => '==contains',
							'value' => 'petition',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'maxlength' => '',
				'allow_in_bindings' => 0,
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array(
				'key' => 'field_685acdfe73c86',
				'label' => 'Lettre',
				'name' => 'lettre',
				'aria-label' => '',
				'type' => 'wysiwyg',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_685aca87362cb',
							'operator' => '==contains',
							'value' => 'petition',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'allow_in_bindings' => 0,
				'tabs' => 'all',
				'toolbar' => 'full',
				'media_upload' => 0,
				'delay' => 0,
			),
			array(
				'key' => 'field_685acf1b73c86',
				'label' => 'Autoriser message utilisateur',
				'name' => 'autoriser_message_utilisateur',
				'aria-label' => '',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_685aca87362cb',
							'operator' => '==contains',
							'value' => 'action-soutien',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 1,
				'allow_in_bindings' => 0,
				'ui' => 0,
				'ui_on_text' => '',
				'ui_off_text' => '',
			),
			array(
				'key' => 'field_6867cd3430784',
				'label' => 'Téléphone requis',
				'name' => 'phone_required',
				'aria-label' => '',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_685aca87362cb',
							'operator' => '==contains',
							'value' => 'action-soutien',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
				'allow_in_bindings' => 0,
				'ui' => 0,
				'ui_on_text' => '',
				'ui_off_text' => '',
			),
			array(
				'key' => 'field_6867ccdb30782',
				'label' => 'Phrase Formulaire',
				'name' => 'form_contenu',
				'aria-label' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_685aca87362cb',
							'operator' => '==contains',
							'value' => 'action-soutien',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'maxlength' => '',
				'allow_in_bindings' => 0,
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array(
				'key' => 'field_6867cd7130785',
				'label' => 'Texte du bouton',
				'name' => 'button_text',
				'aria-label' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_685aca87362cb',
							'operator' => '==contains',
							'value' => 'action-soutien',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'maxlength' => '',
				'allow_in_bindings' => 0,
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array(
				'key' => 'field_6867cd2430783',
				'label' => 'Longueur max commentaire',
				'name' => 'comment_max_length',
				'aria-label' => '',
				'type' => 'number',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_685aca87362cb',
							'operator' => '==contains',
							'value' => 'action-soutien',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'min' => '',
				'max' => '',
				'allow_in_bindings' => 0,
				'placeholder' => '',
				'step' => '',
				'prepend' => '',
				'append' => '',
			),
			array(
				'key' => 'field_6867cd7c30786',
				'label' => 'Terms',
				'name' => 'terms',
				'aria-label' => '',
				'type' => 'wysiwyg',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_685aca87362cb',
							'operator' => '==contains',
							'value' => 'action-soutien',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'allow_in_bindings' => 0,
				'tabs' => 'all',
				'toolbar' => 'full',
				'media_upload' => 0,
				'delay' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'petition',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'acf_after_title',
		'style' => 'default',
		'label_placement' => 'left',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
		'show_in_rest' => 0,
	) );
} );
