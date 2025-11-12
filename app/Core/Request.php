<?php

namespace app\Core;

class Request
{
    public static function uri()
    {
        return trim(parse_url($_GET['url'] ?? '', PHP_URL_PATH), '/');
    }

    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public static function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }
}