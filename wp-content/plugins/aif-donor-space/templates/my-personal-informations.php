<?php

/* Template Name: Espace Donateur - Home */
get_header();

check_user_page_access();


$current_user = wp_get_current_user();
$sf_user_ID = get_SF_user_ID($current_user->ID);

$sf_user = get_salesforce_user_data($sf_user_ID);
$sf_member = get_salesforce_member_data($current_user->user_email);
$SEPA_mandates = get_salesforce_user_SEPA_mandate($sf_user_ID);

$actifMandate  = null;
$day_of_payment = null;
$has_error = false;

$action_succeed = false;
$disable_button = false;


$requiredFields = [
"Salutation", "LastName", "Code_Postal__c", "FirstName", "Email", "Adresse_Ligne_4__c", "Ville__c", "Pays__c", "MobilePhone"
];

$countries = [
    "Afghanistan", "Afrique du Sud", "Albanie", "Algérie", "Allemagne",
    "Andorre", "Angola", "Antigua-et-Barbuda", "Arabie saoudite", "Argentine",
    "Arménie", "Australie", "Autriche", "Azerbaïdjan", "Bahamas", "Bahreïn",
    "Bangladesh", "Barbade", "Belgique", "Belize", "Bénin", "Bhoutan",
    "Biélorussie", "Birmanie", "Bolivie", "Bosnie-Herzégovine", "Botswana",
    "Brésil", "Brunei", "Bulgarie", "Burkina Faso", "Burundi", "Cambodge",
    "Cameroun", "Canada", "Cap-Vert", "Centrafrique", "Chili", "Chine",
    "Chypre", "Colombie", "Comores", "Congo-Brazzaville", "Congo-Kinshasa",
    "Corée du Nord", "Corée du Sud", "Costa Rica", "Côte d'Ivoire", "Croatie",
    "Cuba", "Danemark", "Djibouti", "Dominique", "Égypte", "Émirats arabes unis",
    "Équateur", "Érythrée", "Espagne", "Estonie", "Eswatini", "États-Unis",
    "Éthiopie", "Fidji", "Finlande", "France", "Gabon", "Gambie", "Géorgie",
    "Ghana", "Grèce", "Grenade", "Guatemala", "Guinée", "Guinée-Bissau",
    "Guinée équatoriale", "Guyana", "Haïti", "Honduras", "Hongrie", "Inde",
    "Indonésie", "Irak", "Iran", "Irlande", "Islande", "Israël", "Italie",
    "Jamaïque", "Japon", "Jordanie", "Kazakhstan", "Kenya", "Kirghizistan",
    "Kiribati", "Koweït", "Laos", "Lesotho", "Lettonie", "Liban", "Libéria",
    "Libye", "Liechtenstein", "Lituanie", "Luxembourg", "Macédoine du Nord",
    "Madagascar", "Malaisie", "Malawi", "Maldives", "Mali", "Malte", "Maroc",
    "Marshall", "Maurice", "Mauritanie", "Mexique", "Micronésie", "Moldavie",
    "Monaco", "Mongolie", "Monténégro", "Mozambique", "Namibie", "Nauru",
    "Népal", "Nicaragua", "Niger", "Nigéria", "Niue", "Norvège", "Nouvelle-Zélande",
    "Oman", "Ouganda", "Ouzbékistan", "Pakistan", "Palaos", "Palestine",
    "Panama", "Papouasie-Nouvelle-Guinée", "Paraguay", "Pays-Bas", "Pérou",
    "Philippines", "Pologne", "Portugal", "Qatar", "Roumanie", "Royaume-Uni",
    "Russie", "Rwanda", "Saint-Christophe-et-Niévès", "Sainte-Lucie",
    "Saint-Marin", "Saint-Vincent-et-les-Grenadines", "Salomon", "Salvador",
    "Samoa", "Sao Tomé-et-Principe", "Sénégal", "Serbie", "Seychelles",
    "Sierra Leone", "Singapour", "Slovaquie", "Slovénie", "Somalie", "Soudan",
    "Soudan du Sud", "Sri Lanka", "Suède", "Suisse", "Suriname", "Syrie",
    "Tadjikistan", "Tanzanie", "Tchad", "Tchéquie", "Thaïlande", "Timor oriental",
    "Togo", "Tonga", "Trinité-et-Tobago", "Tunisie", "Turkménistan", "Turquie",
    "Tuvalu", "Ukraine", "Uruguay", "Vanuatu", "Vatican", "Venezuela",
    "Viêt Nam", "Yémen", "Zambie", "Zimbabwe"
];


