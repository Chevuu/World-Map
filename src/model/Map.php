<?php

namespace src\model;

class Map {
    private $db;
    private $table = 'map';

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function insertMap($courseCode, $courseRun, $mapName, $editable, $visible) {
        $query = $this->db->prepare("INSERT INTO {$this->table} (course_code, course_run, map_name, editable, visible) VALUES (?, ?, ?, ?, ?)");
        $query->bind_param("sssii", $courseCode, $courseRun, $mapName, $editable, $visible);
        $query->execute();
    }

    public function fetchAllCourses() {
        $query = "SELECT * FROM {$this->table} ORDER BY id DESC";
        return $this->db->query($query);
    }

    public function fetchMapsByCourseCode($courseCode) {
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE course_code = ? ORDER BY id DESC");
        $query->bind_param("s", $courseCode);
        $query->execute();
        return $query->get_result();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function updateMap($id, $name, $editable, $visible) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET map_name = ?, editable = ?, visible = ? WHERE id = ?");
        $stmt->bind_param("siii", $name, $editable, $visible, $id);
        return $stmt->execute();
    }

    // TODO: Refactor
    public function fetchMapEntries($mapId) {
        $query = $this->db->prepare("SELECT * FROM mooc_map_entry WHERE map_id = ?");
        $query->bind_param("i", $mapId);
        $query->execute();
        return $query->get_result();
    }
}
?>
