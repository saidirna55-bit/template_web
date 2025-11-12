<?php

// Memuat autoloader Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Memuat variabel environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use app\Core\Router;
use app\Core\Request;

// Mulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi Router
$router = new Router();

// Daftarkan route aplikasi Anda di sini
$router->get('', 'HomeController@index');
$router->get('login', 'AuthController@showLoginForm');
$router->post('login', 'AuthController@login');
$router->get('register', 'AuthController@showRegistrationForm');
$router->post('register', 'AuthController@register');
$router->get('logout', 'AuthController@logout');
$router->get('verify', 'AuthController@verifyEmail');

// Middleware untuk proteksi halaman berdasarkan role
$protectedRoutes = [
    'dashboard' => ['administrator', 'crew', 'member'],
    'admin/users' => ['administrator']
];

$uri = Request::uri();

if (isset($protectedRoutes[$uri])) {
    if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], $protectedRoutes[$uri])) {
        // Redirect ke halaman login jika tidak terautentikasi atau tidak punya hak akses
        // PERUBAHAN: Hapus /public dari URL redirect
        header('Location: ' . $_ENV['APP_URL'] . '/login');
        exit;
    }
}


// Dispatch request ke controller yang sesuai
$router->dispatch($uri, Request::method());