<?php
require_once __DIR__ . '/../models/ArtModel.php';

class ArtController {
    private $model;

    public function __construct() {
        $this->model = new ArtModel();
    }

    // 작품 목록 조회
    public function getArtList() {
        # 필터 적용 필요
        $arts = $this->model->getAll();
        header('Content-Type: application/json');
        echo json_encode($arts, JSON_UNESCAPED_UNICODE);
    }

    // 작품 상세 조회
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

    // 작품 등록
    public function createArt() {
        $data = json_decode(file_get_contents('php://input'), true);
        $createdArt = $this->model->create($data);

        if ($createdArt) {
            http_response_code(201);
            echo json_encode([
                'message' => 'Art created successfully',
                'data' => $createdArt
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create art']);
        }
    }

    // 작품 정보 수정
    public function updateArt($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $success = $this->model->update($id, $data);

        if ($success) {
            $updatedArt = $this->model->getById($id);
            echo json_encode([
                'message' => 'Art updated successfully',
                'data' => $updatedArt
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Art not found or update failed']);
        }
    }
    
    // 작품 삭제
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
