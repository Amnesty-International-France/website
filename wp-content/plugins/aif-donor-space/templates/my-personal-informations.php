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



<main class="aif-container--main">
       
        <section class="aif-container--form">

        <header>
            <h1>Mes informations</h1>
        </header>

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


            <div  class="aif-dropdown__container">
            <label for="select">Pays</label>
                <button
                type="button"
                role="combobox"
                id="select"
                id="dropdown-button"
           
                class="checkboxGroup-button i aif-dropdown__container_button">
                Séléctionner votre pays
            
            </button>
       

            <ul role="listbox" id="dropdown-list" class="checkboxGroup-list aif-dropdown__container_option-list">
                
            <?php     foreach ($countries as $country): ?>

            <li role="option" class="aif-dropdown__container_option-list__item "><?= $country ?></li>
            <?php     endforeach ?>

            </ul>

            <div id="announcement" aria-live="assertive" role="alert" class="aif-sr-only" ></div> <!-- The screen reader will announce the content in this element  -->
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


        <section class="aif-container--form">
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

<script>




const elements = {
  button: document.querySelector('[role="combobox"]'),
  dropdown: document.querySelector('[role="listbox"]'),
  options: document.querySelectorAll('[role="option"]'),
  announcement: document.getElementById('announcement'),
};

let isDropdownOpen = false;
let currentOptionIndex = 0;
let lastTypedChar = '';
let lastMatchingIndex = 0;

const toggleDropdown = () => {
  elements.button.classList.toggle('is-active');
  isDropdownOpen = !isDropdownOpen;
  elements.button.setAttribute('aria-expanded', isDropdownOpen.toString());

  if (isDropdownOpen) {
    focusCurrentOption();
  } else {
    elements.button.focus(); // focus the button when the dropdown is closed just like the select element
  }
};

const focusCurrentOption = () => {
  const currentOption = elements.options[currentOptionIndex];

  currentOption.classList.add('aif-dropdown__container_option-list__item--curent');
  currentOption.focus();

  currentOption.focus();

// Scroll the current option into view
    currentOption.scrollIntoView({
        block: 'nearest',
    });

  elements.options.forEach((option, index) => {
    if (option !== currentOption) {
      option.classList.remove('aif-dropdown__container_option-list__item--curent');
    }
  });
};

const handleKeyPress = (event) => {
  event.preventDefault();
  const { key } = event;
  const openKeys = ['ArrowDown', 'ArrowUp', 'Enter', ' '];

  if (!isDropdownOpen && openKeys.includes(key)) {
    toggleDropdown();

  } else if (isDropdownOpen) {
    switch (key) {
      case 'Escape':
        toggleDropdown();
        break;
      case 'ArrowDown':
        moveFocusDown();
        break;
      case 'ArrowUp':
        moveFocusUp();
        break;
      case 'Enter':
      case ' ':
        selectCurrentOption();
        break;
      default:
        // Handle alphanumeric key presses for mini-search
        handleAlphanumericKeyPress(key);
        break;
    }
  }
};

const handleDocumentInteraction = (event) => {
  const isClickInsideButton = elements.button.contains(event.target);
  const isClickInsideDropdown = elements.dropdown.contains(event.target);

  if (isClickInsideButton || (!isClickInsideDropdown && isDropdownOpen)) {
    toggleDropdown();
  }

  // Check if the click is on an option
  const clickedOption = event.target.closest('[role="option"]');
  if (clickedOption) {
    selectOptionByElement(clickedOption);
  }
};


const moveFocusDown = () => {
  if (currentOptionIndex < elements.options.length - 1) {
    currentOptionIndex++;
  } else {
    currentOptionIndex = 0;
  }
  focusCurrentOption();
};

const moveFocusUp = () => {
  if (currentOptionIndex > 0) {
    currentOptionIndex--;
  } else {
    currentOptionIndex = elements.options.length - 1;
  }
  focusCurrentOption();
};

const selectCurrentOption = () => {
  const selectedOption = elements.options[currentOptionIndex];
  selectOptionByElement(selectedOption);
};

const announceOption = (text) => {
  elements.announcement.textContent = text;
  elements.announcement.setAttribute('aria-live', 'assertive');
  setTimeout(() => {
    elements.announcement.textContent = '';
    elements.announcement.setAttribute('aria-live', 'off');
  }, 1000); // Announce and clear after 1 second (adjust as needed)
};


const selectOptionByElement = (optionElement) => {
  const optionValue = optionElement.textContent;

  elements.button.textContent = optionValue;
  elements.options.forEach(option => {
    option.classList.remove('aif-dropdown__container_option-list__item--curent');
    option.setAttribute('aria-selected', 'false');
  });

  optionElement.classList.add('aif-dropdown__container_option-list__item--curent');
  optionElement.setAttribute('aria-selected', 'true');

  toggleDropdown();
  announceOption(optionValue);
};

const handleAlphanumericKeyPress = (key) => {
  const typedChar = key.toLowerCase();

  if (lastTypedChar !== typedChar) {
    lastMatchingIndex = 0;
  }

  const matchingOptions = Array.from(elements.options).filter((option) =>
    option.textContent.toLowerCase().startsWith(typedChar)
  );

  if (matchingOptions.length) {
    if (lastMatchingIndex === matchingOptions.length) {
      lastMatchingIndex = 0;
    }
    let value = matchingOptions[lastMatchingIndex]
    const index = Array.from(elements.options).indexOf(value);
    currentOptionIndex = index;
    focusCurrentOption();
    lastMatchingIndex += 1;
  }
  lastTypedChar = typedChar;
};

elements.button.addEventListener('keydown', handleKeyPress);
// elements.button.addEventListener('click', toggleDropdown);
document.addEventListener('click', handleDocumentInteraction);



</script>


<?php

get_footer();
?>