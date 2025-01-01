<?php



function humanity_theme_parent_theme_enqueue_styles()
{

    wp_enqueue_style('humanity-theme-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'espace-donateur-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['humanity-theme-style']
    );

}
add_action('wp_enqueue_scripts', 'humanity_theme_parent_theme_enqueue_styles');

function humanity_theme_parent_theme_enqueue_scripts()
{
    wp_enqueue_script('check-password-js', get_stylesheet_directory_uri().'/js/check-password.js');
}

add_action('wp_enqueue_scripts', 'humanity_theme_parent_theme_enqueue_scripts');

function hide_admin_bar_for_limited_users()
{
    if (!current_user_can('administrator')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'hide_admin_bar_for_limited_users');


function aif_donor_space_check_requirements()
{

    if (! current_user_can('manage_options')) {
        return;
    }


    $AIF_SALESFORCE_URL = $_ENV['AIF_SALESFORCE_URL'];
    $AIF_SALESFORCE_CLIENT_ID = $_ENV['AIF_SALESFORCE_CLIENT_ID'];
    $AIF_SALESFORCE_SECRET = $_ENV['AIF_SALESFORCE_SECRET'];
    $AIF_MAILGUN_TOKEN = $_ENV['AIF_MAILGUN_TOKEN'];
    $AIF_MAILGUN_URL = $_ENV['AIF_MAILGUN_URL'];
    $AIF_MAILGUN_DOMAIN = $_ENV['AIF_MAILGUN_DOMAIN'];

    if (empty($AIF_SALESFORCE_URL) || empty($AIF_SALESFORCE_CLIENT_ID) || empty($AIF_SALESFORCE_SECRET) || empty($AIF_MAILGUN_DOMAIN) || empty($AIF_MAILGUN_TOKEN) || empty($AIF_MAILGUN_URL)) {
        echo "<div class='notice notice-error'><p><strong>AIF Donor Space:</strong> Ce plugin nécessite la présence des variables d'environnement AIF_SALESFORCE_URL,AIF_SALESFORCE_CLIENT_ID, AIF_SALESFORCE_SECRET, AIF_MAILGUN_TOKEN, AIF_MAILGUN_URL, AIF_MAILGUN_DOMAIN   </p> <p> Voir le README.md pour plus d'informations </p></div>";
    }
}

add_action('admin_notices', 'aif_donor_space_check_requirements');
function aif_donor_space_get_partial($partial_name)
{
    $partial_path = AIF_DONOR_SPACE_PATH . "templates/partials/{$partial_name}.php";



    if (file_exists($partial_path)) {
        include $partial_path;
    } else {
        echo "<!-- Partial {$partial_name} introuvable -->";
    }
}
