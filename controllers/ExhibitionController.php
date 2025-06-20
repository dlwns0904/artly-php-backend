<?php
namespace Controllers;

use OpenApi\Annotations as OA;

use Models\ExhibitionModel;
use Models\UserModel;
use Middlewares\AuthMiddleware;
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
        $this->userModel = new UserModel();
        $this->auth = new AuthMiddleware();
    }

    /**
     * @OA\Get(
     *     path="/api/exhibitions",
     *     summary="전시회 목록 조회",
     *     tags={"Exhibition"},
     *     @OA\Parameter(
     *          name="status", 
     *          in="query", 
     *          description="전시회 상태 (scheduled, exhibited, ended)", 
     *          required=false, 
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *          name="category", 
     *          in="query", 
     *          description="전시회 카테고리 (회화, 미디어, 디자인, 사진, 키즈아트, 특별전시, 조각, 설치미술, 공예, 소장품전, 테마전, 기획전)", 
     *          required=false, 
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *          name="region", 
     *          in="query", 
     *          description="전시회 지역 (서울, 경기, 인천, 대구, 경북, 부산, 울산, 경남, 광주, 전라, 대전, 충청, 세종, 제주, 강원)", 
     *          required=false, 
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *          name="sort", 
     *          in="query", 
     *          description="전시회 정렬 순서 (latest: 최신순, ending: 종료순, popular: 인기순 - 좋아요 수로 정렬)", 
     *          required=false, 
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
 *         response=200,
 *         description="성공",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="exhibition_title", type="string"),
 *                 @OA\Property(property="exhibition_poster", type="string"),
 *                 @OA\Property(property="exhibition_category", type="string"),
 *                 @OA\Property(property="exhibition_start_date", type="string", format="date"),
 *                 @OA\Property(property="exhibition_end_date", type="string", format="date"),
 *                 @OA\Property(property="exhibition_start_time", type="string", format="time"),
 *                 @OA\Property(property="exhibition_end_time", type="string", format="time"),
 *                 @OA\Property(property="exhibition_location", type="string"),
 *                 @OA\Property(property="exhibition_price", type="integer"),
 *                 @OA\Property(property="exhibition_tag", type="string"),
 *                 @OA\Property(property="exhibition_status", type="string", enum={"scheduled", "ongoing", "ended"}),
 *                 @OA\Property(property="create_dtm", type="string", format="date-time"),
 *                 @OA\Property(property="update_dtm", type="string", format="date-time"),
 *                 @OA\Property(property="like_count", type="integer"),
 *                 @OA\Property(property="is_liked", type="boolean"),
 *                 @OA\Property(property="gallery_id", type="integer"),
 *                 @OA\Property(
 *                     property="exhibition_organization",
 *                     type="object",
 *                     @OA\Property(property="name", type="string"),
 *                     @OA\Property(property="image", type="string"),
 *                     @OA\Property(property="address", type="string"),
 *                     @OA\Property(property="start_time", type="string", format="time"),
 *                     @OA\Property(property="end_time", type="string", format="time"),
 *                     @OA\Property(property="closed_day", type="string"),
 *                     @OA\Property(property="category", type="string"),
 *                     @OA\Property(property="description", type="string"),
 *                     @OA\Property(property="latitude", type="float"),
 *                     @OA\Property(property="longitude", type="float")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
    public function getExhibitionList() {
        // 쿼리 파라미터 읽기
        $filters = [
            'status' => $_GET['status'] ?? null,
            'category' => $_GET['category'] ?? null,
            'region' => $_GET['region'] ?? null,
            'sort' => $_GET['sort'] ?? null,
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
     *         description="성공",
     *         @OA\JsonContent(
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
     *             @OA\Property(property="update_dtm", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=404, description="전시회 없음")
     * )
     */
    public function getExhibitionById($id) {
        $exhibition = $this->model->getById($id);
        if ($exhibition) {
            header('Content-Type: application/json');
            echo json_encode($exhibition);
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
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;

        # 사용자가 갤러리 사용자인지 확인
        $userData = $this->userModel->getById($userId);
        $gallery_id = $userData['gallery_id'] ?? null;
        if (!isset($gallery_id) || $gallery_id === null || $gallery_id <= 0) {
            http_response_code(403);
            echo json_encode([
                'message' => '권한이 없습니다.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $createdExhibition = $this->model->create($data, $gallery_id);

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
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;

        # 사용자가 해당 전시회와 관련된 갤러리 사용자인지 확인
        $userData = $this->userModel->getById($userId);
        $exhibition = $this->model->getById($id);
        if ($userData['gallery_id'] != $exhibition['gallery_id']) {
            http_response_code(403);
            echo json_encode([
                'message' => '권한이 없습니다.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $gallery_id = $exhibition['gallery_id'];
        $success = $this->model->update($id, $data, $gallery_id);

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
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;

        # 사용자가 해당 전시회와 관련된 갤러리 사용자인지 확인
        $userData = $this->userModel->getById($userId);
        $exhibition = $this->model->getById($id);
        if ($userData['gallery_id'] != $exhibition['gallery_id']) {
            http_response_code(403);
            echo json_encode([
                'message' => '권한이 없습니다.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $success = $this->model->delete($id);

        if ($success) {
            echo json_encode(['message' => 'Exhibition deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Exhibition not found or delete failed']);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/exhibitions/{id}/arts",
     *     summary="전시회 작품 등록",
     *     tags={"Exhibition"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="art_id", type="integer"),
     *             @OA\Property(property="display_order", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="등록 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="exhibition_id", type="integer"),
     *                  @OA\Property(property="art_id", type="integer"),
     *                  @OA\Property(property="display_order", type="integer"),
     *                  @OA\Property(property="create_dtm", type="string", format="date-time"),
     *                  @OA\Property(property="update_dtm", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function registerArts($id) {
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;

        # 사용자가 해당 전시회와 관련된 갤러리 사용자인지 확인
        $userData = $this->userModel->getById($userId);
        $exhibition = $this->model->getById($id);
        if ($userData['gallery_id'] != $exhibition['gallery_id']) {
            http_response_code(403);
            echo json_encode([
                'message' => '권한이 없습니다.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $registeredArt = $this->model->registerArt($id, $data);

        if ($registeredArt) {
            http_response_code(201);
            echo json_encode([
                'message' => 'Artworks registered successfully',
                'data' => $registeredArt
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to register Artworks']);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/exhibitions/{id}/artworks",
     *     summary="전시회 작가 등록",
     *     tags={"Exhibition"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="artist_id", type="integer"),
     *             @OA\Property(property="role", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="등록 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="exhibition_id", type="integer"),
     *                  @OA\Property(property="artist_id", type="integer"),
     *                  @OA\Property(property="role", type="string"),
     *                  @OA\Property(property="create_dtm", type="string", format="date-time"),
     *                  @OA\Property(property="update_dtm", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function registerArtists($id) {
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;

        # 사용자가 해당 전시회와 관련된 갤러리 사용자인지 확인
        $userData = $this->userModel->getById($userId);
        $exhibition = $this->model->getById($id);
        
        if ($userData['gallery_id'] != $exhibition['gallery_id']) {
            http_response_code(403);
            echo json_encode([
                'message' => '권한이 없습니다.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $registeredArtist = $this->model->registerArtist($id, $data);

        if ($registeredArtist) {
            http_response_code(201);
            echo json_encode([
                'message' => 'Artist registered successfully',
                'data' => $registeredArtist
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to register Artist']);
        }
    }
}
