<?php
return [
    'host' => getenv('DB_HOST') ?: 'db',
    'user' => getenv('DB_USER') ?: 'admin',
    'pass' => getenv('DB_PASS') ?: 'tienda',
    'db'   => getenv('DB_NAME') ?: 'sistema_stock',
];
