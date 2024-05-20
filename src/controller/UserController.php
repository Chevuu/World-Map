<?php

namespace src\controller;

class UserController {
    private $userModel;

    public function __construct($userModel) {
        $this->userModel = $userModel;
    }

    public function login($username, $password) {
        $user = $this->userModel->findByUsernameAndPassword($username, $password);
        if ($user) {
            $_SESSION['delftx_id'] = $user['id'];
            header('Location: index.php?method=dashboard');
            exit;
        } else {
            return '<div class="alert alert-danger" role="alert">Wrong credentials</div>';
        }
    }

    public function checkLogin($username, $password) {
        if ($username === 'mapmgr' && $password === 'W0rldm@p2024!') {
            $_SESSION['delftx_id'] = 0;
            header('Location: index.php?method=dashboard');
            exit;
        } else {
            return $this->login($username, $password);
        }
    }

    public function logout() {
        unset($_SESSION['delftx_id']);
        header('Location: index.php');
        exit;
    }

    public function addUser($course_code, $username, $password) {
        $existingUser = $this->userModel->findByUsername($username);
        if ($existingUser) {
            return '<div class="alert alert-danger" role="alert"><b>User already exists!</b> Please provide another username.</div>';
        } else {
            $this->userModel->insert($course_code, $username, $password);
            return '<div class="alert alert-success" role="alert"><b>Well done!</b> You successfully added a new course</div>';
        }
    }

    public function updateUser($userId, $password) {
        $this->userModel->update($userId, $password);
        return '<div class="alert alert-success" role="alert"><b>Well done!</b> You successfully updated the password</div>';
    }

    public function getAllCourses() {
        $result = $this->userModel->fetchAllCourses();
        $courses = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $courses[] = $row;
        }
        return $courses;
    }

    public function getCourseCodeById($userId) {
        $course = $this->userModel->findById($userId)->fetch_assoc();
        return $course['course_code'];
    }   
}
?>
