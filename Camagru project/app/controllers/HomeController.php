<?php
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/image.php';
require_once __DIR__ . '/../models/comment.php';

class HomeController extends Controller
{
    public function index(): void
    {       
        $userModel = new User();
        $imageModel = new Image();
        $commentModel = new Comment();

        $users = $userModel->getAll();
        $userId = $_SESSION['user_id'] ?? null;

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;

        $limit = 6;
        $offset = ($page - 1) * $limit;

        $images = $imageModel->getAll($userId, $limit, $offset);

        foreach ($images as &$img) {
            $img['comments'] = $commentModel->getByImage($img['id']);
        }

        $totalImages = $imageModel->countAll();
        $totalPages = ceil($totalImages / $limit);

        $this->view('home/index', [
            'users' => $users,
            'images' => $images,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }
}
