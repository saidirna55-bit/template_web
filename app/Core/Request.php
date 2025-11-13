<?php

namespace app\Core;

class Request
{
    public static function uri()
    {
        // Mendapatkan URI permintaan, misalnya: /template_web/public/login?foo=bar
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Mendapatkan path ke file index.php, misalnya: /template_web/public/index.php
        $scriptName = $_SERVER['SCRIPT_NAME'];

        // Mendapatkan direktori tempat index.php berada, misalnya: /template_web/public
        $basePath = dirname($scriptName);

        // Jika URI permintaan dimulai dengan base path, hapus base path tersebut
        // Ini akan mengubah /template_web/public/login menjadi /login
        if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
            $uri = substr($requestUri, strlen($basePath));
        } else {
            $uri = $requestUri;
        }

        // Mengembalikan URI yang bersih, misalnya: 'login'
        return trim($uri, '/');
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