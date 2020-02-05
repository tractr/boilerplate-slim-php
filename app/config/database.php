<?php

/**
 * --------------------------
 * DATABASE CONNECTIVITY CONFIGURATION
 * --------------------------
 * This file will contain the settings needs to access your database.
 *
 * --------------------------
 * EXPLANATION OF VARIABLE
 * --------------------------
 *
 * [host] : The hostname of your database
 * [user] : The user name used to connect to your database
 * [password] : The password used to connect to your database
 * [dbname] : The name of your database
 *
 */

$config['db']['driver'] = 'mysql';
$config['db']['host'] = 'mysql';
$config['db']['database'] = 'api';
$config['db']['username'] = 'api_user';
$config['db']['password'] = 'api_pass';
$config['db']['charset'] = 'utf8';
$config['db']['collation'] = 'utf8_unicode_ci';
$config['db']['engine'] = 'InnoDB';
$config['db']['prefix'] = '';
