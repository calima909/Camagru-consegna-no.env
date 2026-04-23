<?php
require_once __DIR__ . '/../models/comment.php';
require_once __DIR__ . '/../models/image.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/preference.php';
require_once __DIR__ . '/../core/mail.php';
require_once __DIR__ . '/../core/auth.php';

class CommentController extends Controller
{
    public function add(): void
    {
        requireAuth();
        $this->verifyCsrf();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $imageId = $input['image_id'] ?? $_POST['image_id'] ?? null;
        $commentText = trim($input['comment'] ?? $_POST['comment'] ?? '');

        if (!$imageId || !$commentText) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'missing_data'
            ]);
            exit;
        }

        $imageModel = new Image();
        $image = $imageModel->getById((int)$imageId);
        if (!$image) {
            echo json_encode([
                'success' => false,
                'error' => 'image_not_found'
            ]);
            exit;
        }

        $commentModel = new Comment();
        $commentModel->create((int)$imageId, $_SESSION['user_id'], $commentText);

        $userModel = new User();
        $imageOwner = $userModel->getById($image['user_id']);
        $prefModel = new Preference();

        if ($imageOwner &&
            $_SESSION['user_id'] != $image['user_id'] &&
            $prefModel->notifyComments($image['user_id'])
        ) {
            $subject = "Nuovo commento sulla tua immagine su FunkyLink";
            $body = "Ciao {$imageOwner['username']},\n\n";
            $body .= "{$_SESSION['username']} ha commentato la tua immagine:\n\n";
            $body .= $commentText . "\n\n";
            $body .= "Visualizza l'immagine qui: http://localhost:8080\n";
            $body .= "Non rispondere a questa email.";
            sendEmail($imageOwner['email'], $subject, $body);
        }

        echo json_encode([
            'success' => true,
            'username' => $_SESSION['username'],
            'image_id' => $imageId,
            'comment' => $commentText
        ]);
        exit;
    }
}