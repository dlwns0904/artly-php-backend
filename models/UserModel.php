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
            "SELECT *
             FROM APIServer_reservation A, APIServer_exhibition B, APIServer_user C
             WHERE A.exhibition_id = B.id AND A.user_id = C.id;
             AND A.user_id = ?");
        $stmt->execute([$id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results = [];

        # 응답 형식 가공
        foreach ($rows as $row) {
            $results[] = [
                "id" => (int)$row['id'],
                "user_id" => (int)$row['user_id'],
                "exhibition_id" => (int)$row['exhibition_id'],
                "reservation_datetime" => $row['reservation_datetime'],
                "reservation_number_of_tickets" => $row['reservation_number_of_tickets'],
                "reservation_total_price" => $row['reservation_total_price'],
                "reservation_payment_method" => $row['reservation_payment_method'],
                "reservation_status" => $row['reservation_status'],
                "create_dtm" => $row['create_dtm'],
                "update_dtm" => $row['update_dtm'],
                "exhibition_title" => $row['exhibition_title'],
                "exhibition_poster" => $row['exhibition_poster'],
                "exhibition_category" => $row['exhibition_category'],
                "exhibition_start_date" => $row['exhibition_start_date'],
                "exhibition_end_date" => $row['exhibition_end_date'],
                "exhibition_start_time" => $row['exhibition_start_time'],
                "exhibition_end_time" => $row['exhibition_end_time'],
                "exhibition_location" => $row['exhibition_location'],
                "exhibition_price" => (int)$row['exhibition_price'],
                "gallery_id" => (int)$row['gallery_id'],
                "exhibition_tag" => $row['exhibition_tag'],
                "exhibition_status" => $row['exhibition_status'],
                "visitor_name" => $row['user_name'],
                "visitor_email" => $row['user_email'],
                "visitor_phone" => $row['user_phone']
            ];
        }

        return $results;
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
             FROM APIServer_exhibition A, APIServer_exhibition_like B
             WHERE A.id = B.exhibition_id 
             AND B.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    # 사용자의 좋아요한 갤러리 정보 가져오기
    public function getMyLikeGalleries($userId) {
        $stmt = $this->pdo->prepare(
            "SELECT A.*
             FROM APIServer_gallery A, APIServer_gallery_like B
             WHERE A.id = B.gallery_id 
             AND B.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    # 사용자의 좋아요한 작가 정보 가져오기
    public function getMyLikeArtists($userId) {
        $stmt = $this->pdo->prepare(
            "SELECT A.*
             FROM APIServer_artist A, APIServer_artist_like B
             WHERE A.id = B.artist_id 
             AND B.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    # 사용자의 좋아요한 작품 정보 가져오기
    public function getMyLikeArts($userId) {
        $stmt = $this->pdo->prepare(
            "SELECT A.*
             FROM APIServer_art A, APIServer_art_like B
             WHERE A.id = B.art_id 
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
