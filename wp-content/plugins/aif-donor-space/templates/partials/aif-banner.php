<div class="aif-banner">
    <div class="aif-banner__image">
        <img src="<?= $pictureURL ?>" />
    </div>
    <div class="aif-banner__container">
        
    <?php if($member->isMembre && ($member->hasMandatActif || $member->isDonateur)): ?>
        <h2 class="aif-banner__container__title"> Renforcez votre soutien </h2>
        <p class="aif-banner__container__content">

        <?= $firstName ?>, merci de nous soutenir. Vous souhaitez aller plus loin ?
        </p>
    
        <ul class="aif-banner__container__links">
     
            <li>
                <a href="#" class="btn btn--primary">Faire un don ponctuel complémentaire</a>
            </li>        
     
        </ul>
    </div>
    <?php endif ?>

    <?php if($member->isMembre && !$member->hasMandatActif && !$member->isDonateur): ?>
        <h2 class="aif-banner__container__title"> Renforcez votre soutien </h2>
        <p class="aif-banner__container__content">

        <?= $firstName ?>, merci de nous soutenir. Vous souhaitez aller plus loin ?
        </p>
    
        <ul class="aif-banner__container__links">
     
            <li>
                <a href="#" class="btn btn--primary">Faire un don ponctuel complémentaire</a>
            </li>
            
            <li>
                <a href="#" class="aif-banner__container__links__item">Passer en prélèvement automatique</a>
            </li>  
     
        </ul>
    </div>
    <?php endif ?>
 
    <?php if(!$member->isMembre && !$member->hasMandatActif && !$member->isDonateur): ?>
        <h2 class="aif-banner__container__title"> Agissez pour défendre les droits humains         </h2>
        <p class="aif-banner__container__content">

        <?= $firstName ?>, merci de nous soutenir. Vous souhaitez aller plus loin ?
        </p>
    
        <ul class="aif-banner__container__links">
     
            <li>
                <a href="#" class="btn btn--primary">Devenez membre</a>
            </li>
            
            <li>
                <a href="#" class="aif-banner__container__links__item">Faire un don ponctuel</a>
            </li>  
     
        </ul>
    </div>
    <?php endif ?> 

    <?php if(!$member->isMembre && !$member->hasMandatActif && $member->isDonateur): ?>
        <h2 class="aif-banner__container__title"> Agissez pour défendre les droits humains         </h2>
        <p class="aif-banner__container__content">

        <?= $firstName ?>, merci de nous soutenir. Vous souhaitez aller plus loin ?
        </p>
    
        <ul class="aif-banner__container__links">
     
            <li>
                <a href="#" class="btn btn--primary">Devenez membre</a>
            </li>
            
            <li>
                <a href="#" class="aif-banner__container__links__item">Faire un don ponctuel</a>
            </li>  
     
        </ul>
    </div>
    <?php endif ?> 
</div>