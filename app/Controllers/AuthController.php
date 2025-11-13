<?php

namespace app\Controllers;

use app\Models\UserModel;
use app\Core\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Google daftar dan login
use Google_Client;
use Google_Service_Oauth2;
// Facebook login dan daftar
use League\OAuth2\Client\Provider\Facebook;

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
            //echo "Password dan konfirmasi password tidak cocok.";
            //return;
            $_SESSION['flash_message'] = 'Password konfirmasi tidak sama.';
            $_SESSION['flash_message_type'] = "danger";
            header('Location: ' . $_ENV['APP_URL'] . '/register');
            exit;
        }

        if ($this->userModel->findByEmail($email)) {
            // Handle user already exists
            //echo "Email sudah terdaftar.";
            //return;
            $_SESSION['flash_message'] = 'Email sudah terdaftar.';
            $_SESSION['flash_message_type'] = "danger";
            header('Location: ' . $_ENV['APP_URL'] . '/register');
            exit;
        }

        $verification_token = bin2hex(random_bytes(32));

        $this->userModel->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'verification_token' => $verification_token
        ]);

        $this->sendVerificationEmail($email, $verification_token);

        //echo "Registrasi berhasil. Silakan cek email Anda untuk verifikasi.";
        $_SESSION['flash_message'] = 'Pendaftaran berhasil, cek email untuk verifikasi akun.';
        $_SESSION['flash_message_type'] = "Success";
        header('Location: ' . $_ENV['APP_URL'] . '/login');
        exit;
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

        //echo "Email atau password salah.";
        $_SESSION['flash_message'] = 'Email atau password salah.';
        $_SESSION['flash_message_type'] = "danger";
        header('Location: ' . $_ENV['APP_URL'] . '/login');
        exit;
    }

    public function logout()
    {
        session_destroy();
        // PERUBAHAN: Redirect ke root URL, bukan /public/login
        $_SESSION['flash_message'] = 'Anda berhasil keluar.';
        $_SESSION['flash_message_type'] = "success";
        header('Location: ' . $_ENV['APP_URL'] . '/login');
        exit;
    }

    public function redirectToGoogle()
    {
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['APP_URL'] . '/auth/google/callback');
        $client->addScope("email");
        $client->addScope("profile");

        header('Location: ' . $client->createAuthUrl());
        exit;
    }

    public function handleGoogleCallback()
    {
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['APP_URL'] . '/auth/google/callback');

        if (isset($_GET['code'])) {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            if (isset($token['error'])) {
                // Handle error
                $_SESSION['flash_message'] = 'Gagal mengautentikasi dengan Google.';
                $_SESSION['flash_message_type'] = 'danger';
                header('Location: ' . $_ENV['APP_URL'] . '/login');
                exit;
            }
            $client->setAccessToken($token);

            $google_oauth = new Google_Service_Oauth2($client);
            $google_account_info = $google_oauth->userinfo->get();

            $user = $this->userModel->findByEmail($google_account_info->email);

            if ($user) {
                // User exists, log them in
                if (empty($user['google_id'])) {
                    $this->userModel->update($user['id'], ['google_id' => $google_account_info->id]);
                }
            } else {
                // User does not exist, create a new one
                $userId = $this->userModel->create([
                    'name' => $google_account_info->name,
                    'email' => $google_account_info->email,
                    'google_id' => $google_account_info->id,
                    'status' => 'active' // Email from Google is already verified
                ]);
                $user = $this->userModel->find($userId);
            }

            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $user['role']
            ];
            header('Location: ' . $_ENV['APP_URL']); // Redirect to dashboard
            exit;
        }

        // If no code, redirect to login
        header('Location: ' . $_ENV['APP_URL'] . '/login');
        exit;
    }

    //facebook login
    public function facebookRedirect()
    {
        session_start();
        $provider = new Facebook([
            'clientId'          => $_ENV['FACEBOOK_APP_ID'],
            'clientSecret'      => $_ENV['FACEBOOK_APP_SECRET'],
            'redirectUri'       => $_ENV['APP_URL'] . '/auth/facebook/callback',
            'graphApiVersion'   => 'v18.0', // Gunakan versi Graph API yang valid
        ]);

        // Hasilkan URL otorisasi dan alihkan pengguna
        $authUrl = $provider->getAuthorizationUrl([
            'scope' => ['email', 'public_profile'],
        ]);
        $_SESSION['oauth2state'] = $provider->getState();
        
        header('Location: ' . $authUrl);
        exit;
    }

    // GANTI METODE LAMA DENGAN INI
    public function facebookCallback()
    {
        session_start();
        $provider = new Facebook([
            'clientId'          => $_ENV['FACEBOOK_APP_ID'],
            'clientSecret'      => $_ENV['FACEBOOK_APP_SECRET'],
            'redirectUri'       => $_ENV['APP_URL'] . '/auth/facebook/callback',
            'graphApiVersion'   => 'v18.0',
        ]);

        // Validasi state untuk mencegah serangan CSRF
        if (empty($_GET['state']) || !isset($_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state']) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        }

        try {
            // Dapatkan access token dari Facebook menggunakan kode otorisasi
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            // Dapatkan detail pengguna dari Facebook
            $ownerDetails = $provider->getResourceOwner($accessToken);
            
            $userModel = new \app\Models\UserModel();
            $email = $ownerDetails->getEmail();
            
            // Cek apakah pengguna sudah ada di database
            $user = $userModel->findByEmail($email);

            if ($user) {
                // Jika ada, loginkan
                $_SESSION['user_id'] = $user['id'];
            } else {
                // Jika tidak ada, buat pengguna baru
                $newUserId = $userModel->create([
                    'name' => $ownerDetails->getName(),
                    'email' => $email,
                    'password' => null,
                    'google_id' => null,
                    'facebook_id' => $ownerDetails->getId(),
                ]);
                $_SESSION['user_id'] = $newUserId;
            }

            header('Location: /'); // Arahkan ke halaman utama
            exit();

        } catch (\League\OAuth2\Client\Provider\Exception\FacebookProviderException $e) {
            exit('Facebook Provider Exception: ' . $e->getMessage());
        } catch (\Exception $e) {
            exit('Something went wrong: ' . $e->getMessage());
        }
    }

    public function verifyEmail()
    {
        session_start();
        $token = Request::get('token');
        if ($this->userModel->activateUser($token)) {
            // Redirect to login page after successful verification
            $_SESSION['flash_message'] = "Pendaftaran berhasil!";
            $_SESSION['flash_message_type'] = "success";
            header('Location: ' . $_ENV['APP_URL'] . '/login');
            exit;
        } else {
            $_SESSION['flash_message'] = 'Token tidak valid.';
            $_SESSION['flash_message_type'] = "danger";
            header('Location: ' . $_ENV['APP_URL'] . '/login');
            exit;
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
            //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $_SESSION['flash_message'] = 'Email verifikasi gagal. Harap hubungi admin.';
            $_SESSION['flash_message_type'] = "danger";
            header('Location: ' . $_ENV['APP_URL'] . '/register');
            exit;
        }
    }
}