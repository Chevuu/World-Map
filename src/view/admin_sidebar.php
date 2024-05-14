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