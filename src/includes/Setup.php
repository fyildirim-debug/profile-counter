<?php
class Setup {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function isRequired() {
        try {
            // Admin users tablosunu kontrol et
            $stmt = $this->db->query("SELECT COUNT(*) FROM admin_users");
            $count = $stmt->fetchColumn();
            return $count == 0;
        } catch (PDOException $e) {
            // Tablo yoksa kurulum gerekli
            return true;
        }
    }

    public function createTables() {
        try {
            // Counters tablosu
            $this->db->exec("CREATE TABLE IF NOT EXISTS counters (
                id INT AUTO_INCREMENT PRIMARY KEY,
                counter_name VARCHAR(100),
                counter_key VARCHAR(32) UNIQUE,
                counter_type VARCHAR(10),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            // Visitors tablosu
            $this->db->exec("CREATE TABLE IF NOT EXISTS visitors (
                id INT AUTO_INCREMENT PRIMARY KEY,
                counter_id INT,
                ip_address VARCHAR(45),
                user_agent VARCHAR(255),
                visit_time DATETIME,
                FOREIGN KEY (counter_id) REFERENCES counters(id)
            )");

            // Admin users tablosu
            $this->db->exec("CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE,
                password VARCHAR(255),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function createAdminUser($username, $password) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
            return $stmt->execute([$username, $hashedPassword]);
        } catch (PDOException $e) {
            return false;
        }
    }
}