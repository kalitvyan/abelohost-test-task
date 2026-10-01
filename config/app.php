<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'name'  => Env::get('APP_NAME', 'Blog'),
    'env'   => Env::get('APP_ENV', 'prod'),
    'debug' => Env::bool('APP_DEBUG'),
];
