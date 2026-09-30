<?php

declare(strict_types=1);
/*
 * Le bug est lié au plugin Prevent Direct Access.
 * Au chargement de chaque page, il compare l'option 'updated_htaccess_success' avec true.
 * Sauf que l'option a comme valeur '1' qui n'est pas un booléen.
 * Donc le plugin régénère les règles de réécriture à chaque chargement de page.
 * Ce qui cause de la latence notamment sur les pages d'admin.
 * Ce filtre pourra être supprimé si le plugin ne force plus le typage de l'option à un booléen.
 */
add_filter('option_updated_htaccess_success', fn ($value) => $value ? true : $value);
