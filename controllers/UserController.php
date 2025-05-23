<?php
namespace Controllers;

use OpenApi\Annotations as OA;
use Models\UserModel;
use Middlewares\AuthMiddleware;

/**
 * @OA\Tag(
 *     name="User",
 *     description="사용자 관련 API"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class UserController {
    private $model;

    public function __construct() {
        $this->model = new UserModel();
        $this->auth = new AuthMiddleware();
    }

    /**
     * @OA\Get(
     *     path="/api/users/me",
     *     summary="마이페이지",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="login_id", type="string"),
     *             @OA\Property(property="login_pwd", type="string"),
     *             @OA\Property(property="user_name", type="string"),
     *             @OA\Property(property="user_gender", type="string"),
     *             @OA\Property(property="user_age", type="integer"),
     *             @OA\Property(property="user_email", type="string"),
     *             @OA\Property(property="user_phone", type="string"),
     *             @OA\Property(property="user_img", type="string"),
     *             @OA\Property(property="user_keyword", type="string"),
     *             @OA\Property(property="admin_flag", type="integer"),
     *             @OA\Property(property="gallery_id", type="integer"),
     *             @OA\Property(property="last_login_time", type="string", format="date-time"),
     *             @OA\Property(property="reg_time", type="string", format="date-time"),
     *             @OA\Property(property="update_dttm", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=404, description="프로필 없음")
     * )
     */
    public function getMe() {
	header('Content-Type: application/json');
        $user = $this->auth->authenticate();
        $userId = $user->user_id;

        $profile = $this->model->getById($userId);
        if ($profile) {
            echo json_encode($profile, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/users/me",
     *     summary="프로필 수정",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="login_id", type="string"),
     *             @OA\Property(property="login_pwd", type="string"),
     *             @OA\Property(property="user_name", type="string"),
     *             @OA\Property(property="user_gender", type="string"),
     *             @OA\Property(property="user_age", type="integer"),
     *             @OA\Property(property="user_email", type="string"),
     *             @OA\Property(property="user_phone", type="string"),
     *             @OA\Property(property="user_img", type="string"),
     *             @OA\Property(property="user_keyword", type="string"),
     *             @OA\Property(property="admin_flag", type="integer"),
     *             @OA\Property(property="gallery_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="수정 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="login_id", type="string"),
     *                 @OA\Property(property="login_pwd", type="string"),
     *                 @OA\Property(property="user_name", type="string"),
     *                 @OA\Property(property="user_gender", type="string"),
     *                 @OA\Property(property="user_age", type="integer"),
     *                 @OA\Property(property="user_email", type="string"),
     *                 @OA\Property(property="user_phone", type="string"),
     *                 @OA\Property(property="user_img", type="string"),
     *                 @OA\Property(property="user_keyword", type="string"),
     *                 @OA\Property(property="admin_flag", type="integer"),
     *                 @OA\Property(property="gallery_id", type="integer"),
     *                 @OA\Property(property="last_login_time", type="string", format="date-time"),
     *                 @OA\Property(property="reg_time", type="string", format="date-time"),
     *                 @OA\Property(property="update_dttm", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="수정 실패")
     * )
     */
    public function updateMe() {
	header('Content-Type: application/json');
        $user = $this->auth->authenticate();
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

    /**
     * @OA\Get(
     *     path="/api/users/me/exhibitions",
     *     summary="내 전시 일정",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="성공"),
     *     @OA\Response(response=404, description="내 전시 일정 없음")
     * )
     */
public function getMyReservations() {
    header('Content-Type: application/json');
    $user = $this->auth->authenticate();
    $userId = $user->user_id;

    $reservations = $this->model->getMyReservations($userId);
    if ($reservations && count($reservations) > 0) {
        echo json_encode($reservations, JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Reservation not found']);
    }
}



    /**
     * @OA\Get(
     *     path="/api/users/me/purchases",
     *     summary="내 구매 내역",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="성공"),
     *     @OA\Response(response=404, description="내 구매 내역 없음")
     * )
     */
public function getMyPurchases() {
    header('Content-Type: application/json');
    $user = $this->auth->authenticate();
    $userId = $user->user_id;

    $purchases = $this->model->getMyPurchases($userId);
    if ($purchases && count($purchases) > 0) {
        echo json_encode($purchases, JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Purchases not found']);
    }
}
 /**
     * @OA\Get(
     *     path="/api/users/me/likes",
     *     summary="내 좋아요 전시회",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="exhibition_title", type="string"),
     *             @OA\Property(property="exhibition_poster", type="string"),
     *             @OA\Property(property="exhibition_category", type="string"),
     *             @OA\Property(property="exhibition_start_date", type="date-time"),
     *             @OA\Property(property="exhibition_end_date", type="date-time"),
     *             @OA\Property(property="exhibition_start_time", type="date-time"),
     *             @OA\Property(property="exhibition_end_time", type="date-time"),
     *             @OA\Property(property="exhibition_location", type="string"),
     *             @OA\Property(property="exhibition_price", type="integer"),
     *             @OA\Property(property="gallery_id", type="integer"),
     *             @OA\Property(property="exhibition_tag", type="string"),
     *             @OA\Property(property="exhibition_status", type="string"),
     *             @OA\Property(property="create_dtm", type="string", format="date-time"),
     *             @OA\Property(property="update_dtm", type="string", format="date-time")
     *         ))
     *     ),
     *     @OA\Response(response=404, description="내 좋아요 전시회 없음")
     * )
     */
    public function getMyLikes() {
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;

        $likeExhibitions = $this->model->getMyLikeExhibitions($userId);
        if ($likeExhibitions) {
            echo json_encode($likeExhibitions, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Like Exhibitions not found']);
        }
    }

}

