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

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function create(array $data)
    {
         // Atur password menjadi null jika tidak disediakan (untuk login Google)
        $password = isset($data['password']) ? password_hash($data['password'], PASSWORD_BCRYPT) : null;

        // PERBAIKAN: Tambahkan `google_id` ke dalam query SQL
        $sql = "INSERT INTO users (name, email, password, role, verification_token, status, google_id) VALUES (:name, :email, :password, :role, :verification_token, :status, :google_id)";
        $stmt = $this->db->prepare($sql);
        
        // PERBAIKAN: Gunakan variabel $password yang sudah diproses
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => $password, // Gunakan variabel ini
            ':role' => $data['role'] ?? 'member',
            ':verification_token' => $data['verification_token'] ?? null,
            ':status' => $data['status'] ?? 'pending',
            ':google_id' => $data['google_id'] ?? null
        ]);

        return $this->db->lastInsertId();
    }


    public function update($id, array $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $fieldString = implode(', ', $fields);

        $sql = "UPDATE users SET {$fieldString} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $data['id'] = $id;

        return $stmt->execute($data);
    }

    public function activateUser($token)
    {
        $sql = "UPDATE users SET status = 'active', verification_token = NULL WHERE verification_token = :token";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':token' => $token]);
    }
}