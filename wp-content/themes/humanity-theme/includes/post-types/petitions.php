<?php

function amnesty_register_petitions_cpt()
{
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
        'not_found_in_trash' => 'Aucune Pétition dans la corbeille',
    ];
    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'petitions'],
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'menu_icon' => 'dashicons-pressthis',
        'show_in_rest' => true,
    ];

    register_post_type('petition', $args);
}
add_action('init', 'amnesty_register_petitions_cpt');

function amnesty_signature_count_permission_check($allowed, $meta_key, $post_id, $user_id, $cap, $caps)
{
    return user_can($user_id, 'edit_post', $post_id);
}

function amnesty_register_petition_signature_count_meta()
{
    register_post_meta('petition', '_amnesty_signature_count', [
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'integer',
        'default'       => 0,
        'sanitize_callback' => 'absint',
        'auth_callback'     => 'amnesty_signature_count_permission_check',
    ]);
}
add_action('init', 'amnesty_register_petition_signature_count_meta');

function amnesty_register_petition_type_meta()
{
    register_post_meta('petition', 'type', [
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => 'amnesty_signature_count_permission_check',
    ]);
}
add_action('init', 'amnesty_register_petition_type_meta');

function amnesty_get_petition_signature_count($post_id)
{
    $count = get_post_meta($post_id, '_amnesty_signature_count', true);
    return absint($count);
}

/**
 * Ajoute les règles de réécriture et le "flag" pour les pétitions "Mon Espace"
 */
function aif_myspace_petition_rewrite_rules()
{
    add_filter('query_vars', function ($vars) {
        $vars[] = 'is_my_space_petition';
        return $vars;
    });

    add_rewrite_rule(
        '^mon-espace/agir-et-se-mobiliser/nos-petitions/([^/]+)/?$',
        'index.php?post_type=petition&name=$matches[1]&is_my_space_petition=1',
        'top'
    );
}
add_action('init', 'aif_myspace_petition_rewrite_rules');

