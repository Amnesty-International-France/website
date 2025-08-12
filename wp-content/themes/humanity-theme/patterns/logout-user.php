<?php

/**
 * Title: Logout
 * Description: Se deconnecter
 * Slug: amnesty/logout-user
 * Inserter: no
 */

wp_logout();

wp_redirect(get_permalink(get_page_by_path('connectez-vous')));
exit;

