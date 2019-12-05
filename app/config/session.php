<?php

/**
 * --------------------------
 * SESSION
 * --------------------------
 * This file will contain the application's session default configuration.
 * 
 */


/**
 * --------------------------
 * COOKIE NAME
 * --------------------------
 * 
 */
$config['cookie']['name'] = 'sid-api';


/**
 * --------------------------
 * EXPIRE
 * --------------------------
 * Time before cookie expire. In this case, it will take one year from now
 */
$config['cookie']['expire'] = 365 * 24 * 3600;

/**
 * --------------------------
 * EXPIRE
 * --------------------------
 * Time before cookie expire. In this case, it will take one year from now
 */
$config['cookie']['path'] = '/';

//Todo : complete all session params