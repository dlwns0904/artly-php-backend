<?php
namespace Controllers;

use Models\ReservationModel;
use Middlewares\AuthMiddleware;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Reservation",
 *     description="전시회 예약 관련 API"
 * )
 */
class ReservationController {
    private $model;
    private $auth;

    public function __construct() {
        $this->model = new ReservationModel();
        $this->auth = new AuthMiddleware();
    }

    /**
     * @OA\Post(
     *     path="/api/reservations",
     *     summary="전시회 예매 등록",
     *     tags={"Reservation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"exhibition_id", "session_id", "number_of_tickets", "payment_method", "total_price"},
     *             @OA\Property(property="exhibition_id", type="integer", example=1),
     *             @OA\Property(property="number_of_tickets", type="integer", example=2),
     *             @OA\Property(property="payment_method", type="string", example="credit_card"),
     *             @OA\Property(property="total_price", type="integer", example=20000),
     *             @OA\Property(property="reservation_datetime", type="string", format="date", example="2025-06-03")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="예약 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="예약이 완료되었습니다.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="입력 누락"),
     *     @OA\Response(response=500, description="예약 실패")
     * )
     */
    // 예약 생성
    public function createReservation() {
        $user = $this->auth->authenticate(); // JWT에서 사용자 인증
        $userId = $user->user_id;

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset( $data['exhibition_id'], $data['number_of_tickets'], $data['payment_method'], $data['total_price'], $data['reservation_datetime'])) {
            http_response_code(400);
            echo json_encode(['message' => '필수 항목이 누락되었습니다.']);
            return;
        }

        $reservationDate = $data['reservation_datetime'];
        $reservationDateTime = $reservationDate . " 00:00:00";
        $data['reservation_datetime'] = $reservationDateTime;

        $success = $this->model->create($userId, $data);
        

        if ($success) {
            echo json_encode(['message' => '예약이 완료되었습니다.'],JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['message' => '예약에 실패했습니다.'], JSON_UNESCAPED_UNICODE);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/reservations/{id}",
     *     summary="전시회 예매 취소",
     *     tags={"Reservation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="예약 ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="예약 취소 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="예약이 취소되었습니다.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="예약 찾을 수 없음")
     * )
     */
    // 예약 취소
    public function cancelReservation($reservationId) {
        $user = $this->auth->authenticate(); // 권한 확인하고
        $userId = $user->user_id;

        $success = $this->model->cancel($reservationId, $userId);

        if ($success) {
            echo json_encode(['message' => '예약이 취소되었습니다.'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => '예약을 찾을 수 없거나 취소에 실패했습니다.'], JSON_UNESCAPED_UNICODE);
        }
    }


    /**
 * @OA\Patch(
 *     path="/api/reservations/{id}",
 *     summary="전시회 예매 수정",
 *     tags={"Reservation"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="예약 ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="number_of_tickets", type="integer", example=2),
 *             @OA\Property(property="payment_method", type="string", example="credit_card"),
 *             @OA\Property(property="total_price", type="integer", example=20000),
 *             @OA\Property(property="reservation_datetime", type="string", format="date", example="2025-06-10"),
 *             @OA\Property(property="reservation_status", type="string", example="reserved", description="reserved/canceld/used") 
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="예약 수정 성공",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="예약이 수정되었습니다.")
 *         )
 *     ),
 *     @OA\Response(response=404, description="예약 찾을 수 없음")
 * )
 */
    public function updateReservation($reservationId) {
    $user = $this->auth->authenticate();
    $userId = $user->user_id;

    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['reservation_datetime'])) {
        $reservationDate = $data['reservation_datetime'];
        $reservationDateTime = $reservationDate . " 00:00:00";
        $data['reservation_datetime'] = $reservationDateTime;
    }

    // reservation_status validation (간단한 검증)
    if (isset($data['reservation_status'])) {
        $allowedStatuses = ['reserved', 'canceled', 'used'];
        if (!in_array($data['reservation_status'], $allowedStatuses)) {
            http_response_code(400);
            echo json_encode(['message' => '허용되지 않은 상태값입니다.'], JSON_UNESCAPED_UNICODE);
            return;
        }
    }

    $success = $this->model->update($reservationId, $userId, $data);

    if ($success) {
        echo json_encode(['message' => '예약이 수정되었습니다.'], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(['message' => '예약을 찾을 수 없거나 수정에 실패했습니다.'], JSON_UNESCAPED_UNICODE);
    }
}
}
