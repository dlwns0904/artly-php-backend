<?php
namespace Controllers;

use OpenApi\Annotations as OA;

use Models\ExhibitionModel;
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
     *     @OA\Parameter(name="status", in="query", description="전시회 상태", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="category", in="query", description="전시회 카테고리", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="exhibition_title", type="string"),
     *             @OA\Property(property="exhibition_category", type="string"),
     *             @OA\Property(property="exhibition_status", type="string"),
     *             @OA\Property(property="create_dttm", type="string", format="date-time"),
     *             @OA\Property(property="update_dttm", type="string", format="date-time")
     *         ))
     *     )
     * )
     */
    public function getExhibitionList() {
        // 쿼리 파라미터 읽기
        $filters = [
            'status' => $_GET['status'] ?? null,
            'category' => $_GET['category'] ?? null,
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
     *             @OA\Property(property="exhibition_category", type="string"),
     *             @OA\Property(property="exhibition_status", type="string"),
     *             @OA\Property(property="create_dttm", type="string", format="date-time"),
     *             @OA\Property(property="update_dttm", type="string", format="date-time")
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
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="exhibition_title", type="string"),
     *                 @OA\Property(property="exhibition_category", type="string"),
     *                 @OA\Property(property="exhibition_status", type="string"),
     *                 @OA\Property(property="create_dttm", type="string", format="date-time"),
     *                 @OA\Property(property="update_dttm", type="string", format="date-time")
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
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="exhibition_title", type="string"),
     *                 @OA\Property(property="exhibition_category", type="string"),
     *                 @OA\Property(property="exhibition_status", type="string"),
     *                 @OA\Property(property="create_dttm", type="string", format="date-time"),
     *                 @OA\Property(property="update_dttm", type="string", format="date-time")
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
