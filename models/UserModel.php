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

    public function getMyReservations($id) {
        $stmt = $this->pdo->prepare(
            "SELECT *
             FROM APIServer_reservation A
             JOIN APIServer_session B ON A.session_id = B.id
             JOIN APIServer_exhibition C ON B.exhibition_id = C.id
             WHERE A.user_id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // 수정됨
    }

    public function getMyPurchases($id) {
        $stmt = $this->pdo->prepare(
            "SELECT *
             FROM APIServer_user_book A
             JOIN APIServer_book B ON A.book_id = B.id
             WHERE A.user_id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // 수정됨
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
            update_dttm = NOW()
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

