<?php

add_filter('jetpack_get_available_modules', function ($modules) {
    unset($modules['videopress']);

    return $modules;
}, 10, 1);
