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
}