<?php
namespace Controllers;

use Models\SessionModel;

class SessionController {
    private $model;

    public function __construct() {
        $this->model = new SessionModel();
    }

    public function getSessionsByDate($exhibitionId) {
        if (!isset($_GET['date'])) {
            http_response_code(400);
            echo json_encode(["message" => "날짜를 선택하세요."]);
            return;
        }

        $date = $_GET['date'];
        $sessions = $this->model->getSessionsByDate($exhibitionId, $date);
        echo json_encode($sessions, JSON_UNESCAPED_UNICODE);
    }
}
