<?php
/**
 * Title: Foundation Form Pattern
 * Description: foundation form
 * Slug: amnesty/form-foundation
 * Inserter: no
 */

$page = get_page_by_path('formulaire-foundation');
$content = $page->post_content ?? 'page non récupérée';

$image_url      = get_template_directory_uri() . '/assets/images/amnesty-foundation.jpg';
$icon_phone_url = get_template_directory_uri() . '/assets/images/icon-phone.svg';

?>

<div class="page-foundation-form">
    <div class="page-foundation-form-wrapper">
        <div class="form-container">
            <div class="officers">
                <div class="officers-image-container">
                    <img class="officers-image" src="<?php echo esc_url($image_url); ?>" alt=""/>
                </div>
                <p class="officers-job">Chargée de la relation avec les donatrices et donateurs de la Fondation</p>
                <p class="officers-names">Milena Djelic</p>
                <div class="phone-container">
                    <div class="icon-container">
                        <img src="<?php echo esc_url($icon_phone_url); ?>" alt=""/>
                    </div>
                    <p class="phone">01 53 38 65 31</p>
                </div>
            </div>
            <div class="foundation-form">
                <h2 class="title">À VOTRE ÉCOUTE</h2>
                <p class="officers-citation">Je suis à votre écoute pour toute question ou pour tout besoin d’information à propos de la Fondation, ou des dispositions fiscales qui accompagnent votre générosité.</p>
                <?php echo apply_filters('the_content', $content); ?>
            </div>
        </div>
        <p class="legals">Les informations recueillies sur ce formulaire sont enregistrées dans un fichier informatisé et sécurisé par Amnesty International France (AIF), à des fins de traitement administratif de votre don et de votre reçu fiscal, pour répondre à vos demandes, pour vous communiquer des informations en lien avec notre mission ou faire appel à votre engagement. Le responsable de traitement est AIF, Association Loi 1901, dont le siège social est situé au 76 bd de la Villette, 75940 Paris cedex 19. AIF est représentée par Anne Savinel-Barras, sa Présidente. Elles sont destinées au secrétariat administratif de la Fondation AIF et aux tiers mandatés par celle-ci. Vos données personnelles sont hébergées sur des serveurs informatiques situés en Europe et aux États-Unis. Des règles assurant la protection et la sécurité de ces données ont été mises en place. Elles sont disponibles sur simple demande adressée à la Fondation. Ces informations sont conservées pendant la durée strictement nécessaire à la réalisation des finalités précitées. Conformément à la loi « informatique et libertés » et à la réglementation européenne, vous disposez d’un droit d’accès, de rectification, de suppression, de restriction et d’opposition au traitement des données vous concernant, ainsi qu’un droit à la portabilité en contactant : Fondation Amnesty International France – Secrétariat administratif – 76 bd de la Villette CS 40088 75939 Paris Cedex 19 – 01 53 38 65 65 – fondation@amnesty.fr. Vous pouvez également introduire une réclamation auprès de la CNIL.</p>
    </div>
</div>
