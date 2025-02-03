<?php

/* Template Name: Espace Donateur - Home */
get_header();

check_user_page_access();


$current_user = wp_get_current_user();
$sf_user_ID = get_SF_user_ID($current_user->ID);

$SF_User = get_salesforce_user_data($sf_user_ID);
$SF_membre_data = get_salesforce_member_data($current_user->user_email);
$SEPA_mandates = get_salesforce_user_SEPA_mandate($sf_user_ID);

$actifMandate  = null;
$day_of_payment = null;
$has_error = false;


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

if($actifMandate) {
    $day_of_payment = date("d", strtotime($actifMandate->Date_paiement_Avenir__c));
    $ibanBlocks = str_split($actifMandate->Tech_Iban__c, 4);
    $last4IBANDigit = substr($actifMandate->Tech_Iban__c, -4);
}

$user_status = aif_get_user_status($SF_membre_data);


function checkKeys($requiredFields, $array_to_check)
{
    foreach ($requiredFields as $key) {
        if (!isset($key, $array_to_check)) {
            return false;
        }
    }
    return true;
}


if (checkKeys($requiredFields, $_POST)) {

    $partial_data = [
        "Identifiant_contact__c" => $SF_User->Identifiant_contact__c
    ];

    $data  = array_merge($_POST, $partial_data);
    post_salesforce_user_data($data);

}

?>

<div class="aif-grid-container aif-mt1w">

    <nav class="aif-flex aif-mr1w aif-lg-justify-end aif-container aif-mb1w" aria-label="menu retour a l'espace don">
        <a class=""
            href="<?= get_permalink(get_page_by_path('espace-don')) ?>">

            <svg class="" width="13" height="7" viewBox="0 0 13 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g id="Frame">
                    <path id="Vector" d="M3.5 1L3.9 1.4L2.2 3.2H12V3.8H2.2L3.9 5.6L3.5 6L1 3.5L3.5 1Z" fill="#2B2B2B" />
                </g>
            </svg>
            Revenir à mon espace don
        </a>
    </nav>

    <main class="">
        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Mes informations personelles</h1>
        </header>

        <section>
            <h2>Mes informations personelles</h2>

            <p> <?= "Vous êtes <span class='aif-text-bold aif-uppercase'> {$user_status} </span> d’Amnesty International France sous le numéro : {$SF_User->Identifiant_contact__c}" ?>
            </p>


            <form class="" role="form" method="POST" action="">
                <label for="email">Adresse email (obligatoire)</label>
                <input class="aif-input" id="email" readonly read type="email"
                    value="<?= $current_user->user_email ?>"
                    name="Email" />

                <p class="aif-mt1w">Votre email sert d'identifiant pour votre Espace Don. Pour le modiifer <a
                        class="aif-link--primary"> contactez-nous</a></p>

                <fieldset class="aif-flex">
                    <legend>Civilité<legend>

                            <input type="radio" id="M" name="Salutation" value="M" checked />
                            <label for="M">Monsieur</label>


                            <input type="radio" id="Mme" name="Salutation" value="Mme" />
                            <label for="Mme">Madame</label>
                </fieldset>

                <label for="firstname">Prénom (obligatoire)</label>
                <input autocomplete="gi<dven-name" name="FirstName" class="aif-input" required aria-required=""
                    id="firstname" read type="text"
                    value="<?= $SF_User->FirstName ?>" />
                <label for="lastname">Nom (obligatoire)</label>
                <input autocomplete="lastName" name="LastName" class="aif-input" required aria-required="" id="lastname"
                    read type="text"
                    value="<?= $SF_User->LastName ?>" />
                <label for="street-address">Adresse postale (obligatoire)</label>
                <input autocomplete="street-address" name="Adresse_Ligne_4__c" class="aif-input" required
                    aria-required="" id="street-address" type="text"
                    value="<?= $SF_User->Adresse_Ligne_4__c ?>" />
                <label for="address-level3">Complément adresse</label>
                <input autocomplete="address-level3" name="Adresse_Ligne_3__c" class="aif-input" id="address-level3"
                    type="text"
                    value="<?= $SF_User->Adresse_Ligne_3__c ?>" />
                <label for="postal-code">Code Postal (obligatoire)</label>
                <input autocomplete="postal-code" name="Code_Postal__c" class="aif-input" id="postal-code" type="text"
                    value="<?= $SF_User->Code_Postal__c ?>" />
                <label for="city">Ville (obligatoire)</label>
                <input autocomplete="address-level3" name="Ville__c" class="aif-input" id="city" type="text"
                    value="<?= $SF_User->Ville__c ?>" />

                <div>

                    <label for="Pays__c">Pays (obligatoire)</label>
                    <select id="Pays__c" name="Pays__c" aria-label="Sélectionnez un pays">
                        <?php foreach ($countries as $country) : ?>
                        <option
                            value="<?php echo htmlspecialchars($country); ?>">
                            <?php echo htmlspecialchars($country); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <label for="tel">N° de téléphone portable</label>
                <input autocomplete="tel" name="MobilePhone" class="aif-input" id="tel" type="text"
                    value="<?= $SF_User->MobilePhone ?>" />
                <label for="HomePhone">N° de téléphone domicile</label>
                <input name="HomePhone" class="aif-input" id="HomePhone" type="text"
                    value="<?= $SF_User->HomePhone ?>" />


                <button class="btn aif-mt1w aif-button--full" type="submit">Transmettre les modifications</button>
            </form>
        </section>




        <?php if($actifMandate) :  ?>


        <section class="aif-mt1w">
        <h2>
            Mes informations bancaires
        </h2>

        <p> <?= "Vous êtes <span class='aif-text-bold aif-uppercase'> {$user_status} </span> d’Amnesty International France sous le numéro : {$SF_User->Identifiant_contact__c} en prélèvement automatique avec une périodicité <span class='aif-lowercase'> {$actifMandate->Periodicite__c} </span> de {$actifMandate->Montant__c} € le {$day_of_payment} de chaque mois." ?>
        </p>

        <p>Prélèvement automatique sur l'IBAN se terminant par <?= $last4IBANDigit ?> </p>

        <p>
            <a   href="<?= get_permalink(get_page_by_path('espace-don/modification-coordonnees-bancaire')) ?>"  class="aif-link--primary"> Modifier l'IBAN</a>
        </p>

        </section>
<?php endif ?>
  



    </main>

    <div>
        <!-- Leave Empty -->
    </div>
</div>


<?php

get_footer();
?>