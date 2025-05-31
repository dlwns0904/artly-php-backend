<?php
namespace Controllers;

use Models\ExhibitionModel;
use Middlewares\AuthMiddleware;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Exhibition",
 *     description="전시회 관련 API"
 * )
 */
class ExhibitionController {
    private $model;

    public function __construct() {
        $this->model = new ExhibitionModel();
    }

    /**
     * @OA\Get(
     *     path="/api/exhibitions",
     *     summary="전시회 목록 조회",
     *     tags={"Exhibition"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="전시회 상태 (scheduled: 예정, exhibited: 진행중, ended: 종료)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="전시회 카테고리 (회화, 미디어, 디자인, 사진, 키즈아트, 특별전시, 조각, 설치미술, 공예, 소장품전, 테마전, 기획전)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="region",
     *         in="query",
     *         description="전시회 지역 (서울, 경기, 인천, 대구, 경북, 부산, 울산, 경남, 광주, 전라, 대전, 충청, 세종, 제주, 강원)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="정렬 순서 (latest: 최신순, ending: 종료임박순, popular: 좋아요순)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="liked_only",
     *         in="query",
     *         description="좋아요한 전시만 필터 (true일 경우 내가 좋아요한 전시만 표시)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="전시회 목록 조회 성공",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="exhibition_title", type="string"),
     *             @OA\Property(property="exhibition_poster", type="string"),
     *             @OA\Property(property="exhibition_category", type="string"),
     *             @OA\Property(property="exhibition_start_date", type="string", format="date"),
     *             @OA\Property(property="exhibition_end_date", type="string", format="date"),
     *             @OA\Property(property="exhibition_start_time", type="string", format="date-time"),
     *             @OA\Property(property="exhibition_end_time", type="string", format="date-time"),
     *             @OA\Property(property="exhibition_location", type="string"),
     *             @OA\Property(property="exhibition_price", type="integer"),
     *             @OA\Property(property="gallery_id", type="integer"),
     *             @OA\Property(property="exhibition_tag", type="string"),
     *             @OA\Property(property="exhibition_status", type="string"),
     *             @OA\Property(property="create_dtm", type="string", format="date-time"),
     *             @OA\Property(property="update_dtm", type="string", format="date-time"),
     *             @OA\Property(property="like_count", type="integer"),
     *             @OA\Property(property="is_liked", type="boolean")
     *         ))
     *     )
     * )
     */
     public function getExhibitionList() {
        $auth = new AuthMiddleware();
        $decoded = $auth->decodeToken();
        $user_id = $decoded && isset($decoded->user_id) ? $decoded->user_id : null;
        $likedOnly = $_GET['liked_only'] ?? null;
        $likedOnlyBool = filter_var($likedOnly, FILTER_VALIDATE_BOOLEAN);

        if ($likedOnlyBool && !$user_id) {
            http_response_code(401);
            echo json_encode(['message' => 'Authentication required for liked_only filter.']);
            return;
        }

        $filters = [
            'status' => $_GET['status'] ?? null,
            'category' => $_GET['category'] ?? null,
            'region' => $_GET['region'] ?? null,
            'sort' => $_GET['sort'] ?? null,
            'liked_only' => $likedOnly,
            'user_id' => $user_id
        ];

        $exhibitions = $this->model->getExhibitions($filters);
        header('Content-Type: application/json');
        echo json_encode($exhibitions, JSON_UNESCAPED_UNICODE);
    }



