<?php

namespace App\Library;

class Encryption
{
    /**
     * Encrypt a password
     *
     * @param string $password
     * @return string
     */
    public static function hash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ["cost" => 10]);
    }

    /**
     * Test a password
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function test($password, $hash)
    {
        return password_verify($password, $hash);
    }
}