$actifMandate = get_active_sepa_mandate($SEPA_mandates->records);
$next_payement = "";

if($sf_member->hasMandatActif) {
    $day_of_payment = date("d", strtotime($actifMandate->Date_paiement_Avenir__c));
    $ibanBlocks = str_split($actifMandate->Tech_Iban__c, 4);
    $last4IBANDigit = substr($actifMandate->Tech_Iban__c, -4);
    $next_payement = date_format(date_create($actifMandate->Date_paiement_Avenir__c), "d/m/Y");
}

$user_status = aif_get_user_status($sf_member);


function checkKeys($requiredFields, $array_to_check)
{
    foreach ($requiredFields as $key) {
        if (!isset($key, $array_to_check)) {
            return false;
        }
    }
    return true;
}


if (checkKeys($requiredFields, $_POST) && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $disable_button = true;

    $partial_data = [
        "Identifiant_contact__c" => $sf_user->Identifiant_contact__c
    ];

    $data  = array_merge($_POST, $partial_data);

    patch_salesforce_user_data($data, $sf_user_ID);

    $sf_user = get_salesforce_user_data($sf_user_ID);

    $action_succeed = true;


}

?>



<main class="aif-container--main">

    <section class="aif-container--form">

        <header>
            <h1>Mes informations</h1>
        </header>

        <?php
        if ($action_succeed === true) {


            aif_include_partial("alert", [
                "state" => "success",
                "title" => "Votre demande de changement d'informations a bien été prise en compte",
            "content" => "Les changements seront effectifs d'ici quelques minutes"]);

        }

?>

        <h2>Mes informations personelles</h2>


        <?php if($sf_member->hasMandatActif) :  ?>

        <p> <?= "Vous êtes <span class='aif-text-bold aif-uppercase'> {$user_status} </span> d’Amnesty International France sous le numéro : {$sf_user->Identifiant_contact__c} en prélèvement automatique avec une périodicité <span class='aif-lowercase'> {$actifMandate->Periodicite__c} </span> d'un montant de {$actifMandate->Montant__c} €. Votre prochain prélèvement sera effectué le {$next_payement}." ?>
        </p>

        <?php else : ?>

        <p> <?= "Vous êtes <span class='aif-text-bold aif-uppercase'> {$user_status} </span> d’Amnesty International France sous le numéro : {$sf_user->Identifiant_contact__c}" ?>
        </p>

        <?php endif ?>

        <form class="" onsubmit="disablePersonalInformationsButtons()" role="form" method="POST" action="">
            <label for="email">Adresse email (obligatoire)</label>
            <input class="aif-input aif-input--disabled" id="email" readonly read type="email"
                value="<?= $current_user->user_email ?>"
                name="Email" />

            <?php

                        $url = add_query_arg([
                "subject" =>  "Modifier mon e-mail",
            ], get_permalink(get_page_by_path('espace-don/nous-contacter')));
