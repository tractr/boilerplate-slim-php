<?php

/**
 * --------------------------
 * CONFIGURATION
 * --------------------------
 * This file will contain the application's configuration.
 * 
 */


/**
 * --------------------------
 * DISPLAY ERROR
 * --------------------------
 * Turn this on in development mode to get information about errors (without it, Slim will at least log errors so if you’re using the built in PHP webserver then you’ll see them in the console output which is helpful)
 */
$config['displayErrorDetails'] = true;


/**
 * --------------------------
 * CONTENT LENGTH
 * --------------------------
 * Turn this on in development mode to get information about errors (without it, Slim will at least log errors so if you’re using the built in PHP webserver then you’ll see them in the console output which is helpful)
 */
$config['addContentLengthHeader'] = false;


/**
 * --------------------------
 * PASSWORD SALT
 * --------------------------
 * Used to salt password encryption
 */
$config['encryptionSalt'] = 'lkO4qe1Xd2SQKlnwsH41zkfNVGj1FYte2GVsoAISK4VWb3Qc8M6fKfKw29xIYbn3';
