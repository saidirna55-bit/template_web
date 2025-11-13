<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi</title>
    <link rel="stylesheet" href="<?php echo $_ENV['APP_URL'] . '/assets/css/bootstrap-icons/font/bootstrap-icons.min.css'; ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo $_ENV['APP_URL'] . '/assets/css/auth.css'; ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo $_ENV['APP_URL'] . '/assets/css/bootstrap.min.css'; ?>" type="text/css">
</head>
<body style="
            background-image: url('<?php echo $_ENV['APP_URL'] . '/assets/img/bg/bg.jpg'; ?>');
            background-repeat: no-repeat;
            background-attachment: fixed; 
            background-size: 100% 100%;">
    <div class="login-box">
        <h2><a><i class="bi bi-box-arrow-in-right ml-2 text-warning"> </i></a>Halaman Pendaftaran</h2>
        <?php 
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            $type = $_SESSION['flash_message_type'] ?? 'info';
            // Tampilkan pesan di sini, misalnya dengan Bootstrap alert
            echo '<div class="bg-dark text-center text-success alert alert-' . $type . '">' . $message . '</div>';
            
            // Hapus pesan dari session setelah ditampilkan
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_message_type']);
        }
        ?>
        <form action="register" method="post">
            <div class="user-box">
                <input type="text" name="name" id="name" required>
                <label><i class="bi bi-person-badge-fill"></i> Nama</label>
            </div>
            <div class="user-box">
                <input type="email" name="email" id="email" required>
                <label><i class="bi bi-envelope-at-fill ml-5"></i> Email</label>
            </div>
            <div class="user-box">
                <input type="password" name="password" id="password" required>
                <label><i class="bi bi-key-fill ml-5"></i> Password</label>
            </div>
                <div class="user-box">
                    <input type="password" name="password_confirmation" id="password_confirmation" required>
                    <label><i class="bi bi-key-fill ml-5"></i> Konfirmasi Password</label>
                </div>
            <button class="btn btn-dark" type="submit">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                Daftar
            </button>
        </form>
        <div class="text-center text-white my-3">
            ATAU
        </div>
        <a href="<?php echo $_ENV['APP_URL'] . '/auth/google'; ?>" class="btn btn-danger d-block">
            <i class="bi bi-google"></i> Daftar dengan Google
        </a>
        <p class="mx-auto text-center text-white">Sudah punya akun? 
            <a class="" href="<?php echo $_ENV['APP_URL'] . '/login'; ?>">
                Masuk di sini
            </a>
        </p>
    </div>
</body>
</html>