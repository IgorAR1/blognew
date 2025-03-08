<?php


if (!function_exists('dd')) {
    function dd(...$values): never
    {
        echo '<pre>';
        foreach ($values as $value) {
            var_dump($value);
        }
        echo '</pre>';

        die();
    }
}