<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once('database.php');
$db = new database();
require_once('src/model/User.php');
require_once('src/model/Map.php');
require_once('src/controller/UserController.php');
require_once('src/controller/MapController.php');

$userModel = new src\model\User($db);
$userController = new src\controller\UserController($userModel);
$mapModel = new src\model\Map($db);
$mapController = new src\controller\MapController($mapModel, $userModel);
?>
