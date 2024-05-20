<h1 class="page-header">Map details</h1>

<h4 class="page-header">Update map name</h4>

<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
    <?php if ($mapController->updateMap($_GET['map_id'], $_POST['name'], $_POST['editable'], $_POST['visible'])): ?>
        <div class="alert alert-success" role="alert"><b>Well done!</b> You successfully updated the map</div>
    <?php endif; ?>
<?php endif; ?>

<?php
$data_map = $mapController->getMapById($_GET['map_id'])->fetch_assoc();
if (!$data_map) {
    header('Location: index.php?method=dashboard');
    exit;
}
?>

<form role="form" method="post" action="index.php?method=dashboard&action=map_details&map_id=<?php echo $data_map['id']; ?>">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" class="form-control" id="course" name="name" value="<?php echo $data_map['map_name']; ?>" required>
    </div>
    <div class="form-group">
        <label for="editable">Editable</label>
        <select class="form-control" id="editable" name="editable">
            <option value="1" <?php if ($data_map['editable'] == '1') echo 'selected="selected"'; ?>>Yes</option>
            <option value="0" <?php if ($data_map['editable'] == '0') echo 'selected="selected"'; ?>>No</option>
        </select>
    </div>
    <div class="form-group">
        <label for="visible">Visible</label>
        <select class="form-control" id="visible" name="visible">
            <option value="1" <?php if ($data_map['visible'] == '1') echo 'selected="selected"'; ?>>Yes</option>
            <option value="0" <?php if ($data_map['visible'] == '0') echo 'selected="selected"'; ?>>No</option>
        </select>
    </div>
    <button type="submit" class="btn btn-default">Update</button>
</form>

<br />
<h4 class="page-header">Embed World Map</h4>
<p>Copy this code into a raw html component in edX to embed the world map in your course.</p>
<!-- <pre><iframe style="border: none;" src="https://delftxdev.tudelft.nl/map/map.php?action=show&amp;course_id=<?php echo addslashes($_SESSION['delftx_id']) ?>&amp;map_id=<?php echo $data_map['id']; ?>&amp;user_id=%%USER_ID%%" height="615" width="100%"></iframe></pre> -->

<br />
<h4 class="page-header">Export</h4>
<p><a href="json.php?map_id=<?php echo $data_map['id']; ?>">Download json<a></p>
