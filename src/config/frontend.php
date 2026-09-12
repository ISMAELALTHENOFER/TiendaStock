<?php

return [
    'driver' => env('FRONTEND_DRIVER', 'react'),
    'routes' => [
        'dashboard' => env('FRONTEND_DASHBOARD_DRIVER', 'react'),
        'ventas.index' => env('FRONTEND_VENTAS_INDEX_DRIVER', 'react'),
        'ventas.pos' => env('FRONTEND_VENTAS_POS_DRIVER', 'react'),
        'ventas.show' => env('FRONTEND_VENTAS_SHOW_DRIVER', 'react'),
        'productos.index' => env('FRONTEND_PRODUCTOS_INDEX_DRIVER', 'react'),
        'productos.create' => env('FRONTEND_PRODUCTOS_CREATE_DRIVER', 'react'),
        'productos.edit' => env('FRONTEND_PRODUCTOS_EDIT_DRIVER', 'react'),
        'categorias.index' => env('FRONTEND_CATEGORIAS_INDEX_DRIVER', 'react'),
        'admin.users.index' => env('FRONTEND_ADMIN_USERS_INDEX_DRIVER', 'react'),
        'admin.users.create' => env('FRONTEND_ADMIN_USERS_CREATE_DRIVER', 'react'),
        'admin.users.edit' => env('FRONTEND_ADMIN_USERS_EDIT_DRIVER', 'react'),
    ],
];
