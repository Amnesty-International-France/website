<?php

wp_logout();

wp_redirect(get_permalink(get_page_by_path('connectez-vous')));
exit;

