<?php

/**
 * Title: Aside Legs Sticky Pattern
 * Description: Sticky aside block with image
 * Slug: amnesty/aside-legs-sticky
 * Inserter: no
 */

$image_url = get_template_directory_uri() . '/assets/images/testator-relations-officers.png';
$icon_phone_url = get_template_directory_uri() . '/assets/images/icon-phone.svg';

?>

<aside class="page-legs-aside">
  <div class="sticky-card">
    <div class="sticky-card-image-container">
      <img class="sticky-card-image" src="<?php echo esc_url($image_url); ?>" alt="" />
    </div>
    <div class="sticky-card-content">
      <div class="officers">
        <p class="names">Sophie ROUPPERT et Lisa LACOSTE</p>
        <p class="role">Chargées de relations testateurs</p>
      </div>
      <div class="phone-container">
        <div class="icon-container">
          <img src="<?php echo esc_url($icon_phone_url); ?>" alt=""/>
        </div>
        <p class="phone">01 53 38 66 24</p>
      </div>
      <a href="#legs-form" class="button">
        <div class="icon-container">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M16.172 11L10.808 5.63605L12.222 4.22205L20 12L12.222 19.778L10.808 18.364L16.172 13H4V11H16.172Z" fill="black"/>
          </svg>
        </div>
        <p class="label">Demander notre brochure</p>
      </a>
  </div>
</aside>
