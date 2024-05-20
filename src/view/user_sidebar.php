<div class="col-sm-3 col-md-2 sidebar">
    <ul class="nav nav-sidebar">
        <li <?php if (!isset($_GET['action'])) {
                echo 'class="active"';
            } ?>><a href="index.php?method=dashboard">Overview maps</a></li>
        <li <?php if (isset($_GET['action']) && $_GET['action'] == 'add_map') {
                echo 'class="active"';
            } ?>><a href="index.php?method=dashboard&action=add_map">Add map</a></li>
    </ul>
</div>