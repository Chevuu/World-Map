<?php

namespace src\model;

class User {
    private $db;
    private $table = 'mooc_course'; // Assuming your user data is in the 'mooc_course' table

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByUsernameAndPassword($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user = ? AND password = ?");
        $stmt->execute([$username, md5($password)]);
        return $stmt->fetch();
    }

    public function insert($user, $password) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (user, password) VALUES (?, ?)");
        $stmt->execute([$user, md5($password)]);
        return $this->db->lastInsertId();
    }

    public function update($id, $password) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET password = ? WHERE id = ?");
        $stmt->execute([md5($password), $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
    }
}
?>
