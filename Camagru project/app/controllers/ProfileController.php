<?php

require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/image.php';
require_once __DIR__ . '/../models/preference.php';

class ProfileController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /auth/login");
            exit;
        }

        $userModel = new User();
        $imageModel = new Image();
        $prefModel = new Preference();

        $userId = $_SESSION['user_id'];
        $user = $userModel->getById($userId);
        $images = $imageModel->getByUser($userId);
        $notifyComments = $prefModel->notifyComments($userId);
        
        $this->view('profile/index', [
            'user' => $user,
            'images' => $images,
            'notifyComments' => $notifyComments
        ]);
    }

    public function update()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /profile");
            exit;
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("CSRF token non valido");
        }
        $userId = $_SESSION['user_id'];
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $notifyComments = isset($_POST['notify_comments']) ? true : false;

        $userModel = new User();
        $prefModel = new Preference();
        $errors = [];


        if (strlen($username) < 3) {
            $errors[] = "Username troppo corto (min 3 caratteri)";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email non valida";
        }
        if ($password && strlen($password) < 6) {
            $errors[] = "Password troppo corta (min 6 caratteri)";
        }

        $existing = $userModel->findByEmail($email);
        if ($existing && $existing['id'] != $userId) {
            $errors[] = "Email già in uso";
        }
        
        if (!empty($errors)) {
            $user = $userModel->getById($userId);
            $imageModel = new Image();
            $images = $imageModel->getByUser($userId);

            $this->view('profile/index', [
                'user' => $user,
                'images' => $images,
                'notifyComments' => $prefModel->notifyComments($userId),
                'errors' => $errors
            ]);
            return;
        }

        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $userModel->updateWithPassword($userId, $username, $email, $hashed);
        } else {
            $userModel->updateWithoutPassword($userId, $username, $email);
        }

        $prefModel->setNotifyComments($userId, $notifyComments);
        $_SESSION['username'] = $username;
        header("Location: /profile");
        exit;
    }
}