<?php
namespace Models;

use \PDO;

class ExhibitionModel {
    private $pdo;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $this->pdo = new PDO($config['dsn'], $config['user'], $config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getExhibitions($filters = []) {
    $sql = "SELECT
                A.*,
                IFNULL(B.like_count, 0) AS like_count,
                IF(EXISTS (
                    SELECT 1 FROM APIServer_exhibition_like L
                    WHERE L.exhibition_id = A.id AND L.user_id = :user_id_for_like
                ), 1, 0) AS is_liked,
                C.gallery_name, C.gallery_image, C.gallery_address, C.gallery_start_time, C.gallery_end_time,
                C.gallery_closed_day, C.gallery_category, C.gallery_description
            FROM APIServer_exhibition A
            LEFT JOIN (
                SELECT exhibition_id, COUNT(*) as like_count
                FROM APIServer_exhibition_like
                GROUP BY exhibition_id
            ) B ON A.id = B.exhibition_id
            LEFT JOIN APIServer_gallery C ON A.gallery_id = C.id
            WHERE 1=1 ";

    $user_id = $filters['user_id'] ?? 0;
    $params = [':user_id_for_like' => $user_id];

    if (!empty($filters['status'])) {
        $sql .= "AND A.exhibition_status = :status ";
        $params[':status'] = $filters['status'];
    }
    if (!empty($filters['category'])) {
        $sql .= "AND A.exhibition_category = :category ";
        $params[':category'] = $filters['category'];
    }
    if (!empty($filters['region'])) {
        $regions = explode(',', $filters['region']);
        $regionConditions = [];
        foreach ($regions as $index => $region) {
            $placeholder = ":region_$index";
            $regionConditions[] = "A.exhibition_location LIKE $placeholder";
            $params[$placeholder] = '%' . trim($region) . '%';
        }
        $sql .= "AND (" . implode(" OR ", $regionConditions) . ") ";
    }

    $likedOnly = !empty($filters['liked_only']) && filter_var($filters['liked_only'], FILTER_VALIDATE_BOOLEAN);
    if ($likedOnly && !empty($user_id)) {
        $sql .= "AND EXISTS (
                    SELECT 1 FROM APIServer_exhibition_like L
                    WHERE L.exhibition_id = A.id AND L.user_id = :user_id_only
                ) ";
        $params[':user_id_only'] = $user_id;
    }

    if (!empty($filters['sort'])) {
        switch ($filters['sort']) {
            case 'latest': $sql .= "ORDER BY A.create_dtm DESC "; break;
            case 'ending': $sql .= "ORDER BY A.exhibition_end_date ASC "; break;
            case 'popular': $sql .= "ORDER BY like_count DESC "; break;
            default: $sql .= "ORDER BY A.create_dtm DESC ";
        }
    } else {
        $sql .= "ORDER BY A.create_dtm DESC ";
    }

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 여기서 가공 시작
    $results = [];

    foreach ($rows as $row) {
        $results[] = [
            "id" => (int)$row['id'],
            "exhibition_title" => $row['exhibition_title'],
            "exhibition_poster" => $row['exhibition_poster'],
            "exhibition_category" => $row['exhibition_category'],
            "exhibition_start_date" => $row['exhibition_start_date'],
            "exhibition_end_date" => $row['exhibition_end_date'],
            "exhibition_start_time" => $row['exhibition_start_time'],
            "exhibition_end_time" => $row['exhibition_end_time'],
            "exhibition_location" => $row['exhibition_location'],
            "exhibition_price" => (int)$row['exhibition_price'],
            "exhibition_tag" => $row['exhibition_tag'],
            "exhibition_status" => $row['exhibition_status'],
            "create_dtm" => $row['create_dtm'],
            "update_dtm" => $row['update_dtm'],
            "like_count" => (int)$row['like_count'],
            "is_liked" => (bool)$row['is_liked'],
            "gallery_id" => (int)$row['gallery_id'],
            "exhibition_organization" => [
                "name" => $row['gallery_name'],
                "image" => $row['gallery_image'] ?? null,
                "address" => $row['gallery_address'],
                "start_time" => $row['gallery_start_time'] ?? null,
                "end_time" => $row['gallery_end_time'] ?? null,
                "closed_day" => $row['gallery_closed_day'] ?? null,
                "category" => $row['gallery_category'] ?? null,
                "description" => $row['gallery_description'] ?? null
            ]
        ];
    }

    return $results;
}

