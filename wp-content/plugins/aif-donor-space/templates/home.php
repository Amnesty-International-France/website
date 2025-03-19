<?php

/* Template Name: Espace Donateur - Home */
get_header();

check_user_page_access();

$current_user = wp_get_current_user();
$sf_member = get_salesforce_member_data($current_user->user_email);
$sf_user = get_salesforce_user_data($sf_member->Id);
$user_status =  aif_get_user_status($sf_member);
$picture_url = plugin_dir_url(__DIR__). "assets/pictures/foo.png"


?>


<main class="aif-container--main">
    <div class="aif-container--form">
        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="aif-mb1w">Bonjour
                <?= $current_user->first_name ?>,
            </h1>
            <?php
        aif_include_partial("label", [
            "content" => "Votre statut : {$user_status} (n° {$sf_user->Identifiant_contact__c}) ",
            "variant" => "warning"
            ]);
?>

        </header>

        <p>
            Bienvenue dans votre espace don qui permet la gestion administrative des informations liées à vos dons et
            adhésion.
        </p>

        <nav class="secondary-nav-container" aria-label="menu de navigation secondaire">
            <ul class="secondary-nav-container__list">
                <li class="secondary-nav-container__list__item">

                    <?php
        aif_include_partial("nav-card", [
        "iconName" => "my-info",
        "url" => get_permalink(get_page_by_path('mes-informations-personelles')),
        "title" => "Mes informations",
        "content" => "Affichez ou modifiez vos informations personnelles."]); ?>

                </li>

                <li class="secondary-nav-container__list__item">

                    <?php
       aif_include_partial("nav-card", [
       "iconName" => "paper",
       "url" => get_permalink(get_page_by_path('mes-recus-fiscaux')),
       "title" => "Mes reçus fiscaux",
       "content" => "Retrouvez dans cet espace tous vos reçus fiscaux."]); ?>

                </li>


                <li class="secondary-nav-container__list__item">

                    <?php
       aif_include_partial("nav-card", [
       "iconName" => "plane",
       "url" => get_permalink(get_page_by_path('mes-demandes')),
       "title" => "Mes demandes",
       "content" => "Affichez l’état de vos demandes passées ou en cours.",

    ]);


?>

                </li>
            </ul>
        </nav>

        <?php
       aif_include_partial("aif-banner", [
       "pictureURL" => $picture_url,
       "firstName" => $current_user->first_name,
       "member" => $sf_member,

    ]);


?>



    </div>
</main>

<?php
get_footer();
?>