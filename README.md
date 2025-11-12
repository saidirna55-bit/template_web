# Template Aplikasi Web PHP Vanilla

Ini adalah template dasar untuk aplikasi web PHP vanilla dengan fungsionalitas multi-user (administrator, crew, member), sistem registrasi dengan verifikasi email, dan proteksi halaman berdasarkan peran (role).

Proyek ini menggunakan Composer untuk manajemen dependensi dan mengikuti pola desain yang terinspirasi dari MVC (Model-View-Controller).

## Prasyarat

1.  PHP 8.2.12 atau lebih baru.
2.  Composer.
3.  Web server (seperti XAMPP/WAMP) dengan `mod_rewrite` aktif.
4.  Database MySQL.

## Instalasi

1.  **Clone/Unduh Proyek**: Letakkan file proyek di direktori `htdocs` Anda.
2.  **Buat Virtual Host**: Untuk pengalaman terbaik, konfigurasikan Virtual Host di Apache yang mengarah ke direktori `public/` proyek ini. Contohnya, `template-web.test`.
3.  **Update File `hosts`**: Tambahkan `127.0.0.1 template-web.test` ke file `hosts` Anda.
4.  **Buat File `.env`**: Salin `.env.example` menjadi `.env` dan isi konfigurasi database serta PHPMailer Anda. Pastikan `APP_URL` sesuai dengan Virtual Host yang Anda buat (misal: `http://template-web.test`).
5.  **Install Dependensi**: Buka terminal di direktori root proyek dan jalankan `composer install`.
6.  **Impor Database**: Impor file `database/template_web.sql` ke database MySQL Anda.
7.  **Restart Apache**: Pastikan untuk me-restart Apache setelah mengubah konfigurasi.
8.  **Akses Aplikasi**: Buka URL Virtual Host Anda di browser (misal: `http://template-web.test`).

---

## Menambahkan Halaman/Fitur Baru

Untuk menambahkan halaman baru yang dapat diakses melalui URL, ikuti tiga langkah utama berikut. Sebagai contoh, kita akan membuat halaman "Tentang Kami" yang dapat diakses di `http://template-web.test/about`.

### Langkah 1: Buat File View

Buat file PHP baru yang akan berisi konten HTML dari halaman Anda.

1.  Buat file baru di dalam direktori `views/`.
    - **Contoh**: `d:\xamppv8212\htdocs\new_project\template_web\views\about.php`

2.  Isi file tersebut dengan konten HTML Anda.
    ```php
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Tentang Kami</title>
    </head>
    <body>
        <h1>Tentang Aplikasi Ini</h1>
        <p>Ini adalah aplikasi web yang dibuat dengan PHP vanilla.</p>
        <a href="<?php echo $_ENV['APP_URL']; ?>">Kembali ke Home</a>
    </body>
    </html>
    ```

### Langkah 2: Buat Metode di Controller

Buat sebuah fungsi (metode) di dalam salah satu controller yang ada untuk menampilkan view yang baru saja Anda buat. Untuk halaman statis seperti "Tentang Kami", `HomeController` adalah pilihan yang baik.

1.  Buka file controller yang sesuai.
    - **Contoh**: `d:\xamppv8212\htdocs\new_project\template_web\app\Controllers\HomeController.php`

2.  Tambahkan metode baru untuk me-render view Anda.
    ```php
    <?php

    namespace app\Controllers;

    class HomeController
    {
        public function index()
        {
            if (isset($_SESSION['user'])) {
                require_once __DIR__ . '/../../views/dashboard.php';
            } else {
                require_once __DIR__ . '/../../views/home.php';
            }
        }

        // TAMBAHKAN METODE BARU DI SINI
        public function about()
        {
            require_once __DIR__ . '/../../views/about.php';
        }
    }
    ```

### Langkah 3: Daftarkan Route Baru

Langkah terakhir adalah memberitahu aplikasi URL mana yang akan memicu metode controller yang baru Anda buat.

1.  Buka file `public/index.php`.
2.  Di bawah inisialisasi `$router`, tambahkan route baru menggunakan metode `get()` atau `post()`.

    ```php
    // ... kode yang sudah ada ...
    $router = new Router();

    // Daftarkan route aplikasi Anda di sini
    $router->get('', 'HomeController@index');
    $router->get('login', 'AuthController@showLoginForm');
    // ... route lain yang sudah ada ...

    // TAMBAHKAN ROUTE BARU ANDA DI SINI
    $router->get('about', 'HomeController@about');

    // Middleware untuk proteksi halaman berdasarkan role
    // ...
    ```

Sekarang, jika Anda membuka `http://template-web.test/about` di browser, Anda akan melihat halaman "Tentang Kami" yang baru.

---

## Melindungi Halaman Baru (Membutuhkan Login)

Jika Anda ingin halaman baru hanya bisa diakses oleh pengguna yang sudah login, Anda perlu mendaftarkannya ke dalam *middleware* proteksi.

1.  Buka file `public/index.php`.
2.  Cari array `$protectedRoutes`.
3.  Tambahkan URL halaman baru Anda sebagai *key* dan daftar peran (role) yang diizinkan sebagai *value*.

    ```php
    // ...
    // Middleware untuk proteksi halaman berdasarkan role
    $protectedRoutes = [
        'dashboard' => ['administrator', 'crew', 'member'],
        'admin/users' => ['administrator'],
        // TAMBAHKAN HALAMAN BARU ANDA DI SINI JIKA PERLU LOGIN
        'about' => ['administrator', 'crew', 'member'] // Contoh: semua role bisa akses
    ];
    // ...
    ```

Dengan konfigurasi di atas, jika pengguna yang belum login mencoba mengakses `http://template-web.test/about`, mereka akan otomatis dialihkan ke halaman login.