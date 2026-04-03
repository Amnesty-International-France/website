<?php

/**
 * Title: Legs Form Pattern
 * Description: Legs form
 * Slug: amnesty/form-legs
 * Inserter: no
 */

$page = get_page_by_path('formulaire-legs');
$content = $page->post_content ?? 'page non récupérée';

$image_url      = get_template_directory_uri() . '/assets/images/testator-relations-officers.png';
$icon_phone_url = get_template_directory_uri() . '/assets/images/icon-phone.svg';
?>

<div class="page-legs-form">
    <div class="page-legs-form-wrapper">
        <div class="form-container">
            <div class="officers">
                <div class="officers-image-container">
                    <img class="officers-image" src="<?php echo esc_url($image_url); ?>" alt=""/>
                </div>
                <p class="officers-names">Sophie ROUPPERT et Lisa LACOSTE</p>
                <p class="officers-job">Chargées de relations testateurs</p>
                <div class="phone-container">
                    <div class="icon-container">
                        <img src="<?php echo esc_url($icon_phone_url); ?>" alt=""/>
                    </div>
                    <p class="phone">01 53 38 66 24</p>
                </div>
            </div>
            <div class="legs-form">
                <h2 class="title"> DEMANDE DE BROCHURE</h2>
                <p class="subtitle">Je souhaite recevoir la brochure d'informations sur les legs, donations et assurances-vie gratuitement et sans engagement :</p>
			    <?php echo apply_filters('the_content', $content); ?>
            </div>
        </div>
        <p class="legals">Les informations que vous nous transmettez sont traitées par l’association Amnesty International France (AIF), responsable du traitement, pour répondre à vos demandes et suivre au mieux votre projet de transmission, pour vous communiquer des informations en lien avec notre mission et vous envoyer nos propositions d’engagement, qu’elles soient militantes ou financières.Conformément au Règlement européen général sur la protection des données du 27 avril 2016 et à la loi Informatique et Libertés modifiée, vous disposez d’un droit d’accès, de rectification, d’effacement, de limitation et d’opposition au traitement des données vous concernant, ainsi qu’un droit à la portabilité. Vous pouvez exercer ces droits en contactant le service relations membres et donateurs d’AIF à l’adresse mentionnée au recto, par email (smd@amnesty.fr) ou par téléphone (01 53 38 65 80). Vous pouvez également introduire une réclamation auprès de la CNIL. Pour plus d’informations sur le traitement de vos données personnelles, veuillez consulter <a href="/politique-de-confidentialite">politique de confidentialité</a></p>
    </div>
</div>

