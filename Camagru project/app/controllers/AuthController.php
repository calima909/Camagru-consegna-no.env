<?php
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../core/mail.php';

class AuthController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }    

    public function register(): void
    {
        $this->view('auth/register');
    }
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /auth/register');
            exit;
        }
        $this->verifyCsrf();
    
        $username = trim($_POST['username']);
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['error'] = "Tutti i campi sono obbligatori.";
            header("Location: /auth/register");
            exit;
        }    
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Email non valida.";
            header("Location: /auth/register");
            exit;
        }
        if (strlen($username) < 3) {
            $_SESSION['error'] = "Username troppo corto minimo 3 caratteri.";
            header("Location: /auth/register");
            exit;
        }    
        if (strlen($password) < 6) {
            $_SESSION['error'] = "La password deve avere almeno sei caratteri.";
            header("Location: /auth/register");
            exit;
        }
    
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));    
        $userModel = new User();

        if ($userModel->findByUsername($username)) { 
            $_SESSION['error'] = "Username già utilizzato.";
            header("Location: /auth/register");
            exit;
        }
        if ($userModel->findByEmail($email)) {
            $_SESSION['error'] = "Email già registrata.";
            header("Location: /auth/register");
            exit;
        }
    
        $userModel->create($username, $email, $hashedPassword, $token);

        $link = "http://localhost:8080/auth/verify?token=" . $token;
        $subject = "Conferma registrazione FunkyLink";
        $body = "Benvenuto su FunkyLink!\n\n";
        $body .= "Clicca qui per attivare il tuo account:\n\n";
        $body .= $link;
        sendEmail($email, $subject, $body);
    
        $message = "Controlla la tua email per confermare la registrazione.";
        $this->view('auth/verifyToken', ['message' => $message]);
    }

    public function login(): void
    {
        $this->view('auth/login');
    }

    public function loginPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /auth/login');
            exit;
        }
        $this->verifyCsrf();

        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $userModel = new User();
        $user = $userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Credenziali non valide.';
            header('Location: /auth/login');
            exit;
        }
        if (!$user['email_verified']) {
            $_SESSION['error'] = 'Devi verificare la tua email prima di fare login.';
            header('Location: /auth/login');
            exit;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header('Location: /');
        exit;
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        header('Location: /');
        exit;
    }

    public function verify(): void
    {
        $token = $_GET['token'] ?? null;
        if (!$token) {
            $message = "Token mancante.";
            $this->view('auth/verifyToken', ['message' => $message]);
            return;
        }

        $userModel = new User();
        $user = $userModel->getByToken($token);
        if (!$user) {
            $message = "Token non valido.";
            $this->view('auth/verifyToken', ['message' => $message]);
            return;
        }

        $userModel->verifyEmail($token);

        $message = "Email verificata! Ora puoi fare login.";
        $this->view('auth/verifyToken', ['message' => $message]);
    }

    public function forgot()
    {
        $this->view('auth/forgot');
    }

    public function sendReset()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /auth/forgot');
            exit;
        }    
        $this->verifyCsrf();
        $email = trim($_POST['email'] ?? '');

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user && $user['email_verified']) {
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", time() + 600);

            $userModel->setResetToken($email, $token, $expires);

            $link = "http://localhost:8080/auth/reset?token=$token";

            sendEmail($email, "Reset password", "Clicca qui: $link");
            $this->view('auth/resetSent');
        }
        else {
            $message = "Email non trovata o non verificata.";
            $this->view('auth/forgot', ['message' => $message]);
        }

    }

    public function reset()
    {
        $token = $_GET['token'] ?? '';
        $userModel = new User();
        $user = $userModel->findByResetToken($token);
        if (!$user || strtotime($user['reset_expires']) < time()) {
            die("Token non valido o scaduto");
        }
        $this->view('auth/reset', ['token' => $token]);
    }

    public function updatePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }
        $this->verifyCsrf();    
        $password = $_POST['password'] ?? '';
        $token = $_POST['token'] ?? '';
        if (strlen($password) < 6) {
            $this->view('auth/reset', [
                'message' => 'Password troppo corta',
                'token' => $token
            ]);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByResetToken($token);

        if (!$user) {
            $message = "Token non valido";
            $this->view('auth/reset', ['message' => $message]);
            return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $userModel->updatePasswordWithToken($hash, $token);
        $this ->view('auth/success', ['message' => 'Password aggiornata.']);
    }
    
    public function error () {
        $this->view('auth/error');
    }
}
