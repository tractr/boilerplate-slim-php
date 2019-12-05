<?php 

/**
 * --------------------------
 * AuthException
 * --------------------------
 * Exception occured when an operation need to be autheticated
 */

class AuthException extends Exception {
	
    public function __construct() {
        $this->message = 'You must authenticate to access this resource.';
        $this->code = 402;
    }
}