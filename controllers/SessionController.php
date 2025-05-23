<?php
namespace Controllers;

use Models\SessionModel;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Reservation",
 *     description="예매  관련 API"
 * )
 */
class SessionController {
    private $model;

    public function __construct() {
        $this->model = new SessionModel();
    }

    /**
     * @OA\Get(
     *     path="/api/exhibitions/{id}/sessions",
     *     summary="전시회 세션 목록 조회 (특정 날짜)",
     *     tags={"Reservation"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="전시회 ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         description="조회할 날짜 (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="세션 목록 조회 성공",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="session_id", type="integer", example=5),
     *                 @OA\Property(property="time", type="string", example="10:00"),
     *                 @OA\Property(property="available_capacity", type="integer", example=20)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="날짜를 입력하지 않은 경우"
     *     )
     * )
     */
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

