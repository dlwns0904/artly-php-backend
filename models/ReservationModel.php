<?php
namespace Models;

use \PDO;

class ReservationModel {
    private $pdo;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $this->pdo = new PDO($config['dsn'], $config['user'], $config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function create($userId, $data) {
        $this->pdo->beginTransaction();

        try {
            // 세션에서 남은 좌석 체크
            $stmt = $this->pdo->prepare(
                "SELECT session_total_capacity, session_reservation_capacity 
                 FROM APIServer_session WHERE id = :session_id"
            );
            $stmt->execute([':session_id' => $data['session_id']]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$session) throw new \Exception("세션 없음");
            $available = $session['session_total_capacity'] - $session['session_reservation_capacity'];
            if ($available < $data['number_of_tickets']) throw new \Exception("좌석 부족");

            // 예약 테이블에 삽입
            $stmt = $this->pdo->prepare(
                "INSERT INTO APIServer_reservation 
                (user_id, session_id, reservation_number_of_tickets, reservation_total_price, reservation_payment_method, reservation_status, reservation_datetime, create_dtm, update_dtm)
                VALUES (:user_id, :session_id, :tickets, :total_price, :payment_method, 'reserved', NOW(), NOW(), NOW())"
            );
            $stmt->execute([
                ':user_id' => $userId,
                ':session_id' => $data['session_id'],
                ':tickets' => $data['number_of_tickets'],
                ':total_price' => $data['total_price'],
                ':payment_method' => $data['payment_method']
            ]);

            // 세션 예약 수량 증가
            $stmt = $this->pdo->prepare(
                "UPDATE APIServer_session 
                 SET session_reservation_capacity = session_reservation_capacity + :inc 
                 WHERE id = :session_id"
            );
            $stmt->execute([
                ':inc' => $data['number_of_tickets'],
                ':session_id' => $data['session_id']
            ]);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function cancel($reservationId, $userId) {
        $this->pdo->beginTransaction();

        try {
            // 예약 정보 가져오기
            $stmt = $this->pdo->prepare(
                "SELECT session_id, reservation_number_of_tickets
                 FROM APIServer_reservation
                 WHERE id = :id AND user_id = :user_id AND reservation_status = 'reserved'"
            );
            $stmt->execute([
                ':id' => $reservationId,
                ':user_id' => $userId
            ]);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$reservation) throw new \Exception("예약 없음");

            // 예약 상태 변경
            $stmt = $this->pdo->prepare(
                "UPDATE APIServer_reservation
                 SET reservation_status = 'canceled', update_dtm = NOW()
                 WHERE id = :id"
            );
            $stmt->execute([':id' => $reservationId]);

            // 세션 예약 수량 감소
            $stmt = $this->pdo->prepare(
                "UPDATE APIServer_session
                 SET session_reservation_capacity = session_reservation_capacity - :dec
                 WHERE id = :session_id"
            );
            $stmt->execute([
                ':dec' => $reservation['reservation_number_of_tickets'],
                ':session_id' => $reservation['session_id']
            ]);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
