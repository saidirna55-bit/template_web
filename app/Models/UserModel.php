<?php

namespace app\Models;

use app\Core\Database;
use PDO;

class UserModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO users (name, email, password, role, verification_token, status) VALUES (:name, :email, :password, :role, :verification_token, :status)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':role' => $data['role'] ?? 'member',
            ':verification_token' => $data['verification_token'],
            ':status' => $data['status'] ?? 'pending'
        ]);
    }

    public function activateUser($token)
    {
        $sql = "UPDATE users SET status = 'active', verification_token = NULL WHERE verification_token = :token";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':token' => $token]);
    }
}