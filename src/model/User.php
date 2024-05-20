<?php

namespace src\model;

class User {
    private $db;
    private $table = 'Course';

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findByUsernameAndPassword($username, $password) {
        $password = $password ? md5($password) : md5('default_password');
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function insert($course_code, $username, $password) {
        $password = $password ? md5($password) : md5('default_password');
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (course_code, username, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $course_code, $username, $password);
        $stmt->execute();
        return $this->db->insert_id();
    }

    public function update($id, $password) {
        $password = $password ? md5($password) : md5('default_password');
        $stmt = $this->db->prepare("UPDATE {$this->table} SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $password, $id);
        $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    public function fetchAllCourses() {
        return $this->db->query("SELECT * FROM {$this->table} ORDER BY id DESC");
    }
    
}

?>
