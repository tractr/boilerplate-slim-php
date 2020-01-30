<?php


namespace App\Library;


class Encryption
{
    /**
     * Encryption a password
     *
     * @param $password string
     * @return string
     */
    public static function hash($password) {
        global $config;
        return md5($config['encryptionSalt'].$password);
    }

    /**
     * Test a password
     *
     * @param $password string
     * @param $encrypted string
     * @return bool
     */
    public static function test($password, $encrypted) {
        return static::hash($password) === $encrypted;
    }
}