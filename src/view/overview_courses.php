<?php
require_once('config/database.php');
$db = new database();
require_once('src/model/User.php');
$userModel = new src\model\User($db);
require_once('src/controller/UserController.php');
$userController = new src\controller\UserController($userModel);
?>
<h1 class="page-header">Overview courses</h1>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Number of maps</th>
                <th>Admin</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $courses = $userController->getAllCourses();

            foreach ($courses as $data) {
                $q_maps = $db->query("SELECT * FROM map WHERE course_code = '" . $data['course_code'] . "'");
                $number_maps = $db->rows($q_maps);
            ?>
                <tr>
                    <td><?php echo $data['course_code']; ?></td>
                    <td><?php echo $number_maps; ?></td>
                    <td><a href="index.php?method=dashboard&action=edit_user&user_id=<?php echo $data['id'] ?>"><?php echo $data['username']; ?></a></td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
</div>