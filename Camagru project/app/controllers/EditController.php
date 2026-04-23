<?php

require_once __DIR__ . '/../models/image.php';
require_once __DIR__ . '/../models/comment.php';
require_once __DIR__ . '/../core/auth.php';

class EditController extends Controller
{
    public function index(): void
    {
        requireAuth();
        
        $imageModel = new Image();
        $commentModel = new Comment();

        $userImages = $imageModel->getByUser($_SESSION['user_id']);
        foreach ($userImages as &$img) {
            $img['comments'] = $commentModel->getByImage($img['id']);
        }

        $overlays = scandir(__DIR__ . '/../../public/overlays');
        $overlays = array_filter($overlays, fn($f) => !in_array($f, ['.', '..']));

        $this->view('edit/index', [
            'userImages' => $userImages,
            'overlays' => $overlays
        ]);
    }

    public function save(): void
    {
        requireAuth();
        $this->verifyCsrf();

        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['image'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'No image data'
            ]);
            return;
        }

        $imgData = $data['image'];

        if (preg_match('/^data:image\/png;base64,(.+)$/', $imgData, $matches)) {
            $dataDecoded = base64_decode($matches[1]);
            $filename = uniqid() . '.png';
            $path = __DIR__ . '/../../public/uploads/' . $filename;

            if (file_put_contents($path, $dataDecoded) === false) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Errore salvataggio file'
                ]);
                return;
            }

            $imageModel = new Image();
            $imageModel->create($_SESSION['user_id'], $filename);

            echo json_encode([
                'success' => true,
                'filename' => $filename
            ]);

        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Formato non valido'
            ]);
        }
    }

    public function delete(): void
    {
        requireAuth();
        $this->verifyCsrf();

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['filename'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File mancante']);
            return;
        }

        $filename = $data['filename'];

        $imageModel = new Image();
        $image = $imageModel->getByFilenameAndUser($filename, $_SESSION['user_id']);
        if (!$image) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Accesso negato']);
            return;
        }

        $imageModel->delete($filename);
        $path = __DIR__ . '/../../public/uploads/' . $filename;
        if (file_exists($path)) unlink($path);
        echo json_encode(['success' => true]);
    }
}