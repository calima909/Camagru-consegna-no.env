<?php

require_once __DIR__ . '/../models/like.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../models/image.php';

class LikeController extends Controller
{
    public function toggle(): void
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'error' => 'not_logged'
            ]);
            return;
        }
        $this->verifyCsrf();

        $data = json_decode(file_get_contents('php://input'), true);
        $imageId = $data['image_id'] ?? null;
        if (!$imageId) {
            echo json_encode([
                'success' => false,
                'error' => 'missing_image'
            ]);
            return;
        }

        $imageModel = new Image();
        $image = $imageModel->getById((int)$imageId);
        if (!$image) {
            echo json_encode([
                'success' => false,
                'error' => 'image_not_found'
            ]);
            return;
        }

        $likeModel = new Like();
        $liked = $likeModel->toggle((int)$imageId, $_SESSION['user_id']);
        $count = $likeModel->countByImage((int)$imageId);
        echo json_encode([
            'success' => true,
            'count' => $count,
            'liked' => $liked
        ]);
        return;
    }
}