aif_include_partial("info-message", [
"id" => "email-help-message",
"content" => "Votre email sert d’identifiant pour votre compte Espace donateur. Pour le modifier, <a class='aif-link--secondary' href='{$url}'>contactez-nous </a>."]); ?>

            <fieldset class="aif-flex aif-fieldset">
                <legend class="aif-fieldset__legend">Civilité (obligatoire)<legend>

                        <div class="aif-radio-button-container">
                            <div class="aif-radio-button-container__button">
                                <input
                                    <?= $sf_user->Salutation == 'M' ? "checked" : '' ?>
                                type="radio" id="M" name="Salutation" value="M" />
                                <label for="M">Monsieur</label>
                            </div>
                            <div class="aif-radio-button-container__button">
                                <input type="radio" id="Mme"
                                    <?= $sf_user->Salutation == 'MME' ? "checked" : '' ?>
                                name="Salutation" value="MME" />
                                <label for="Mme">Madame</label>
                            </div>

                        </div>
            </fieldset>

            <label for="firstname">Prénom (obligatoire)</label>
            <input placeholder="Prénom" autocomplete="gi<dven-name" name="FirstName" class="aif-input" required
                aria-required="" id="firstname" read type="text"
                value="<?= $sf_user->FirstName ?>" />
            <label for="lastname">Nom (obligatoire)</label>
            <input placeholder="Nom" autocomplete="lastName" name="LastName" class="aif-input" required aria-required=""
                id="lastname" read type="text"
                value="<?= $sf_user->LastName ?>" />
            <label for="street-address">Adresse postale (obligatoire)</label>
            <input placeholder="N° et nom de la rue" autocomplete="street-address" name="Adresse_Ligne_4__c"
                class="aif-input" required aria-required="" id="street-address" type="text"
                value="<?= $sf_user->Adresse_Ligne_4__c ?>" />
            <label for="address-level3">Complément adresse</label>
            <input placeholder="N° d'appartement, étage, bâtiment" autocomplete="address-level3"
                name="Adresse_Ligne_3__c" class="aif-input" id="address-level3" type="text"
                value="<?= $sf_user->Adresse_Ligne_3__c ?>" />

            <label for="address-level5">Lieu dit</label>
            <input autocomplete="address-level5" name="Adresse_Ligne_5__c" class="aif-input" id="address-level5"
                type="text"
                value="<?= $sf_user->Adresse_Ligne_5__c ?>" />
            <label for="postal-code">Code Postal (obligatoire)</label>
            <input placeholder="Code Postal" autocomplete="postal-code" name="Code_Postal__c" class="aif-input"
                id="postal-code" type="text"
                value="<?= $sf_user->Code_Postal__c ?>" />
            <label for="city">Ville (obligatoire)</label>
            <input placeholder="Ville" autocomplete="address-level3" name="Ville__c" class="aif-input" id="city"
                type="text" value="<?= $sf_user->Ville__c ?>" />


            <div class="aif-dropdown__container">
                <label for="select">Pays</label>
                <button type="button" role="combobox" id="select" id="dropdown-button"
                    class="checkboxGroup-button i aif-dropdown__container_button">


                    <?=isset($sf_user->Pays__c) ? $sf_user->Pays__c : "Sélectionner votre Pays" ?>

                </button>


                <ul role="listbox" id="dropdown-list" class="checkboxGroup-list aif-dropdown__container_option-list">

                    <?php     foreach ($countries as $country): ?>

                    <li role="option" class="aif-dropdown__container_option-list__item <?= $country == $sf_user->Pays__c ? 'aif-dropdown__container_option-list__item--curent' : ''  ?> ">
                        <?= $country ?>
                    </li>
                    <?php     endforeach ?>

                </ul>

                <div id="announcement" aria-live="assertive" role="alert" class="aif-sr-only"></div>
                <!-- The screen reader will announce the content in this element  -->
            </div>

            <div class="aif-hidden">
                <label for="inputResult">Votre pays</label>
                <input autocomplete="country"
                    value="<?= $sf_user->Pays__c ?>" name="Pays__c"
                    class="aif-input" id="inputResult" type="text" />
            </div>



            <label for="tel">N° de téléphone portable</label>
            <input placeholder="06 00 00 00 00" autocomplete="tel" name="MobilePhone" class="aif-input" id="tel"
                type="text" value="<?= $sf_user->MobilePhone ?>" />

            <?php
            if(empty($sf_user->MobilePhone)) {
                aif_include_partial("info-message", [
                    "id" => "email-help-message",
                    "content" => "Nous vous conseillons de renseigner un numéro de téléphone afin de pouvoir vous contacter plus facilement."]);
            }
?>

            <label for="HomePhone">N° de téléphone domicile</label>
            <input placeholder="01 00 00 00 00" name="HomePhone" class="aif-input" id="HomePhone" type="text"
                value="<?= $sf_user->HomePhone ?>" />

            <button class="btn aif-mt1w aif-button--full" onclick="this.form.submit(); this.disabled=true;"
                type="submit">Transmettre les modifications</button>

            <button class="btn btn--dark aif-mt1w aif-button--full" type="reset">Annuler</button>
        </form>
    </section>




    <?php if($sf_member->hasMandatActif) :  ?>


    <section class="aif-container--form">
        <h2>
            Mes informations bancaires
        </h2>

        <p> <?= "Vous êtes <span class='aif-text-bold aif-uppercase'> {$user_status} </span> d’Amnesty International France sous le numéro : {$sf_user->Identifiant_contact__c} en prélèvement automatique avec une périodicité <span class='aif-lowercase'> {$actifMandate->Periodicite__c} </span> d'un montant de {$actifMandate->Montant__c} € le {$day_of_payment} de chaque mois." ?>
        </p>


        <?php aif_include_partial("alert", ["content" => "Prélèvement automatique sur l'IBAN se terminant par {$last4IBANDigit}"]); ?>

        <p>
            <a href="<?= get_permalink(get_page_by_path('espace-don/modification-coordonnees-bancaire')) ?>"
                class="btn btn--white  aif-button--full"> Modifier l'IBAN</a>
        </p>

    </section>
    <?php endif ?>




</main>


<?php

get_footer();
?>