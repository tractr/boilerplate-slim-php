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
 * The name of the cookie.
 */
$config['cookie']['name'] = 'sid-api';


/**
 * --------------------------
 * EXPIRE
 * --------------------------
 * Time before cookie expire.
 */
$config['cookie']['expire'] = 3 * 24 * 3600; // 3 Days in seconds

/**
 * --------------------------
 * PATH
 * --------------------------
 * The path on the server in which the cookie will be available on.
 */
$config['cookie']['path'] = '/';

/**
 * --------------------------
 * DOMAIN
 * --------------------------
 * The (sub)domain that the cookie is available to.
 */
$config['cookie']['domain'] = '';

/**
 * --------------------------
 * SECURE
 * --------------------------
 * Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client.
 */
$config['cookie']['secure'] = false;

/**
 * --------------------------
 * HTTP ONLY
 * --------------------------
 * When TRUE the cookie will be made accessible only through the HTTP protocol.
 */
$config['cookie']['httponly'] = false;

/**
 * --------------------------
 * OPTIONS
 * --------------------------
 * An associative array which may have any of the keys expires, path, domain, secure, httponly and samesite.
 */
$config['cookie']['options'] = null;