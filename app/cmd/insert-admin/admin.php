<?php

use App\Library\Encryption;

$admin = array(
    'created_at' => date('Y-m-d H:i:s'),
    'email' => 'admin@example.com',
	'password' => Encryption::hash('admin'), // Must be changed
	'name' => 'Admin Demo',
	'role' => 'admin',
    'banned' => false
);