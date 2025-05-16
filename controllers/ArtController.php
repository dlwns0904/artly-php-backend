<?php
use OpenApi\Annotations as OA; 
require_once __DIR__ . '/../models/ArtModel.php';

class ArtController {
    private $model;

    public function __construct() {
        $this->model = new ArtModel();
    }

    /**
     * @OA\Get(
     *     path="/api/arts",
     *     summary="작품 목록 조회",
     *     tags={"Art"},
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="art_title", type="string")
     *         ))
     *     )
     * )
     */
    public function getArtList() {
        $arts = $this->model->getAll();
        header('Content-Type: application/json');
        echo json_encode($arts, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Get(
     *     path="/api/arts/{id}",
     *     summary="작품 상세 조회",
     *     tags={"Art"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="성공"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function getArtById($id) {
        $art = $this->model->getById($id);
        if ($art) {
            header('Content-Type: application/json');
            echo json_encode($art);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Art not found']);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/arts",
     *     summary="작품 등록",
     *     tags={"Art"},
     *     @OA\RequestBody(required=true),
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function createArt() {
        $data       = json_decode(file_get_contents('php://input'), true);
        $createdArt = $this->model->create($data);

        if ($createdArt) {
            http_response_code(201);
            echo json_encode([
                'message' => 'Art created successfully',
                'data'    => $createdArt
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create art']);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/arts/{id}",
     *     summary="작품 수정",
     *     tags={"Art"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true),
     *     @OA\Response(response=200, description="Updated")
     * )
     */
    public function updateArt($id) {
        $data    = json_decode(file_get_contents('php://input'), true);
        $success = $this->model->update($id, $data);

        if ($success) {
            $updatedArt = $this->model->getById($id);
            echo json_encode([
                'message' => 'Art updated successfully',
                'data'    => $updatedArt
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Art not found or update failed']);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/arts/{id}",
     *     summary="작품 삭제",
     *     tags={"Art"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted")
     * )
     */
    public function deleteArt($id) {
        $success = $this->model->delete($id);

        if ($success) {
            echo json_encode(['message' => 'Art deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Art not found or delete failed']);
        }
    }
}

