<?php
namespace Models;

use \PDO;

class SessionModel {
    private $pdo;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $this->pdo = new PDO($config['dsn'], $config['user'], $config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

public function getSessionsByDate($exhibitionId, $date) {
    $stmt = $this->pdo->prepare(
        "SELECT 
            id AS session_id,
            TIME_FORMAT(session_datetime, '%H:%i') AS time,
            session_total_capacity - session_reservation_capacity AS available_capacity
         FROM APIServer_session
         WHERE exhibition_id = :exhibition_id
           AND DATE(session_datetime) = :date
         ORDER BY session_datetime ASC"
    );

    $stmt->execute([
        ':exhibition_id' => $exhibitionId,
        ':date' => $date
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 여기서 숫자 캐스팅 처리
    foreach ($results as &$row) {
        $row['session_id'] = (int) $row['session_id'];
        $row['available_capacity'] = (int) $row['available_capacity'];
    }

    return $results;
}



}