    public function getById($id, $user_id = null) {
        $sql = "SELECT
                    A.*,
                    IFNULL(B.like_count, 0) AS like_count,
                    IF(EXISTS (
                        SELECT 1 FROM APIServer_exhibition_like L
                        WHERE L.exhibition_id = A.id AND L.user_id = :user_id_check
                    ), 1, 0) AS is_liked
                FROM APIServer_exhibition A
                LEFT JOIN (
                    SELECT exhibition_id, COUNT(*) as like_count
                    FROM APIServer_exhibition_like
                    GROUP BY exhibition_id
                ) B ON A.id = B.exhibition_id
                WHERE A.id = :id";

        $params = [
            ':id' => $id,
            ':user_id_check' => $user_id ?? 0
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO APIServer_exhibition
            (exhibition_title, exhibition_poster, exhibition_category, exhibition_start_date, exhibition_end_date, exhibition_start_time, exhibition_end_time, exhibition_location, exhibition_price, gallery_id, exhibition_tag, exhibition_status, create_dtm, update_dtm)
            VALUES (:title, :poster, :category, :start_date, :end_date, :start_time, :end_time, :location, :price, :gallery_id, :tag, :status, NOW(), NOW())");

        $stmt->execute([
            ':title' => $data['exhibition_title'],
            ':poster' => $data['exhibition_poster'],
            ':category' => $data['exhibition_category'],
            ':start_date' => $data['exhibition_start_date'],
            ':end_date' => $data['exhibition_end_date'],
            ':start_time' => $data['exhibition_start_time'],
            ':end_time' => $data['exhibition_end_time'],
            ':location' => $data['exhibition_location'],
            ':price' => $data['exhibition_price'],
            ':gallery_id' => $data['gallery_id'],
            ':tag' => $data['exhibition_tag'],
            ':status' => $data['exhibition_status']
        ]);

        // 생성된 데이터의 ID 가져오기
        $id = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_exhibition WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE APIServer_exhibition SET
            exhibition_title = :title,
            exhibition_poster = :poster,
            exhibition_category = :category,
            exhibition_start_date = :start_date,
            exhibition_end_date = :end_date,
            exhibition_start_time = :start_time,
            exhibition_end_time = :end_time,
            exhibition_location = :location,
            exhibition_price = :price,
            gallery_id = :gallery_id,
            exhibition_tag = :tag,
            exhibition_status = :status,
            update_dtm = NOW()
            WHERE id = :id");

        return $stmt->execute([
            ':title' => $data['exhibition_title'],
            ':poster' => $data['exhibition_poster'],
            ':category' => $data['exhibition_category'],
            ':start_date' => $data['exhibition_start_date'],
            ':end_date' => $data['exhibition_end_date'],
            ':start_time' => $data['exhibition_start_time'],
            ':end_time' => $data['exhibition_end_time'],
            ':location' => $data['exhibition_location'],
            ':price' => $data['exhibition_price'],
            ':gallery_id' => $data['gallery_id'],
            ':tag' => $data['exhibition_tag'],
            ':status' => $data['exhibition_status'],
            ':id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM APIServer_exhibition WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getExhibitionsBySearch($filters = []) {
        $search = $filters['search'];
        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_exhibition WHERE exhibition_title LIKE :search");
        $stmt->execute([':search' => "%$search%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
