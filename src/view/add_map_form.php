<?php $course_code = $userController->getCourseCodeById($_SESSION['delftx_id']); ?>

<h1 class="page-header">Add map</h1>

<form role="form" method="post" action="index.php?method=dashboard&action=add_map">
    <div class="form-group">
        <label for="course_code">Course Code</label>
        <input type="text" class="form-control" id="course_code" name="course_code" value="<?php echo $course_code; ?>" readonly>
    </div>
    <div class="form-group">
        <label for="course_run">Course Run</label>
        <input type="text" class="form-control" id="course_run" name="course_run" placeholder="Enter course run" required>
    </div>
    <div class="form-group">
        <label for="editable">Editable</label>
        <select class="form-control" id="editable" name="editable">
            <option value="1">Yes</option>
            <option value="0" selected="selected">No</option>
        </select>
    </div>
    <div class="form-group">
        <label for="visible">Visible</label>
        <select class="form-control" id="visible" name="visible">
            <option value="1" selected="selected">Yes</option>
            <option value="0">No</option>
        </select>
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
</form>
