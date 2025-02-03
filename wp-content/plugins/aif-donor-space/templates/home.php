<?php

/* Template Name: Espace Donateur - Home */
get_header();

check_user_page_access();

$current_user = wp_get_current_user();
$sf_member = get_salesforce_member_data($current_user->user_email);
$sf_user = get_salesforce_user_data($sf_member->Id);

?>

<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">
    <?php if (isset($error_message)) : ?>
    <div class="aif-error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <div class="container">
        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Mon espace donateur</h1>
        </header>

        <section>

            <p>Bonjour <span class="aif-text-bold"> <?= $sf_user->Name ?> </span> vous êtes

                <?php if($sf_member->isDonateur) : ?>
                membre donateur
                <?php elseif($sf_member->isMembre): ?>
                membre
                <?php else: ?>
                ancien membre
                <?php endif; ?>

                d'Amnesty International France en prélèvement automatique mensuel sous le n° <?= $sf_user->Identifiant_contact__c ?> </p>


            <p>Bienvenue dans votre espace don qui permet la gestion administrative des informations liées à vos
                dons et adhésion

            </p>

            <h2>Menu navigation</h2>

            <nav aria-label="navigation espace donateur">
                <ul class="aif-list-none aif-m0 aif-flex aif-gap-single aif-flex-col">
                    <li>
                        <a class="aif-underline"
                            href="<?= get_permalink(get_page_by_path('espace-don/mes-recus-fiscaux')) ?>">Mes reçus
                            fiscaux
                            <svg class="aif-rotate-180" width="13" height="7" viewBox="0 0 13 7" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="Frame">
                                    <path id="Vector" d="M3.5 1L3.9 1.4L2.2 3.2H12V3.8H2.2L3.9 5.6L3.5 6L1 3.5L3.5 1Z"
                                        fill="#2B2B2B" />
                                </g>
                            </svg>

                        </a>
                    </li>

                    <li>
                        <a class="aif-underline"
                            href="<?= get_permalink(get_page_by_path('espace-don/mes-informations-personelles')) ?>">Modifier mes informations personelles
                            <svg class="aif-rotate-180" width="13" height="7" viewBox="0 0 13 7" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="Frame">
                                    <path id="Vector" d="M3.5 1L3.9 1.4L2.2 3.2H12V3.8H2.2L3.9 5.6L3.5 6L1 3.5L3.5 1Z"
                                        fill="#2B2B2B" />
                                </g>
                            </svg>

                        </a>
                    </li>

                </ul>

            </nav>


        </section>




    </div>

</main>

<?php
get_footer();
