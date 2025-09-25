<?php
namespace Controllers;
use OpenApi\Annotations as OA;
require_once __DIR__ . '/../models/GalleryModel.php';

/**
 * @OA\Tag(
 *     name="Gallery",
 *     description="갤러리 관련 API"
 * )
 */
class GalleryController {
    /**
     * @OA\Post(
     *     path="/api/galleries",
     *     summary="갤러리 생성",
     *     tags={"Gallery"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="gallery_name", type="string"),
     *             @OA\Property(property="gallery_image", type="string"),
     *             @OA\Property(property="gallery_address", type="string"),
     *             @OA\Property(property="gallery_closed_day", type="string"),
     *             @OA\Property(property="gallery_category", type="string"),
     *             @OA\Property(property="gallery_description", type="string"),
     *             @OA\Property(property="gallery_start_time", type="string", format="date-time"),
     *             @OA\Property(property="gallery_end_time", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="생성 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="gallery_name", type="string"),
     *                 @OA\Property(property="gallery_image", type="string"),
     *                 @OA\Property(property="gallery_address", type="string"),
     *                 @OA\Property(property="gallery_closed_day", type="string"),
     *                 @OA\Property(property="gallery_category", type="string"),
     *                 @OA\Property(property="gallery_description", type="string"),
     *                 @OA\Property(property="gallery_start_time", type="string", format="date-time"),
     *                 @OA\Property(property="gallery_end_time", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="생성 실패")
     * )
     */
    public function createGallery() {
        // 관리자 인증
        $auth = new \Middlewares\AuthMiddleware();
        $decoded = $auth->requireAdmin();

        $data = json_decode(file_get_contents('php://input'), true);
        $createdGallery = $this->model->create($data);
        if ($createdGallery) {
            http_response_code(201);
            echo json_encode([
                'message' => 'Gallery created successfully',
                'data' => $createdGallery
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create gallery']);
        }
    }
    private $model;

    public function __construct() {
        $this->model = new \Models\GalleryModel();
    }

    /**
     * @OA\Get(
     *     path="/api/galleries",
     *     summary="갤러리 목록 조회",
     *     tags={"Gallery"},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="전시 진행 상태 (진행중 등)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="region",
     *         in="query",
     *         description="지역 (서울, 경기, 대구 등)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="공간 형태 (미술관, 박물관, 갤러리 등)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="갤러리 목록 조회 성공",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="image", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function getGalleryList() {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'region' => $_GET['region'] ?? null,
            'type' => $_GET['type'] ?? null
        ];
        $galleries = $this->model->getGalleries($filters);
        header('Content-Type: application/json');
        echo json_encode($galleries, JSON_UNESCAPED_UNICODE);
    }

    /**
 * @OA\Get(
 *     path="/api/galleries/{id}",
 *     summary="갤러리 상세 조회",
 *     tags={"Gallery"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="갤러리 ID",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="갤러리 상세 조회 성공",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="gallery_name", type="string"),
 *             @OA\Property(property="gallery_image", type="string"),
 *             @OA\Property(property="gallery_address", type="string"),
 *             @OA\Property(property="gallery_start_time", type="string"),
 *             @OA\Property(property="gallery_end_time", type="string"),
 *             @OA\Property(property="gallery_closed_day", type="string"),
 *             @OA\Property(property="gallery_category", type="string"),
 *             @OA\Property(property="gallery_description", type="string"),
 *             @OA\Property(
 *                 property="exhibitions",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="exhibition_title", type="string"),
 *                     @OA\Property(property="exhibition_poster", type="string"),
 *                     @OA\Property(property="exhibition_start_date", type="string", format="date"),
 *                     @OA\Property(property="exhibition_end_date", type="string", format="date")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="갤러리 없음"
 *     )
 * )
 */

    public function getGalleryById($id) {
        $gallery = $this->model->getById($id);
        if ($gallery) {
            header('Content-Type: application/json');
            echo json_encode($gallery, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Gallery not found']);
        }
    }
}
