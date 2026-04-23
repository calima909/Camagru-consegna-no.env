<?php

class Controller
{
    public function __construct()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }    

    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            exit('View not found');
        }
        require_once $viewPath;
    }

    protected function verifyCsrf(): void
    {
        $token = null;

        if (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['csrf_token'])) {
            $token = $input['csrf_token'];
        }

        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            $this->view('auth/error');
        }
    }
}