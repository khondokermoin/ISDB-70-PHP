<?php
// app/models/User.php

class User
{
    private $conn;
    private $table = 'users';

    // ডাটাবেস কানেকশন রিসিভ করার জন্য Constructor
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ১. ইউজারনেম দিয়ে ইউজারের সমস্ত ডাটা খুঁজে বের করার ফাংশন (লগিনের জন্য লাগবে)
    public function getUserByUsername($username)
    {
        $query = "SELECT id, username, password, role FROM " . $this->table . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);

        // সিকিউরিটির জন্য প্যারামিটার বাইন্ড করা
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        // ডাটা পেলে রিটার্ন করবে, না পেলে false রিটার্ন করবে
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ২. নতুন ইউজার/এডমিন তৈরি করার ফাংশন (setup_admin.php বা ইউজার ম্যানেজমেন্টের জন্য)
    public function createUser($username, $hashed_password, $role = 'admin')
    {
        $query = "INSERT INTO " . $this->table . " (username, password, role) VALUES (:username, :password, :role)";
        $stmt = $this->conn->prepare($query);

        try {
            $stmt->execute([
                ':username' => $username,
                ':password' => $hashed_password,
                ':role' => $role
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
