<?php if (isset($_SESSION['delftx_id'])) {
    header('Location: index.php?method=dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $userController->checkLogin($_POST['username'], $_POST['password']);
}
?>

<div class="container">
    <form class="form-signin" role="form" action="index.php?method=login" method="post">
        <h2 class="form-signin-heading">DelftX Worldmap</h2>
        <?php if (isset($message)) {
            echo $message;
        } ?>
        <input type="username" name="username" class="form-control" placeholder="Username" required autofocus>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    </form>
</div>
