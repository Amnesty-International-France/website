<?php
$image_url = get_template_directory_uri() . '/assets/images/other-donation.png';
?>

<div class="aif-banner">
    <div class="aif-banner__image">
        <img src="<?= $image_url ?>" />
    </div>
    <div class="aif-banner__container">
        
    <?php if ($member->hasMandatActif): ?>
        <h2 class="aif-banner__container__title">  <?= $firstName ?>, renforcez votre soutien ! </h2>
        <p class="aif-banner__container__content">Merci de nous soutenir ! Envie d’aller plus loin dans votre engagement ? </p>
        <ul class="aif-banner__container__links">
            <li>
                <div class='custom-button-block'>
                    <a href="https://soutenir.amnesty.fr/b?cid=427&lang=fr_FR" class="custom-button">
                        <div class='content bg-yellow medium'>
                            <div class="icon-container">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                >
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </div>
                            <div class="button-label">Je fais un don complémentaire</div>
                        </div>
                    </a>
                </div>
            </li>        
        </ul>
    </div>

    <?php elseif ($member->isMembre): ?>
        <div>
            <h2 class="aif-banner__container__title">  <?= $firstName ?>, renforcez votre soutien !</h2>
            <p class="aif-banner__container__content">Merci de nous soutenir ! Envie d’aller plus loin dans votre engagement ?</p>
            <ul class="aif-banner__container__links">
                <li>
                    <div class='custom-button-block'>
                        <a href="https://soutenir.amnesty.fr/b?cid=428&lang=fr_FR" class="custom-button">
                            <div class='content bg-yellow medium'>
                                <div class="icon-container">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        strokeWidth="1.5"
                                        stroke="currentColor"
                                    >
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </div>
                                <div class="button-label">Faire un don ponctuel complémentaire</div>
                            </div>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    <?php else: ?>
        <div>
            <h2 class="aif-banner__container__title"> Agissez pour défendre les droits humains</h2>
            <p class="aif-banner__container__content">Merci de nous soutenir ! Envie d’aller plus loin dans votre engagement ?</p>

            <ul class="aif-banner__container__links">
                <li>
                    <div class='custom-button-block'>
                        <a href="https://soutenir.amnesty.fr/b?cid=429&lang=fr_FR" class="custom-button">
                            <div class='content bg-yellow medium'>
                                <div class="icon-container">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        strokeWidth="1.5"
                                        stroke="currentColor"
                                    >
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </div>
                                <div class="button-label">Je m’engage dans la durée.</div>
                            </div>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    <?php endif ?>
</div>
