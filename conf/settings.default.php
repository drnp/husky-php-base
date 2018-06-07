<?php
// Default settings for husky
\define('IN_HUSKY', true);
\define('DEFAULT_APP_ENV', 'dev');
\define('DEFAULT_CONTENT_TYPE', 'application/json');

$app_env = \getenv('HUSKY_ENV');
if (!$app_env)
{
    $app_env = \DEFAULT_APP_ENV;
}

return [
    'app' => [
        'name' => 'Husky.App',
        'enable_ssl' => false,
        'base_url' => 'localhost',
        'enable_debug' => false,
    ],
    'runtime' => [
        'dependencies' => [
        ],
        'middlewares' => [
            'Authorization' => [
            ],
            'Version' => [
                'default_version' => 'v1',
            ],
            'Result' => [
                'enable_envelope' => true,
            ],
            'Cors' => [
            ],
        ],
    ]
];
