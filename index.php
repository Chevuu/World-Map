<?php

//Added undermendioned 2 lines to see errors on display
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
session_start();
require_once('includes/main/database.class.inc.php');
$db = new database();
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
  <link href="includes/style/<?php echo $cssFile; ?>" rel="stylesheet">

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
  // DASHBOARD
  // ---------------
  elseif (@$_GET['method'] == 'dashboard') {
    if (!isset($_SESSION['delftx_id'])) {
      header('Location: index.php');
    }
  ?>
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">DelftX World Map</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="index.php?method=logout">Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
    <?php
    // ---------------
    // DASHBOARD/ADMIN
    // ---------------
    if ($_SESSION['delftx_id'] == 0) {
    ?>
      <div class="container-fluid">
        <div class="row">
          <div class="col-sm-3 col-md-2 sidebar">
            <ul class="nav nav-sidebar">
              <li <?php if (!isset($_GET['action'])) {
                    echo 'class="active"';
                  } ?>><a href="index.php?method=dashboard">Overview courses</a></li>
              <li <?php if (isset($_GET['action']) && $_GET['action'] == 'add_course') {
                    echo 'class="active"';
                  } ?>><a href="index.php?method=dashboard&action=add_course">Add course</a></li>
            </ul>
          </div>
          <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

            <?php
            // ADD COURSE
            if (@$_GET['action'] == 'add_course') {
              if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $q_user = $db->query("SELECT * FROM mooc_course WHERE user = '" . addslashes($_POST['username']) . "'", __LINE__);
                if ($db->rows($q_user) != 0) {
            ?>
                  <div class="alert alert-danger" role="alert"><b>User already exists!</b> Please provide another username.</div>
                <?php
                } else {
                  $db->query("INSERT INTO mooc_course (course, user, password) VALUES ('" . addslashes($_POST['course']) . "', '" . addslashes($_POST['username']) . "', '" . md5(isset($_POST['password'])) . "')", __LINE__);
                ?>
                  <div class="alert alert-success" role="alert"><b>Well done!</b> You successfully added a new course</div>
              <?php
                }
              }
              ?>
              <h1 class="page-header">Add course</h1>

              <form role="form" method="post" action="index.php?method=dashboard&action=add_course">
                <div class="form-group">
                  <label for="course">Course</label>
                  <input type="text" class="form-control" id="course" name="course" placeholder="Enter course name" required>
                </div>
                <div class="form-group">
                  <label for="username">Username</label>
                  <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                  <label for="password">Password</label>
                  <input type="password" class="form-control" id="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-default">Submit</button>
              </form>
            <?php
            }
            // EDIT USER
            elseif (@$_GET['action'] == 'edit_user' & isset($_GET['user_id'])) {
            ?>
              <h1 class="page-header">Edit user</h1>
              <?php
              if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $db->query("UPDATE mooc_course SET password = '" . md5($_POST['password']) . "' WHERE id = '" . addslashes($_GET['user_id']) . "'", __LINE__);
              ?>
                <div class="alert alert-success" role="alert"><b>Well done!</b> You successfully updated the password</div>
              <?php
              }
              $q_course = $db->query("SELECT * FROM mooc_course WHERE id = '" . addslashes($_GET['user_id']) . "'", __LINE__);

              if ($db->rows($q_course) != 1) {
                header('Location: index.php?method=dashboard');
              }

              $data_course = $db->assoc($q_course);
              ?>
              <form role="form" method="post" action="index.php?method=dashboard&action=edit_user&user_id=<?php echo $data_course['id']; ?>">
                <div class="form-group">
                  <label for="user">Username</label>
                  <input type="text" class="form-control" id="user" name="user" value="<?php echo $data_course['user']; ?>" disabled>
                </div>
                <div class="form-group">
                  <label for="password">Password</label>
                  <input type="text" class="form-control" id="password" name="password" value="" required>
                </div>
                <button type="submit" class="btn btn-default">Update</button>
              </form>
            <?php
            }
            // OVERVIEW COURSES
            else {
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
                    $q_courses = $db->query("SELECT * FROM mooc_course ORDER BY id DESC");

                    while ($data = $db->assoc($q_courses)) {
                      $q_maps = $db->query("SELECT * FROM mooc_map WHERE course_id = '" . $data['id'] . "'");
                      $number_maps = $db->rows($q_maps);
                    ?>
                      <tr>
                        <td><?php echo $data['course']; ?></td>
                        <td><?php echo $number_maps; ?></td>
                        <td><a href="index.php?method=dashboard&action=edit_user&user_id=<?php echo $data['id'] ?>"><?php echo $data['user']; ?></a></td>
                      </tr>
                    <?php
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            <?php
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
          <div class="col-sm-3 col-md-2 sidebar">
          <ul class="nav nav-sidebar">
            <li <?php if (!isset($_GET['action'])) { echo 'class="active"'; } ?>><a href="index.php?method=dashboard">Overview maps</a></li>
            <li <?php if (isset($_GET['action']) && $_GET['action'] == 'add_map') { echo 'class="active"'; } ?>><a href="index.php?method=dashboard&action=add_map">Add map</a></li>
          </ul>
          </div>
          <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

            <?php
            // ADD MAP
            if (@$_GET['action'] == 'add_map') {
            ?>
              <h1 class="page-header">Add map</h1>
              <?php
              date_default_timezone_set('GMT');
              if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $db->query("INSERT INTO mooc_map (course_id, name, editable, visible) VALUES ('" . addslashes($_SESSION['delftx_id']) . "', '" . addslashes($_POST['name'] . " (" . date('M Y') . ")") . "', '" . addslashes($_POST['editable']) . "', '" . addslashes($_POST['visible']) . "')", __LINE__);
              ?>
                <div class="alert alert-success" role="alert"><b>Well done!</b> You successfully added a new map</div>
              <?php
              }
              ?>
              <form role="form" method="post" action="index.php?method=dashboard&action=add_map">
                <div class="form-group">
                  <label for="name">Name</label>
                  <input type="text" class="form-control" id="course" name="name" placeholder="Enter map name" required>
                </div>
                <div class="form-group">
                  <label for="name">Editable</label>
                  <select class="form-control" id="editable" name="editable">
                    <option value="1">Yes</option>
                    <option value="0" selected="selected">No</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="name">Visible</label>
                  <select class="form-control" id="visible" name="visible">
                    <option value="1" selected="selected">Yes</option>
                    <option value="0">No</option>
                  </select>
                </div>
                <button type="submit" class="btn btn-default">Submit</button>
              </form>
            <?php
            }
            // MAP DETAILS
            elseif (@$_GET['action'] == 'map_details' & isset($_GET['map_id'])) {
            ?>
              <h1 class="page-header">Map details</h1>

              <h4 class="page-header">Update map name</h4>
              <?php
              if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $db->query("UPDATE mooc_map SET name = '" . addslashes($_POST['name']) . "', editable = '" . addslashes($_POST['editable']) . "', visible = '" . addslashes($_POST['visible']) . "' WHERE id = '" . addslashes($_GET['map_id']) . "'", __LINE__);
              ?>
                <div class="alert alert-success" role="alert"><b>Well done!</b> You successfully updated the map</div>
              <?php
              }
              $q_map = $db->query("SELECT * FROM mooc_map WHERE id = '" . addslashes($_GET['map_id']) . "'", __LINE__);

              if ($db->rows($q_map) != 1) {
                header('Location: index.php?method=dashboard');
              }

              $data_map = $db->assoc($q_map);
              ?>
              <form role="form" method="post" action="index.php?method=dashboard&action=map_details&map_id=<?php echo $data_map['id']; ?>">
                <div class="form-group">
                  <label for="name">Name</label>
                  <input type="text" class="form-control" id="course" name="name" value="<?php echo $data_map['name'] ?>" required>
                </div>
                <div class="form-group">
                  <label for="name">Editable</label>
                  <select class="form-control" id="editable" name="editable">
                    <option value="1" <?php if ($data_map['editable'] == '1') {
                                        echo ' selected="selected"';
                                      } ?>>Yes</option>
                    <option value="0" <?php if ($data_map['editable'] == '0') {
                                        echo ' selected="selected"';
                                      } ?>>No</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="name">Visible</label>
                  <select class="form-control" id="visible" name="visible">
                    <option value="1" <?php if ($data_map['visible'] == '1') {
                                        echo ' selected="selected"';
                                      } ?>>Yes</option>
                    <option value="0" <?php if ($data_map['visible'] == '0') {
                                        echo ' selected="selected"';
                                      } ?>>No</option>
                  </select>
                </div>
                <button type="submit" class="btn btn-default">Update</button>
              </form>

              <br />
              <h4 class="page-header">Embed World Map</h4>

              <p>Copy this code into a raw html component in edX to embed the world map in your course.</p>

              <!-- <pre><iframe style="border: none;" src="https://delftxdev.tudelft.nl/map/map.php?action=show&amp;course_id=<?php echo addslashes($_SESSION['delftx_id']) ?>&amp;map_id=<?php echo $data_map['id'] ?>&amp;user_id=%%USER_ID%%" height="615" width="100%"></iframe></pre> -->

              <br />
              <h4 class="page-header">Export</h4>
              <p><a href="json.php?map_id=<?php echo $data_map['id'] ?>">Download json<a></p>
            <?php
            }
            // OVERVIEW MAPS
            else {
              $q_course = $db->query("SELECT * FROM mooc_course WHERE id = '" . addslashes($_SESSION['delftx_id']) . "'");
              $data_course = $db->assoc($q_course);

            ?>
              <h1 class="page-header">Overview maps</h1>

              <h4 class="page-header">All maps</h4>

              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Number of entries</th>
                      <th>Editable?</th>
                      <th>Visible?</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $q_maps = $db->query("SELECT * FROM mooc_map WHERE course_id = '" . addslashes($_SESSION['delftx_id']) . "' ORDER BY id DESC");

                    while ($data = $db->assoc($q_maps)) {
                      $first_id = $data['id'];
                      $q_entries = $db->query("SELECT * FROM mooc_map_entry WHERE map_id = '" . $data['id'] . "'");
                      $number_enties = $db->rows($q_entries);
                    ?>
                      <tr>
                        <td><a href="index.php?method=dashboard&action=map_details&map_id=<?php echo $data['id']; ?>"><?php echo $data['name']; ?></a></td>
                        <td><?php echo $number_enties; ?></td>
                        <td><?php echo (($data['editable'] == '1') ? 'Yes' : 'No') ?></td>
                        <td><?php echo (($data['visible'] == '1') ? 'Yes' : 'No') ?></td>
                      </tr>
                    <?php
                    }
                    ?>
                  </tbody>
                </table>
              </div>

              <h4 class="page-header">Embed World Map</h4>

              <p>All world maps should be embedded seperately and thus the html to embed a map can be found on the detail screen of the world map itself. </p>

              <!-- <pre style="display:none;"><iframe style="border: none;" src="https://delftxdev.tudelft.nl/map/map.php?action=show&amp;course_id=<?php echo addslashes($_SESSION['delftx_id']) ?>&amp;map_id=<?php echo $first_id; ?>&amp;user_id=%%USER_ID%%" height="615" width="100%"></iframe></pre> -->

            <?php
            }
            ?>

          </div>
        </div>
      </div>
    <?php
    }
  }
  // ---------------
  // LOGIN
  // ---------------
  else {
    if (isset($_SESSION['delftx_id'])) {
      header('Location: index.php?method=dashboard');
    }
    ?>
    <div class="container">
      <form class="form-signin" role="form" action="index.php" method="post">
        <h2 class="form-signin-heading">DelftX Worldmap</h2>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
          if ($_POST['username'] == 'mapmgr' and $_POST['password'] == 'W0rldm@p2024!') {
            $_SESSION['delftx_id'] = 0;
            header('Location: index.php?method=dashboard');
          } else {
            $q_user = $db->query("SELECT * FROM mooc_course WHERE user = '" . addslashes($_POST['username']) . "' AND password = '" . md5($_POST['password']) . "'");
            if ($db->rows($q_user) == 1) {
              $data_user = $db->assoc($q_user);
              $_SESSION['delftx_id'] = $data_user['id'];
              header('Location: index.php?method=dashboard');
            } else {
        ?>
              <div class="alert alert-danger" role="alert">Wrong credentials</div>
        <?php
            }
          }
        }
        ?>
        <input type="username" name="username" class="form-control" placeholder="Username" required autofocus>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      </form>

    </div> <!-- /container -->
  <?php
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