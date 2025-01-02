<?php

/* Template Name: Espace Donateur - Home */
get_header();

check_user_page_access();

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

            <h2>Menu navigation</h2>
            <nav aria-label="navigation espace donateur">
                <li>
                    <a class="aif-underline aif-text-large"
                        href="<?= get_permalink(get_page_by_path('espace-donateur/mes-recus-fiscaux')) ?>">Mes reçus
                        fiscaux
                        <svg class="aif-rotate-180" width="26" height="15" viewBox="0 0 13 7" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g id="Frame">
                                <path id="Vector" d="M3.5 1L3.9 1.4L2.2 3.2H12V3.8H2.2L3.9 5.6L3.5 6L1 3.5L3.5 1Z"
                                    fill="#2B2B2B" />
                            </g>
                        </svg>

                    </a>
                </li>
            </nav>


        </section>




    </div>

</main>

<?php
get_footer();
