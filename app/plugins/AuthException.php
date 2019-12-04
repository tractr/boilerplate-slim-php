<?php 

class AuthException extends Exception {
    public function __construct() {
        $this->message = 'You must authenticate to access this resource.';
        $this->code = 401;
    }
}