<?php

namespace src\controller;

class MapController {
    private $mapModel;
    private $userModel;

    public function __construct($mapModel, $userModel) {
        $this->mapModel = $mapModel;
        $this->userModel = $userModel;
    }

    public function addMap($courseCode, $courseRun, $editable, $visible) {
        $mapName = $courseCode . " " . $courseRun . " (" . date('M Y') . ")";
        $this->mapModel->insertMap($courseCode, $courseRun, $mapName, $editable, $visible);
        return '<div class="alert alert-success" role="alert"><b>Well done!</b> You successfully added a new map</div>';
    }

    public function getMapsByCourseCode($userId) {
        $courseCode = $this->userModel->findById($userId)->fetch_assoc()['course_code'];
        return $this->mapModel->fetchMapsByCourseCode($courseCode);
    }

    public function getMapEntries($mapId) {
        return $this->mapModel->fetchMapEntries($mapId);
    }

    public function getMapById($mapId) {
        return $this->mapModel->findById($mapId);
    }

    public function updateMap($mapId, $name, $editable, $visible) {
        return $this->mapModel->updateMap($mapId, $name, $editable, $visible);
    }
}
?>
