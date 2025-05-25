<?php
namespace Models;

use PDO;

class UserModel {
    private $pdo;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $this->pdo = new PDO($config['dsn'], $config['user'], $config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_user WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByLoginId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_user WHERE login_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_user WHERE user_email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    # 사용자의 예매 정보 가져오기
    public function getMyReservations($id) {
        $stmt = $this->pdo->prepare(
            "SELECT A.*, B.session_datetime, B.session_total_capacity, B.session_reservation_capacity, 
                    C.exhibition_title, C.exhibition_poster, C.exhibition_category, C.exhibition_start_date, C.exhibition_end_date, C.exhibition_start_time, C.exhibition_end_time,
                    C.exhibition_location, C.exhibition_price, C.gallery_id, C.exhibition_tag, C.exhibition_status
             FROM APIServer_reservation A, APIServer_session B, APIServer_exhibition C
             WHERE A.session_id = B.id AND B.exhibition_id = C.id
             AND A.user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    # 사용자의 구매(도록) 정보 가져오기
    public function getMyPurchases($id) {
        $stmt = $this->pdo->prepare(
            "SELECT *
             FROM APIServer_user_book A, APIServer_book B
             WHERE A.book_id = B.id
             AND A.user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    # 사용자의 좋아요한 전시회 정보 가져오기
    public function getMyLikeExhibitions($userId) {
        $stmt = $this->pdo->prepare(
            "SELECT A.*
             FROM APIServer_exhibition A, APIServer_like B
             WHERE B.liked_type = 'exhibition' 
             AND A.id = B.liked_id 
             AND B.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO APIServer_user 
            (login_id, login_pwd, user_name, user_gender, user_age, user_email, user_phone, user_img, user_keyword, admin_flag, gallery_id, last_login_time, reg_time, update_dtm)
            VALUES (:userId, :password, :name, :gender, :age, :email, :phone, :img, :keyword, :admin_flag, :gallery_id, NOW(), NOW(), NOW())
        ");

        $stmt->execute([
            ':userId' => $data['login_id'],
            ':password' => $data['login_pwd'],
            ':name' => $data['user_name'],
            ':gender' => $data['user_gender'],
            ':age' => $data['user_age'],
            ':email' => $data['user_email'],
            ':phone' => $data['user_phone'],
            ':img' => $data['user_img'],
            ':keyword' => $data['user_keyword'],
            ':admin_flag' => $data['admin_flag'],
            ':gallery_id' => $data['gallery_id']
        ]);

        // 생성된 데이터의 ID 가져오기
        $id = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_user WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE APIServer_user SET
            login_id = :userId,
            login_pwd = :password,
            user_name = :name,
            user_gender = :gender,
            user_age = :age,
            user_email = :email,
            user_phone = :phone,
            user_img = :img,
            user_keyword = :keyword,
            admin_flag = :admin_flag,
            gallery_id = :gallery_id,
            update_dtm = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            ':userId' => $data['login_id'],
            ':password' => $data['login_pwd'],
            ':name' => $data['user_name'],
            ':gender' => $data['user_gender'],
            ':age' => $data['user_age'],
            ':email' => $data['user_email'],
            ':phone' => $data['user_phone'],
            ':img' => $data['user_img'],
            ':keyword' => $data['user_keyword'],
            ':admin_flag' => $data['admin_flag'],
            ':gallery_id' => $data['gallery_id'],
            ':id' => $id
        ]);
    }
}
