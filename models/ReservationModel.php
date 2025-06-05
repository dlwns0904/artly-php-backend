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
    $stmt = $this->pdo->prepare(
        "INSERT INTO APIServer_reservation
        (user_id, exhibition_id, reservation_number_of_tickets, reservation_total_price, reservation_payment_method, reservation_status, reservation_datetime, create_dtm, update_dtm)
        VALUES (:user_id, :exhibition_id, :tickets, :total_price, :payment_method, 'reserved', :reservation_datetime, NOW(), NOW())"
    );

    return $stmt->execute([
        ':user_id' => $userId,
        ':exhibition_id' => $data['exhibition_id'],
        ':tickets' => $data['number_of_tickets'],
        ':total_price' => $data['total_price'],
        ':payment_method' => $data['payment_method'],
        ':reservation_datetime' => $data['reservation_datetime']
    ]);
}




    public function cancel($reservationId, $userId) {
        $this->pdo->beginTransaction();

        try {
            // 예약 정보 가져오기
            $stmt = $this->pdo->prepare(
                "SELECT reservation_number_of_tickets
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
            //$stmt = $this->pdo->prepare(
            //   "UPDATE APIServer_session
            //     SET session_reservation_capacity = session_reservation_capacity - :dec
            //     WHERE id = :session_id"
            //);
            //$stmt->execute([
            //    ':dec' => $reservation['reservation_number_of_tickets'],
            //    ':session_id' => $reservation['session_id']
            //]);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
    public function update($reservationId, $userId, $data) {
    $fields = [];
    $params = [':id' => $reservationId, ':user_id' => $userId];

    if (isset($data['number_of_tickets'])) {
        $fields[] = "reservation_number_of_tickets = :tickets";
        $params[':tickets'] = $data['number_of_tickets'];
    }
    if (isset($data['total_price'])) {
        $fields[] = "reservation_total_price = :total_price";
        $params[':total_price'] = $data['total_price'];
    }
    if (isset($data['payment_method'])) {
        $fields[] = "reservation_payment_method = :payment_method";
        $params[':payment_method'] = $data['payment_method'];
    }
    if (isset($data['reservation_datetime'])) {
        $fields[] = "reservation_datetime = :reservation_datetime";
        $params[':reservation_datetime'] = $data['reservation_datetime'];
    }
    if (isset($data['reservation_status'])) {
        $fields[] = "reservation_status = :reservation_status";
        $params[':reservation_status'] = $data['reservation_status'];
    }

    if (empty($fields)) return false;

    $sql = "UPDATE APIServer_reservation SET " . implode(", ", $fields) . ", update_dtm = NOW() 
            WHERE id = :id AND user_id = :user_id";

    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute($params);
}

   
}

