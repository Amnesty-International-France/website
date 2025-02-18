<?php

/* Template Name: Espace Donateur - Home */
get_header();

check_user_page_access();

$current_user = wp_get_current_user();
$sf_user_ID = get_SF_user_ID($current_user->ID);


$demands =  get_salesforce_user_demands($sf_user_ID);

function aif_format_date($date)
{
    $formatted_date = date_format(date_create($date), "d/m/Y à H:m");
    return "Le {$formatted_date}" ;

}


?>



<main class="aif-container--main">

    <section class="aif-container--form">
        <header>
            <h1>Mes demandes</h1>
        </header>

        <?php     foreach ($demands as $demand): ?>

        <div class="aif-my-demand-container">
            <div class="aif-my-demand-container__title-container">
                <p class="aif-my-demand-container__title-container__date">
                    <?= aif_format_date($demand->Date_de_la_demande__c) ?>
                </p>
                <p class="aif-my-demand-container__title-container__status <?= $demand->Statut_Espace_Don__c == "Rejetée" ? "aif-my-demand-container__title-container__status--refused" : "" ?>  <?= $demand->Statut_Espace_Don__c == "En cours" ? "aif-my-demand-container__title-container__status--in-progress" : "" ?> <?= $demand->Statut_Espace_Don__c == "Fermé - Traité" ? "aif-my-demand-container__title-container__status--success" : "" ?>">
                    <?= $demand->Statut_Espace_Don__c ?>
                </p>

            </div>

            <div class="aif-my-demand-container__info-container">
                <p class="aif-my-demand-container__info-container__subject">
                    <?= $demand->Type_de_demande_AIF__c ?>
                </p>

                <?php  if ($demand->Statut_Espace_Don__c == "Rejetée") : ?>

                    <?php
        aif_include_partial("info-message", [
        "content" => "Malheureusement votre demande n'a pas pu aboutir. Contactez-nous pour en savoir plus."]);
                    ?>


                <?php endif ?>
            </div>
        </div>
        <?php endforeach ?>


        <a class="btn btn--dark aif-mt1w aif-button--full" href="#">Vous avez une question ?</a>



    </section>


</main>



<?php

                    get_footer();
?>