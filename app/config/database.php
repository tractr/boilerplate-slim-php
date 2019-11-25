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

$config['db']['driver']   = 'mysql';
$config['db']['host']   = 'localhost';
$config['db']['database']   = 'hapify';
$config['db']['username']   = 'root';
$config['db']['password']   = '';
$config['db']['charset']   = 'utf8';
$config['db']['collation']   = 'utf8_unicode_ci';
$config['db']['prefix']   = '';