<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
        <h2><a><i class="bi bi-box-arrow-in-right ml-2 text-warning"> </i></a>Halaman Login</h2>
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
        <br>
        <form action="login" method="post">
            <div class="user-box">
                <input type="email" name="email" id="email" required>
                <label><i class="bi bi-envelope-at-fill ml-5"></i> Email</label>
            </div>
            <div class="user-box">
                <input type="password" name="password" id="password" required>
                <label><i class="bi bi-key-fill ml-5"></i> Password</label>
            </div>
            <button class="btn btn-dark" type="submit">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                Submit
            </button>
        </form>
        <div class="text-center text-white my-3">
            ATAU
        </div>
        <a href="<?php echo $_ENV['APP_URL'] . '/auth/google'; ?>" class="btn btn-danger d-block">
            <i class="bi bi-google"></i> Masuk dengan Google
        </a>
        <p class="mx-auto text-center text-white">Belum punya akun? 
            <a class="" href="<?php echo $_ENV['APP_URL'] . '/register'; ?>">
                Buat akun
            </a>
        </p>
    </div>
</body>
</html>

