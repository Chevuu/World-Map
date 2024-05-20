<form role="form" method="post" action="index.php?method=dashboard&action=edit_user&user_id=<?php echo $course_data['id']; ?>">
    <div class="form-group">
        <label for="user">Username</label>
        <input type="text" class="form-control" id="user" name="user" value="<?php echo $course_data['username']; ?>" disabled>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="text" class="form-control" id="password" name="password" value="" required>
    </div>
    <button type="submit" class="btn btn-default">Update</button>
</form>