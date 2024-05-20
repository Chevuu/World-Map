<?php
ob_start();
require_once('config/config.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>DelftX World Map</title>

  <!-- Bootstrap core CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">

  <?php
  // Check which CSS file is being selected
  $cssFile = (@$_GET['method'] == 'dashboard') ? 'dashboard.css' : 'signin.css';
  echo "<!-- Using CSS File: $cssFile -->";
  ?>
  <link href="assets/css/<?php echo $cssFile; ?>" rel="stylesheet">

</head>

<body>
  <?php
  // ---------------
  // LOGOUT
  // ---------------
  if (@$_GET['method'] == 'logout') {
    unset($_SESSION['delftx_id']);
    header('Location: index.php');
  }
  // ---------------
  // LOGIN
  // ---------------
  elseif (@$_GET['method'] == 'login' || !isset($_GET['method'])) {
    require_once 'src/view/login.php';
  }
  // ---------------
  // DASHBOARD
  // ---------------
  elseif (@$_GET['method'] == 'dashboard') {
    if (!isset($_SESSION['delftx_id'])) {
      header('Location: index.php');
    }
  ?>
    <?php
    require_once 'src/view/navigation_bar.php';
    // ---------------
    // DASHBOARD/ADMIN
    // ---------------
    if ($_SESSION['delftx_id'] == 11) {
    ?>
      <div class="container-fluid">
        <div class="row">
          <?php require_once 'src/view/admin_sidebar.php'; ?>
          <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <?php
            // ADD COURSE
            if (@$_GET['action'] == 'add_course') {
              if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // Initialize variables
                $course_code = isset($_POST['course_code']) ? $_POST['course_code'] : null;
                $username = isset($_POST['username']) ? $_POST['username'] : null;
                $password = isset($_POST['password']) ? $_POST['password'] : null;
                echo $userController->addUser($course_code, $username, $password);
              }
              require_once 'src/view/add_course_form.php';
            }
            // EDIT USER
            elseif (@$_GET['action'] == 'edit_user' & isset($_GET['user_id'])) {
            ?>
              <h1 class="page-header">Edit user</h1>
            <?php
              if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                echo $userController->updateUser($_GET['user_id'], $_POST['password']);
              }
              $data_course = $userModel->findById($_GET['user_id']);
              if ($data_course->num_rows != 1) {
                header('Location: index.php?method=dashboard');
              }
              $course_data = $data_course->fetch_assoc();
              require_once 'src/view/edit_user_form.php';
            }
            // OVERVIEW COURSES
            else {
              require_once 'src/view/overview_courses.php';
            }
            ?>
          </div>
        </div>
      </div>
    <?php
    }
    // ---------------
    // DASHBOARD/USER
    // ---------------
    else {
    ?>
      <div class="container-fluid">
        <div class="row">
          <?php require_once 'src/view/user_sidebar.php'; ?>
          <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <?php
            // ADD MAP
            if (@$_GET['action'] == 'add_map') {
              date_default_timezone_set('GMT');
              if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                echo $mapController->addMap($_POST['course_code'], $_POST['course_run'], $_POST['editable'], $_POST['visible']);
              }
              require_once 'src/view/add_map_form.php';
            }
            // MAP DETAILS
            elseif (@$_GET['action'] == 'map_details' && isset($_GET['map_id'])) {
              require_once 'src/view/map_details.php';
            }
            // OVERVIEW MAPS
            else {
              $maps = $mapController->getMapsByCourseCode($_SESSION['delftx_id']);
              require_once 'src/view/overview_maps.php';
            }
            ?>

          </div>
        </div>
      </div>
    <?php
    }
  }
  ?>

  <!-- Bootstrap core JavaScript
    ================================================== -->
  <!-- Placed at the end of the document so the pages load faster -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script type="text/javascript">
    $(function() {
      var pre = $('pre');
      pre.html(htmlEncode(pre.html()));
    });

    function htmlEncode(value) {
      return $('<div/>').text(value).html();
    }
  </script>
</body>

</html>
<?php
ob_end_flush();
?>