    /**
     * @OA\Get(
     *     path="/api/exhibitions/{id}",
     *     summary="전시회 상세 조회",
     *     tags={"Exhibition"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="전시회 상세 조회 성공",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="exhibition_title", type="string"),
     *             @OA\Property(property="exhibition_poster", type="string"),
     *             @OA\Property(property="exhibition_category", type="string"),
     *             @OA\Property(property="exhibition_start_date", type="string", format="date"),
     *             @OA\Property(property="exhibition_end_date", type="string", format="date"),
     *             @OA\Property(property="exhibition_start_time", type="string", format="date-time"),
     *             @OA\Property(property="exhibition_end_time", type="string", format="date-time"),
     *             @OA\Property(property="exhibition_location", type="string"),
     *             @OA\Property(property="exhibition_price", type="integer"),
     *             @OA\Property(property="gallery_id", type="integer"),
     *             @OA\Property(property="exhibition_tag", type="string"),
     *             @OA\Property(property="exhibition_status", type="string"),
     *             @OA\Property(property="create_dtm", type="string", format="date-time"),
     *             @OA\Property(property="update_dtm", type="string", format="date-time"),
     *             @OA\Property(property="like_count", type="integer"),
     *             @OA\Property(property="is_liked", type="boolean")
     *         )
     *     )
     * )
     */
    public function getExhibitionById($id) {
        $user_id = AuthMiddleware::getUserId();
        $exhibition = $this->model->getById($id, $user_id);
        if ($exhibition) {
            header('Content-Type: application/json');
            echo json_encode($exhibition, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Exhibition not found']);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/exhibitions",
     *     summary="전시회 등록",
     *     tags={"Exhibition"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="exhibition_title", type="string"),
     *             @OA\Property(property="exhibition_category", type="string"),
     *             @OA\Property(property="exhibition_status", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="등록 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="exhibition_title", type="string"),
     *                  @OA\Property(property="exhibition_poster", type="string"),
     *                  @OA\Property(property="exhibition_category", type="string"),
     *                  @OA\Property(property="exhibition_start_date", type="string", format="date"),
     *                  @OA\Property(property="exhibition_end_date", type="string", format="date"),
     *                  @OA\Property(property="exhibition_start_time", type="string", format="date-time"),
     *                  @OA\Property(property="exhibition_end_time", type="string", format="date-time"),
     *                  @OA\Property(property="exhibition_location", type="string"),
     *                  @OA\Property(property="exhibition_price", type="integer"),
     *                  @OA\Property(property="gallery_id", type="integer"),
     *                  @OA\Property(property="exhibition_tag", type="string"),
     *                  @OA\Property(property="exhibition_status", type="string"),
     *                  @OA\Property(property="create_dtm", type="string", format="date-time"),
     *                  @OA\Property(property="update_dtm", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function createExhibition() {
        $data = json_decode(file_get_contents('php://input'), true);
        $createdExhibition = $this->model->create($data);

        if ($createdExhibition) {
            http_response_code(201);
            echo json_encode([
                'message' => 'Exhibition created successfully',
                'data' => $createdExhibition
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create exhibition']);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/exhibitions/{id}",
     *     summary="전시회 수정",
     *     tags={"Exhibition"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="exhibition_title", type="string"),
     *             @OA\Property(property="exhibition_category", type="string"),
     *             @OA\Property(property="exhibition_status", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="수정 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="exhibition_title", type="string"),
     *                  @OA\Property(property="exhibition_poster", type="string"),
     *                  @OA\Property(property="exhibition_category", type="string"),
     *                  @OA\Property(property="exhibition_start_date", type="string", format="date"),
     *                  @OA\Property(property="exhibition_end_date", type="string", format="date"),
     *                  @OA\Property(property="exhibition_start_time", type="string", format="date-time"),
     *                  @OA\Property(property="exhibition_end_time", type="string", format="date-time"),
     *                  @OA\Property(property="exhibition_location", type="string"),
     *                  @OA\Property(property="exhibition_price", type="integer"),
     *                  @OA\Property(property="gallery_id", type="integer"),
     *                  @OA\Property(property="exhibition_tag", type="string"),
     *                  @OA\Property(property="exhibition_status", type="string"),
     *                  @OA\Property(property="create_dtm", type="string", format="date-time"),
     *                  @OA\Property(property="update_dtm", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="전시회 없음")
     * )
     */
    public function updateExhibition($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $success = $this->model->update($id, $data);

        if ($success) {
            $updatedExhibition = $this->model->getById($id);
            echo json_encode([
                'message' => 'Exhibition updated successfully',
                'data' => $updatedExhibition
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Exhibition not found or update failed']);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/exhibitions/{id}",
     *     summary="전시회 삭제",
     *     tags={"Exhibition"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="삭제 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="전시회 없음 또는 삭제 실패")
     * )
     */
    public function deleteExhibition($id) {
        $success = $this->model->delete($id);

        if ($success) {
            echo json_encode(['message' => 'Exhibition deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Exhibition not found or delete failed']);
        }
    }
}
