<div class="aif-banner">
    <div class="aif-banner__image">
        <img src="<?= $pictureURL ?>" />
    </div>
    <div class="aif-banner__container">
        
    <?php if($member->hasMandatActif): ?>
        <h2 class="aif-banner__container__title">  <?= $firstName ?>, renforcez votre soutien ! </h2>
        <p class="aif-banner__container__content">Merci de nous soutenir ! Envie d’aller plus loin dans votre engagement ? </p>
        <ul class="aif-banner__container__links">
            <li>
                <a href="https://soutenir.amnesty.fr/b?cid=427&lang=fr_FR" class="btn btn--primary">Je fais un don complémentaire</a>
            </li>        
        </ul>
    </div>

    <?php elseif($member->isMembre): ?>
        <div>
            <h2 class="aif-banner__container__title">  <?= $firstName ?>, renforcez votre soutien !</h2>
            <p class="aif-banner__container__content">Merci de nous soutenir ! Envie d’aller plus loin dans votre engagement ?</p>
            <ul class="aif-banner__container__links">
                <li>
                    <a href="https://soutenir.amnesty.fr/b?cid=428&lang=fr_FR" class="btn btn--primary">Faire un don ponctuel complémentaire</a>
                </li>
            </ul>
        </div>
    <?php else: ?>
        <div>
            <h2 class="aif-banner__container__title"> Agissez pour défendre les droits humains</h2>
            <p class="aif-banner__container__content">Merci de nous soutenir ! Envie d’aller plus loin dans votre engagement ?</p>

            <ul class="aif-banner__container__links">
                <li>
                    <a href="https://soutenir.amnesty.fr/b?cid=429&lang=fr_FR" class="btn btn--primary">Je m’engage dans la durée. </a>
                </li>
            </ul>
        </div>
    <?php endif ?>
</div>
