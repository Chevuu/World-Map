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

    public function logout() {
        unset($_SESSION['delftx_id']);
        header('Location: index.php');
        exit;
    }

    public function addUser($course, $username, $password) {
        $existingUser = $this->userModel->findByUsername($username);
        if ($existingUser) {
            return '<div class="alert alert-danger" role="alert"><b>User already exists!</b> Please provide another username.</div>';
        } else {
            $this->userModel->insert($course, $username, $password);
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
    
    
}
?>
