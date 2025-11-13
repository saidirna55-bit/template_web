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
// =================== TAMBAHKAN KODE DEBUG DI BAWAH INI ===================
//echo "<pre>";
//echo "<b>DEBUGGING INFO (ATTEMPT 2):</b>\n";
//echo "--------------------\n";
//echo "<b>APP_URL from .env:</b> " . ($_ENV['APP_URL'] ?? 'NOT SET') . "\n";
//echo "<b>Request URI from \$_SERVER:</b> " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "\n";

// Panggil metode uri() untuk mendapatkan URI yang diproses
//$uri_from_request_class = app\Core\Request::uri();
//echo "<b>Processed URI from Request class:</b> '" . //$uri_from_request_class . "'\n";
//echo "--------------------";
//echo "</pre>";
//die("--- END OF DEBUG ---");
// =================== AKHIR DARI KODE DEBUG ===================

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

// Rute untuk Google OAuth
$router->get('auth/google', 'AuthController@redirectToGoogle');
$router->get('auth/google/callback', 'AuthController@handleGoogleCallback');

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