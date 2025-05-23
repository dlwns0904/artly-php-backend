<?php
namespace Controllers;

use OpenApi\Annotations as OA;
use Models\LikeModel;
use Middlewares\AuthMiddleware;

/**
 * @OA\Tag(
 *     name="Like",
 *     description="좋아요 관련 API"
 * )
 */
class LikeController {
    private $model;
    private $auth;

    public function __construct() {
        $this->model = new LikeModel();
        $this->auth = new AuthMiddleware();
    }

    /**
     * @OA\Post(
     *     path="/api/likes",
     *     summary="좋아요 생성",
     *     tags={"Like"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="liked_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="liked_type",
     *                 type="string",
     *                 enum={"gallery", "exhibition", "artist"},
     *                 example="exhibition",
     *                 description="좋아요 대상의 타입: gallery, exhibition, artist 중 하나"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="좋아요 생성 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="정상적으로 등록되었습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="좋아요 생성 실패"
     *     )
     * )
     */
    public function createLike() {
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$this->model->targetExists($data)) {
            http_response_code(404);
            echo json_encode(['message' => '해당 좋아요 대상이 존재하지 않습니다.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $success = $this->model->create($userId, $data);

        if ($success) {
            http_response_code(201);
            echo json_encode(['message' => '정상적으로 등록되었습니다.'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Failed to create Like'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/likes",
     *     summary="좋아요 삭제",
     *     tags={"Like"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="liked_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="liked_type",
     *                 type="string",
     *                 enum={"gallery", "exhibition", "artist"},
     *                 example="exhibition"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="좋아요 삭제 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="정상적으로 삭제되었습니다.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="좋아요 삭제 실패"
     *     )
     * )
     */
    public function deleteLike() {
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;
        $data = json_decode(file_get_contents('php://input'), true);
        $success = $this->model->delete($userId, $data);

        if ($success) {
            echo json_encode(['message' => '정상적으로 삭제되었습니다.'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Like not found or delete failed'], JSON_UNESCAPED_UNICODE);
        }
    }
}

