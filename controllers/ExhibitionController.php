<?php
require_once __DIR__ . '/../models/ExhibitionModel.php';

class ExhibitionController {
    private $model;

    public function __construct() {
        $this->model = new ExhibitionModel();
    }

    // 전시회 목록 조회
    public function getExhibitionList() {
        # 필터 적용 필요
        $exhibitions = $this->model->getAll();
        header('Content-Type: application/json');
        echo json_encode($exhibitions, JSON_UNESCAPED_UNICODE);
    }

    // 전시회 상세 조회
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

    // 전시회 등록
    public function createExhibition() {
        $data = json_decode(file_get_contents('php://input'), true);
        $createdId = $this->model->create($data);

        if ($createdId) {
            $createdExhibition = $this->model->getById($createdId);
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

    // 전시회 정보 수정
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
    
    // 전시회 삭제
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
