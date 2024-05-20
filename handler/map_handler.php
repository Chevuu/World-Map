<?php
require_once('config/config.php'); // Ensure this file includes your database and necessary class initializations

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_code = $_POST['course_code'] ?? '';
    $course_run = $_POST['course_run'] ?? '';
    $map_id = $_POST['map_id'] ?? '';

    if (!empty($course_code) && !empty($course_run) && !empty($map_id)) {
        // Create a new map entry in the database or retrieve existing map ID
        $editable = 1; // Default value, adjust as needed
        $visible = 1; // Default value, adjust as needed

        // Assuming your MapController and mapModel are set up correctly
        $mapId = $mapController->getOrCreateMap($course_code, $course_run, $editable, $visible);

        if ($mapId) {
            $mapUrl = "https://delftxdev.tudelft.nl/new_map/map.php?action=show&course_id={$course_code}&map_id={$mapId}&user_id=%%USER_ID%%";
            echo json_encode(['success' => true, 'mapUrl' => $mapUrl]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create or retrieve map']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid course details']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
