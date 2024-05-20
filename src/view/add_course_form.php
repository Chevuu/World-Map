<h1 class="page-header">Add course</h1>
<form role="form" method="post" action="index.php?method=dashboard&action=add_course">
    <div class="form-group">
        <label for="course_code">Course code</label>
        <input type="text" class="form-control" id="course_code" name="course_code" placeholder="Enter course code" required>
    </div>
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
</form>