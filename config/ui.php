<?php

return [

    'theme_toggle' => true, // enable dark/light mode toggle in sidebar
    'driver' => 'auto', // 'auto', 'laratrust', 'spatie', 'gate'
    'sidebar' => [

        [
            'type' => 'group',
            'text' => 'Platform',
            'items' => [
                [
                    'text' => 'Dashboard',
                    'url' => 'dashboard', // can be a route name or a full URL
                    'icon' => 'home', // flux icon 
                    'permission' => null, // Support single permission as string
                ],
                [
                    'text' => 'FontAwesome Icon',
                    'url' => 'users.index',
                    'icon' => 'fas fa-users', // fontawesome icon
                    'permission' => null,
                ],
            ],
        ],
        [
            'type' => 'group',
            'text' => 'Resources',
            'items' => [
                [
                    'type' => 'link',
                    'text' => 'External Docs',
                    'url'  => 'https://laravel.com',
                    'icon' => 'link',
                    'permission' => null,
                    'target' => '_blank',
                ],
            ]
        ],

    ],

];
