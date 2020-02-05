<?php

class Helpers
{

    public static function output($type = 'success', $output = null)
    {
        if ($type == 'success') {
            echo "\033[32m" . $output . "\033[0m" . PHP_EOL;
        } elseif ($type == 'warning') {
            echo "\033[33m" . $output . "\033[0m" . PHP_EOL;
        } elseif ($type == 'error') {
            echo "\033[31m" . $output . "\033[0m" . PHP_EOL;
        } elseif ($type == 'fatal') {
            echo "\033[31m" . $output . "\033[0m" . PHP_EOL;
            exit(1);
        } else {
            echo $output . PHP_EOL;
        }
    }
}