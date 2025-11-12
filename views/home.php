<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Selamat Datang</title>
</head>
<body>
    <h1>Selamat Datang di Aplikasi Kami</h1>
    <p>
        <a href="<?php echo $_ENV['APP_URL'] . '/login'; ?>">Login</a> atau <a href="<?php echo $_ENV['APP_URL'] . '/register'; ?>">Registrasi</a>
    </p>
</body>
</html>