function aif_myspace_petition_template_include($template)
{
    if (get_query_var('is_my_space_petition') && is_singular('petition')) {
        $new_template = get_stylesheet_directory() . '/patterns/single-petition-my-space.php';
        if ('' !== $new_template) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'aif_myspace_petition_template_include', 99);

function amnesty_handle_petition_signature()
{
    if (isset($_POST['sign_petition']) && isset($_POST['user_email']) && isset($_POST['petition_id'])) {
        $petition_id = absint($_POST['petition_id']);
        $user_email = sanitize_email($_POST['user_email']);

        $type = get_field('type', $petition_id)['value'];
        $current_date = date('Y-m-d');
        $end_date = get_field('date_de_fin', $petition_id);

        if (isset($end_date) && (strtotime($end_date) < strtotime($current_date))) {
            exit;
        }

        if (! is_email($user_email) || ! $petition_id) {
            wp_redirect(add_query_arg('signature_status', 'invalid', wp_get_referer()));
            exit;
        }

        $local_user = get_local_user($user_email);

        if ($local_user !== false) {
            $user_id = $local_user->id;
            if (have_signed($petition_id, $user_id)) {
                $petition_permalink = get_permalink($petition_id);
                $redirect_url = trailingslashit($petition_permalink) . 'merci/?alreadysigned';
                wp_redirect($redirect_url);
                exit;
            }
        } else {
            $civility = $_POST[ 'civility' ];
            $firstname = $_POST[ 'user_firstname' ];
            $lastname = $_POST[ 'user_lastname' ];
            $postal_code = $_POST[ 'user_zipcode' ];
            $country = $_POST[ 'user_country' ];
            $phone = $_POST[ 'user_phone' ];

            $user_id = insert_user($civility, $firstname, $lastname, $user_email, $country, $postal_code, $phone);

            if ($user_id === false) {
                wp_redirect(add_query_arg('signature_status', 'error', wp_get_referer()));
                exit;
            }
        }

        $code_origine = isset($_POST['code_origine']) && ! empty($_POST['code_origine']) ? $_POST['code_origine'] : get_field('code_origine', $petition_id) ?? '';
        $message = $type === 'action-soutien' && isset($_POST['user_message']) && ! empty($_POST['user_message']) ? sanitize_textarea_field($_POST['user_message']) : '';

        if (insert_petition_signature($petition_id, $user_id, date('Y-m-d'), $code_origine, $message) === false) {
            wp_redirect(add_query_arg('signature_status', 'error', wp_get_referer()));
            exit;
        }

        $current_signatures = amnesty_get_petition_signature_count($petition_id);
        $new_signatures = $current_signatures + 1;
        update_post_meta($petition_id, '_amnesty_signature_count', $new_signatures);

        $gtm_type = 'petition';
        $gtm_name = get_the_title($petition_id);

        $petition_permalink = trailingslashit(get_permalink($petition_id)) . 'merci/';
        $redirect_url = add_query_arg([
            'gtm_type' => $gtm_type,
            'gtm_name' => urlencode($gtm_name),
        ], $petition_permalink);

        wp_redirect($redirect_url);
        exit;
    }
}
add_action('template_redirect', 'amnesty_handle_petition_signature');

function filter_petition_archive(WP_Query $query)
{
    if (! $query->is_main_query() || ! is_post_type_archive('petition')) {
        return;
    }

    $meta_query_args = [
        [
            'key' => 'date_de_fin',
            'value' => date('Y-m-d'),
            'compare' => '>=',
            'type' => 'DATE',
        ],
    ];
    $query->set('meta_query', $meta_query_args);


    $query->set('meta_key', 'date_de_fin');
    $query->set('meta_type', 'DATE');
    $query->set('orderby', 'meta_value');
    $query->set('order', 'ASC');
}

if (!is_admin()) {
    add_action('pre_get_posts', 'filter_petition_archive');
}

add_filter('manage_petition_posts_columns', function ($columns) {
    $columns['type_petition'] = 'Type de pétition';
    return $columns;
});

add_action('manage_petition_posts_custom_column', function ($column, $post_id) {
    if ($column === 'type_petition') {
        echo get_field('field_685aca87362cb', $post_id)['label'];
    }
}, 10, 2);

add_action('restrict_manage_posts', function ($post_type) {
    if ($post_type !== 'petition') {
        return;
    }
    $selected = $_GET['type_petition'] ?? '';
    ?>
	<select name="type_petition">
		<option value="">Toutes les pétitions</option>
		<option value="petition" <?php selected($selected, 'petition'); ?>>Pétition</option>
		<option value="action-soutien" <?php selected($selected, 'action-soutie'); ?>>Action de soutien</option>
	</select>
	<?php
});

if (is_admin()) {
    add_action('pre_get_posts', function ($query) {
        global $pagenow;

        if (
            $pagenow !== 'edit.php' ||
            !$query->is_main_query() ||
            $query->get('post_type') !== 'petition'
        ) {
            return;
        }

        $petitionTypeFilter = $_GET['type_petition'] ?? '';
        if ($petitionTypeFilter !== '') {
            $query->set('meta_query', [
                [
                    'key'   => 'type',
                    'value' => $petitionTypeFilter,
                ],
            ]);
        }
    });
}

add_action('acf/include_fields', function () {
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_685aca878b4d7',
        'title' => 'Attributs Pétition',
        'fields' => [
            [
                'key' => 'field_685aca87362cb',
                'label' => 'Type',
                'name' => 'type',
                'aria-label' => '',
                'type' => 'select',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'choices' => [
                    'petition' => 'Pétition',
                    'action-soutien' => 'Action de soutien',
                ],
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
            ],
            [
                'key' => 'field_685acdfe73c83',
                'label' => 'ID SF',
                'name' => 'uidsf',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_685acdfe73c84',
                'label' => 'Code origine',
                'name' => 'code_origine',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_685ace6573c85',
                'label' => 'Date de fin',
                'name' => 'date_de_fin',
                'aria-label' => '',
                'type' => 'date_picker',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'display_format' => 'd/m/Y',
                'return_format' => 'd.m.Y',
                'first_day' => 1,
                'allow_in_bindings' => 0,
            ],
            [
                'key' => 'field_685acd6d73c81',
                'label' => 'Objectif signatures',
                'name' => 'objectif_signatures',
                'aria-label' => '',
                'type' => 'number',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'min' => 1,
                'max' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'step' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_685acdfe73c82',
                'label' => 'Destinataire',
                'name' => 'destinataire',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'petition',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_685ace1673c83',
                'label' => 'PDF pétition',
                'name' => 'pdf_petition',
                'aria-label' => '',
                'type' => 'file',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'petition',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'return_format' => 'id',
                'library' => 'all',
                'min_size' => '',
                'max_size' => '',
                'mime_types' => 'pdf',
                'allow_in_bindings' => 0,
            ],
            [
                'key' => 'field_685ace4c73c84',
                'label' => 'Punchline',
                'name' => 'punchline',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'petition',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_68f4b1a4a9c10',
                'label' => 'Sous-titre CLH',
                'name' => 'subtitle_clh',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_685acdfe73c86',
                'label' => 'Lettre',
                'name' => 'lettre',
                'aria-label' => '',
                'type' => 'wysiwyg',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'petition',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'allow_in_bindings' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 0,
                'delay' => 0,
            ],
            [
                'key' => 'field_685acf1b73c86',
                'label' => 'Autoriser message utilisateur',
                'name' => 'autoriser_message_utilisateur',
                'aria-label' => '',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'action-soutien',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 1,
                'allow_in_bindings' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ],
            [
                'key' => 'field_6867cd3430784',
                'label' => 'Téléphone requis',
                'name' => 'phone_required',
                'aria-label' => '',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'action-soutien',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 0,
                'allow_in_bindings' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ],
            [
                'key' => 'field_6867ccdb30782',
                'label' => 'Phrase Formulaire',
                'name' => 'form_contenu',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'action-soutien',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_6867cd7130785',
                'label' => 'Texte du bouton',
                'name' => 'button_text',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'action-soutien',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_6867cd2430783',
                'label' => 'Longueur max commentaire',
                'name' => 'comment_max_length',
                'aria-label' => '',
                'type' => 'number',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'action-soutien',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => 1000,
                'min' => 0,
                'max' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'step' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_6867cd7c30786',
                'label' => 'Terms',
                'name' => 'terms',
                'aria-label' => '',
                'type' => 'wysiwyg',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_685aca87362cb',
                            'operator' => '==contains',
                            'value' => 'action-soutien',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'allow_in_bindings' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 0,
                'delay' => 0,
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'petition',
                ],
            ],
        ],
        'menu_order' => 0,
        'position' => 'acf_after_title',
        'style' => 'default',
        'label_placement' => 'left',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 1,
    ]);
});
