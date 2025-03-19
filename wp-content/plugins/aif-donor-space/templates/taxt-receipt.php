<?php

/* Template Name: Espace Donateur - Home */
get_header();

check_user_page_access();

$current_user = wp_get_current_user();
$sf_user_ID = get_SF_user_ID($current_user->ID);
$tax_reciept = get_salesforce_user_tax_reciept($sf_user_ID);

$sorted = [];
$groupped = [];

if (count($tax_reciept) > 0) {
    $sorted = sortByDateProp($tax_reciept, "Debut__c");
    $groupped = groupByYear($sorted, "Debut__c");
}

?>


<main class="aif-container--main">

    <div class="aif-container--form"">

        <header class=" wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
        <h1 class="article-title wp-block-post-title">Mes reçus fiscaux</h1>
        </header>


        <section>
            <h2> Historique de vos reçus fiscaux </h2>
            <p> Retrouvez dans cet espace tous vos reçus fiscaux. </p>

            <?php
              $url = add_query_arg([
                "subject" =>  "Mes dons et adhésions",
            ], get_permalink(get_page_by_path('nous-contacter')));

aif_include_partial("alert", [
"content" => "Pour information, les reçus fiscaux annuels seront disponibles à la fin du premier trimestre suivant l'année de vos dons.",
"additional_content" => "Pour toutes questions ou modifications sur vos dons et/ou adhésion <a class='aif-link--secondary' href='{$url}'>contactez-nous </a>",
           ]);

?>

            <?php if (count($tax_reciept) > 0): ?>
            <?php $index = 0 ?>


            <?php foreach ($groupped as $year => $records): ?>
            <details class="wp-block-details" <?= $index < 2 ? 'open' : '' ?>>
                <summary class="">
                    <h3> <?=  $year ?> </h3>

                </summary>

                <?php foreach ($records as $record): ?>
                <?php $name = $record->Name  ?>

                <div class="aif-mb1w">
                    <div id="aif-success-message-<?=$name?>"
                        class="aif-hide">
                        <?php

                    $url = get_permalink(get_page_by_path('mes-demandes'));
                    aif_include_partial("alert", [
                        "title" => "Votre demande à bien été prise en compte",
                    "content" => "L'envoi de votre reçu fiscal n'est pas immédiat. Vous le receverez d'ici quelques minutes dans votre boîte email.",
          "additional_content" => "Vous pouvez voir le suivi du traitement de vos demandes sur la page  <a class='aif-link--secondary' href='{$url}'> Mes demandes. </a>",
         "state" => "success"]);

                    ?>
                    </div>
                    <p class="aif-m0 aif-p0">Du
                        <?=  date("d/m/Y", strtotime($record->Debut__c));  ?>
                        au
                        <?=  date("d/m/Y", strtotime($record->Fin__c));  ?>
                        -
                        <span class="aif-text-bold">
                            <?= $record->Montant_recu__c ?> € </span>
                    </p>

                    <p>
                        Numéro du reçu fiscal : <?= $name ?>
                    </p>


                    <button data-id="get-duplicate-tax-receipt-button"
                        onclick="createDuplicateTaxReceiptDemand('<?=$name?>');"
                        aria-label='Demander votre duplicata pour le reçu fiscal numéro <?= $name?>'
                        class="btn btn--large">
                        Demander votre duplicata de reçu fiscal
                    </button>
                </div>

                <?php endforeach ?>

            </details>

            <?php $index = $index + 1 ?>
            <?php endforeach ?>

            <?php else: ?>
            <p>Vous n'avez pas encore de reçu fiscaux</p>
            <?php endif; ?>

            <p>
            </p>

            <?php

$content = "Les dons de plus de 5 ans et inférieurs à 7 € ne sont pas affichés dans cet espace";

aif_include_partial("alert", ["content" => $content, ]);

$url = add_query_arg([
    "subject" =>  "Mes reçus fiscaux",
], get_permalink(get_page_by_path('nous-contacter')));

$content2 = "Le montant de votre abonnement au magazine la Chronique n’est pas déductible des impôts. Pour toutes autres demandes concernant vos reçus fiscaux, <a class='aif-link--secondary' title='contacter le service SMD d'Amnesty France' href='{$url}'>contactez-nous </a>";


aif_include_partial("alert", ["content" => $content2, ]);


?>


        </section>
    </div>
</main>


</div>


<?php

get_footer();
?>
