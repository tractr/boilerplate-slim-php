<?php

require_once __DIR__ . '/../../../public/initialize.php';
require_once __DIR__ . '/../inc/helper.php';
require_once __DIR__ . '/admin.php';

if (App\Models\User::where('email', $admin['email'])->first()) {
    Helpers::output('', 'Admin already exists. Skip creation.');
    exit();
}

App\Models\User::create($admin);

Helpers::output('', "Admin {$admin['email']} created.");