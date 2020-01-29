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
        return md5($password);
    }

    /**
     * Test a password
     *
     * @param $password string
     * @param $encrypted string
     * @return bool
     */
    public static function test($password, $encrypted) {
        return md5($password) === $encrypted;
    }
}