<?php

declare(strict_types=1);

add_filter('option_updated_htaccess_success', fn ($value) => $value ? true : $value);
