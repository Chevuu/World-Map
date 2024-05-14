<?php

namespace src\model;

class Map {
    private $db;
    private $table = 'map';

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function fetchAllCourses() {
        $query = "SELECT * FROM {$this->table} ORDER BY id DESC";
        return $this->db->query($query);
    }
}
?>
