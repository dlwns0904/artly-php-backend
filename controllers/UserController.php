<?php
namespace Controllers;

use OpenApi\Annotations as OA;

use Models\UserModel;
use Middlewares\AuthMiddleware;

class UserController {
    private $model;

    public function __construct() {
        $this->model = new UserModel();
        $this->auth = new AuthMiddleware();
    }

    public function getMe() {
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;

        $profile = $this->model->getById($userId);
        if ($profile) {
            echo json_encode($profile, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
        }
    }
    
    public function updateMe() {
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        $success = $this->model->update($userId, $data);

        if ($success) {
            $user = $this->model->getById($userId);
            echo json_encode([
                'message' => 'Profile updated successfully',
                'data' => $user
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Failed to update profile']);
        }
    }

    public function getMyReservations() {
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;

        $reservations = $this->model->getMyReservations($userId);
        if ($reservations) {
            echo json_encode($reservations, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Reservation not found']);
        }
    }

    public function getMyPurchases() {
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;

        $purchases = $this->model->getMyPurchases($userId);
        if ($purchases) {
            echo json_encode($purchases, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Purchases not found']);
        }
    }
}
