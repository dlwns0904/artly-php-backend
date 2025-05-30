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

    # 전시회 목록
    public function getExhibitions($filters = []) {
        $sql = "SELECT A.*, case when B.like_count is null then 0 else B.like_count  end AS like_count
                FROM APIServer_exhibition A LEFT JOIN (SELECT exhibition_id, count(*) as like_count FROM APIServer_exhibition_like group by exhibition_id) B
                ON A.id = B.exhibition_id
                WHERE 1=1";
        $params = [];

        # 전시 상태
        if (!empty($filters['status'])) {
            $sql .= " AND exhibition_status = :status";
            $params[':status'] = $filters['status'];
        }
        # 전시 카테고리
        if (!empty($filters['category'])) {
            $sql .= " AND exhibition_category = :category";
            $params[':category'] = $filters['category'];
        }
        # 지역 -> 콤마(,)로 구분하여 입력
        if (!empty($filters['region'])) {
            $regions = preg_split(',', $filters['region']);
            $regionConditions = [];
            foreach ($regions as $index => $region) {
                $placeholder = ":region_$index";
                $regionConditions[] = "exhibition_location LIKE $placeholder";
                $params[$placeholder] = '%' . trim($region) . '%';
            }
            if (!empty($regionConditions)) {
                $sql .= " AND (" . implode(" OR ", $regionConditions) . ")";
            }
        }
        # 정렬 순서
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'latest': # 최신순
                    $orderBy = 'create_dttm DESC';
                    break;
                case 'ending': # 종료순
                    $orderBy = 'exhibition_end_date';
                    break;
                case 'popular': # 인기순
                    $orderBy = 'like_count DESC';
                    break;
            }
            $sql .= " ORDER BY {$orderBy}";
        }
        

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_exhibition WHERE id = :id");
        $stmt->execute(['id' => $id]);
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

