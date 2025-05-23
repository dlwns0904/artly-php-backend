<?php
namespace Controllers;

use OpenApi\Annotations as OA;
use Models\GalleryModel;

/**
 * @OA\Tag(
 *     name="Gallery",
 *     description="갤러리 관련 API"
 * )
 */
class GalleryController {
    private $model;

    public function __construct() {
        $this->model = new GalleryModel();
    }

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
     *             @OA\Property(property="gallery_start_time", type="string"),
     *             @OA\Property(property="gallery_end_time", type="string"),
     *             @OA\Property(property="gallery_closed_day", type="string"),
     *             @OA\Property(property="gallery_category", type="string"),
     *             @OA\Property(property="gallery_description", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="갤러리 생성 완료")
     * )
     */
    public function createGallery() {
        $data = json_decode(file_get_contents("php://input"), true);
        $created = $this->model->create($data);
        http_response_code(201);
        echo json_encode($created, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Put(
     *     path="/api/galleries/{id}",
     *     summary="갤러리 수정",
     *     tags={"Gallery"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="gallery_name", type="string"),
     *             @OA\Property(property="gallery_image", type="string"),
     *             @OA\Property(property="gallery_address", type="string"),
     *             @OA\Property(property="gallery_start_time", type="string"),
     *             @OA\Property(property="gallery_end_time", type="string"),
     *             @OA\Property(property="gallery_closed_day", type="string"),
     *             @OA\Property(property="gallery_category", type="string"),
     *             @OA\Property(property="gallery_description", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="갤러리 수정 완료")
     * )
     */
    public function updateGallery($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $updated = $this->model->update($id, $data);
        http_response_code(200);
        echo json_encode($updated, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Delete(
     *     path="/api/galleries/{id}",
     *     summary="갤러리 삭제",
     *     tags={"Gallery"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="갤러리 삭제 완료")
     * )
     */
    public function deleteGallery($id) {
        $this->model->delete($id);
        http_response_code(200);
        echo json_encode(['message' => 'Gallery deleted'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Get(
     *     path="/api/galleries",
     *     summary="갤러리 목록 조회",
     *     tags={"Gallery"},
     *     @OA\Parameter(name="status", in="query", description="전시 상태", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="region", in="query", description="지역", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="type", in="query", description="공간 형태", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="조회 성공",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="image", type="string")
     *         ))
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
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="상세 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="gallery_name", type="string"),
     *             @OA\Property(property="gallery_image", type="string"),
     *             @OA\Property(property="gallery_address", type="string"),
     *             @OA\Property(property="gallery_start_time", type="string"),
     *             @OA\Property(property="gallery_end_time", type="string"),
     *             @OA\Property(property="gallery_closed_day", type="string"),
     *             @OA\Property(property="gallery_category", type="string"),
     *             @OA\Property(property="gallery_description", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="갤러리 없음")
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

