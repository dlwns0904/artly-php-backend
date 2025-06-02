<?php
namespace Models;

use \PDO;


class BookModel {
    private $pdo;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $this->pdo = new PDO($config['dsn'], $config['user'], $config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    # 도록에 대한 기본 정보
    public function getBookInfoById($id) {
        $stmt = $this->pdo->prepare("SELECT *
                                     FROM APIServer_book A, APIServer_exhibition B, APIServer_gallery C
                                     WHERE A.exhibition_id = B.id and B.gallery_id = C.id and A.id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // 응답 형태 가공
        $result = [
            'book' => [
                'id' => $row['id'],
                'book_title' => $row['book_title'],
                'book_poster' => $row['book_poster'],
            ],
            'exhibition' => [
                'id' => $row['exhibition_id'],
                'exhibition_title' => $row['exhibition_title'],
                'exhibition_start_date' => $row['exhibition_start_date'],
                'exhibition_end_date' => $row['exhibition_end_date'],
                'exhibition_location' => $row['exhibition_location'],
            ],
            'gallery' => [
                'id' => $row['gallery_id'],
                'gallery_name' => $row['gallery_name'],
                'gallery_latitude' => $row['gallery_latitude'],
                'gallery_longitude' => $row['gallery_longitude'],
            ],
        ];

        return $result;
    }

    # 도록 상세 페이지 목록
    public function getBookPages($book_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_book_page WHERE book_id = :book_id ORDER BY book_page_sequence");
        $stmt->execute(['book_id' => $book_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO APIServer_book
            (book_title, book_poster, exhibition_id, create_dtm, update_dtm)
            VALUES (:title, :poster, :exhibition_id, NOW(), NOW())");

        $stmt->execute([
            ':title' => $data['book_title'],
            ':poster' => $data['book_poster'],
            ':exhibition_id' => $data['exhibition_id']
        ]);

        // 생성된 데이터의 ID 가져오기
        $id = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_book WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE APIServer_book SET
            book_title = :title,
            book_poster = :poster,
            exhibition_id = :exhibition_id,
            update_dtm = NOW()
            WHERE id = :id");

        return $stmt->execute([
            ':title' => $data['book_title'],
            ':poster' => $data['book_poster'],
            ':exhibition_id' => $data['exhibition_id'],
            ':id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM APIServer_book WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}

