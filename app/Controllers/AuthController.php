<?php

namespace app\Controllers;

use app\Models\UserModel;
use app\Core\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function showLoginForm()
    {
        require_once __DIR__ . '/../../views/auth/login.php';
    }

    public function showRegistrationForm()
    {
        require_once __DIR__ . '/../../views/auth/register.php';
    }

    public function register()
    {
        $name = Request::post('name');
        $email = Request::post('email');
        $password = Request::post('password');
        $password_confirmation = Request::post('password_confirmation');

        if ($password !== $password_confirmation) {
            // Handle password mismatch
            echo "Password dan konfirmasi password tidak cocok.";
            return;
        }

        if ($this->userModel->findByEmail($email)) {
            // Handle user already exists
            echo "Email sudah terdaftar.";
            return;
        }

        $verification_token = bin2hex(random_bytes(32));

        $this->userModel->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'verification_token' => $verification_token
        ]);

        $this->sendVerificationEmail($email, $verification_token);

        echo "Registrasi berhasil. Silakan cek email Anda untuk verifikasi.";
    }

    public function login()
    {
        $email = Request::post('email');
        $password = Request::post('password');

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                echo "Akun Anda belum aktif. Silakan verifikasi email Anda.";
                return;
            }
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $user['role']
            ];
            // PERUBAHAN: Redirect ke root URL, bukan /public/dashboard
            header('Location: ' . $_ENV['APP_URL']);
            exit;
        }

        echo "Email atau password salah.";
    }

    public function logout()
    {
        session_destroy();
        // PERUBAHAN: Redirect ke root URL, bukan /public/login
        header('Location: ' . $_ENV['APP_URL'] . '/login');
        exit;
    }

    public function verifyEmail()
    {
        $token = Request::get('token');
        if ($this->userModel->activateUser($token)) {
            echo "Verifikasi email berhasil. Anda sekarang bisa login.";
        } else {
            echo "Token verifikasi tidak valid.";
        }
    }

     private function sendVerificationEmail($email, $token)
    {
        $mail = new PHPMailer(true);
        // PERUBAHAN: Hapus /public dari URL verifikasi
        $verification_link = $_ENV['APP_URL'] . '/verify?token=' . $token;

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['MAIL_PORT'];

            //Recipients
            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($email);

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Verifikasi Akun Anda';
            $mail->Body    = "Silakan klik link berikut untuk memverifikasi akun Anda: <a href='{$verification_link}'>Verifikasi</a>";

            $mail->send();
        } catch (Exception $e) {
            // Handle error, for now just echo it
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}