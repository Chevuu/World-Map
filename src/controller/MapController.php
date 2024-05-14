<?php

namespace src\controller;

class MapController {
    private $mapModel;

    public function __construct($mapModel) {
        $this->mapModel = $mapModel;
    }

    public function login() {
        $map = $this->mapModel->method();
    }
}
?>
