<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h1>Selamat Datang, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>!</h1>
    <p>Anda berhasil login sebagai <?php echo htmlspecialchars($_SESSION['user']['role']); ?>.</p>
    
    <?php if ($_SESSION['user']['role'] === 'administrator'): ?>
        <p>Anda memiliki akses administrator.</p>
        <!-- Tambahkan link atau menu khusus admin di sini -->
    <?php endif; ?>

    <a href="logout">Logout</a>
</body>
</html>