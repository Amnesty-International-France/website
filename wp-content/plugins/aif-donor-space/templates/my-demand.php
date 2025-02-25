<?php

/* Template Name: Espace Donateur - Home */
get_header();

check_user_page_access();

$current_user = wp_get_current_user();
$sf_user_ID = get_SF_user_ID($current_user->ID);


$demands =  get_salesforce_user_demands($sf_user_ID);
$sortedDemands = sortByDateProp($demands, "Date_de_la_demande__c");

function aif_format_date($date)
{
    $formatted_date = date_format(date_create($date), "d/m/Y");
    return "Le {$formatted_date}" ;

}


?>



<main class="aif-container--main">

    <section class="aif-container--form">
        <header>
            <h1>Mes demandes</h1>
        </header>

        <?php if (count($demands) == 0) : ?>


        <p>
            Vous n’avez pas encore fait de demande.
        </p>

        <p>
            Revenez ici pour vérifier le statut de vos prochaines demandes.
        </p>

        <?php else: ?>

        <?php     foreach ($sortedDemands as $demand): ?>

        <div class="aif-my-demand-container">
            <div class="aif-my-demand-container__title-container">
                <p class="aif-my-demand-container__title-container__date">
                    <?= aif_format_date($demand->Date_de_la_demande__c) ?>
                </p>

                <?php

                $status = "";

            switch ($demand->Statut_Espace_Don__c) {
                case "A traiter":
                case "En cours":
                    $status = "warning";
                    break;
                case "Fermé - Echoué":
                    $status = "error";
                    break;
                case "Fermé - Traité":
                    $status = "success";
                    break;
                default:
                    $status = "warning";
                    break;

            }

            aif_include_partial("label", [
            "content" => $demand->Statut_Espace_Don__c,
            "variant" => $status
            ]);
            ?>

            </div>

            <div class="aif-my-demand-container__info-container">
                <p class="aif-my-demand-container__info-container__subject">

                    <?php if (isset($demand->Type_de_demande_AIF__c)) : ?>
                    <?= $demand->Type_de_demande_AIF__c ?>

                    <?php else : ?>
                    <?= $demand->Subject ?>
                    <?php endif ?>
                </p>

                <?php  if ($demand->Statut_Espace_Don__c == "Fermé - Echoué") : ?>

                <?php


$url = add_query_arg([
    "subject" =>  "Ma demande n'a pas pu aboutir",
], get_permalink(get_page_by_path('espace-don/nous-contacter')));

                    aif_include_partial("info-message", [
                    "content" => "Malheureusement votre demande n'a pas pu aboutir. <a class='aif-link--secondary' href='{$url}'>Contactez-nous pour en savoir plus. </a>."]);
                    ?>


                <?php endif ?>
            </div>
        </div>
        <?php endforeach ?>

        <?php endif ?>




        <a class="btn btn--dark aif-mt1w aif-button--full"
            href="<?= get_permalink(get_page_by_path('espace-don/nous-contacter')) ?>">Vous
            avez une question ?</a>



    </section>


</main>



<?php

                            get_footer();
?>