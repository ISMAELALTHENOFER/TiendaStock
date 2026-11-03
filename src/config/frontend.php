<?php

return [
    'driver' => env('FRONTEND_DRIVER', 'react'),
    'routes' => [
        'dashboard' => env('FRONTEND_DASHBOARD_DRIVER', 'react'),
        'login' => env('FRONTEND_LOGIN_DRIVER', 'react'),
        'profile' => [
            'edit' => env('FRONTEND_PROFILE_EDIT_DRIVER', 'react'),
        ],
        'password' => [
            'confirm' => env('FRONTEND_PASSWORD_CONFIRM_DRIVER', 'react'),
        ],
        'ventas' => [
            'index' => env('FRONTEND_VENTAS_INDEX_DRIVER', 'react'),
            'pos' => env('FRONTEND_VENTAS_POS_DRIVER', 'react'),
            'show' => env('FRONTEND_VENTAS_SHOW_DRIVER', 'react'),
        ],
        'productos' => [
            'index' => env('FRONTEND_PRODUCTOS_INDEX_DRIVER', 'react'),
            'create' => env('FRONTEND_PRODUCTOS_CREATE_DRIVER', 'react'),
            'edit' => env('FRONTEND_PRODUCTOS_EDIT_DRIVER', 'react'),
            'show' => env('FRONTEND_PRODUCTOS_SHOW_DRIVER', 'react'),
        ],
        'categorias' => [
            'index' => env('FRONTEND_CATEGORIAS_INDEX_DRIVER', 'react'),
            'show' => env('FRONTEND_CATEGORIAS_SHOW_DRIVER', 'react'),
        ],
        'admin' => [
            'users' => [
                'index' => env('FRONTEND_ADMIN_USERS_INDEX_DRIVER', 'react'),
                'create' => env('FRONTEND_ADMIN_USERS_CREATE_DRIVER', 'react'),
                'edit' => env('FRONTEND_ADMIN_USERS_EDIT_DRIVER', 'react'),
            ],
        ],
    ],
];
