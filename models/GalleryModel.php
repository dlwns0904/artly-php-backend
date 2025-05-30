<?php
namespace Models;

use \PDO;

class GalleryModel {
    private $pdo;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $this->pdo = new PDO($config['dsn'], $config['user'], $config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function create($data) {
        $sql = "INSERT INTO APIServer_gallery (
            gallery_name, gallery_image, gallery_address, gallery_start_time,
            gallery_end_time, gallery_closed_day, gallery_category, gallery_description
        ) VALUES (
            :name, :image, :address, :start_time,
            :end_time, :closed_day, :category, :description
        )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name' => $data['gallery_name'],
            ':image' => $data['gallery_image'],
            ':address' => $data['gallery_address'],
            ':start_time' => $data['gallery_start_time'],
            ':end_time' => $data['gallery_end_time'],
            ':closed_day' => $data['gallery_closed_day'],
            ':category' => $data['gallery_category'],
            ':description' => $data['gallery_description']
        ]);

        $id = $this->pdo->lastInsertId();
        return $this->getById($id);
    }

    public function update($id, $data) {
        $sql = "UPDATE APIServer_gallery SET
            gallery_name = :name,
            gallery_image = :image,
            gallery_address = :address,
            gallery_start_time = :start_time,
            gallery_end_time = :end_time,
            gallery_closed_day = :closed_day,
            gallery_category = :category,
            gallery_description = :description
        WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':name' => $data['gallery_name'],
            ':image' => $data['gallery_image'],
            ':address' => $data['gallery_address'],
            ':start_time' => $data['gallery_start_time'],
            ':end_time' => $data['gallery_end_time'],
            ':closed_day' => $data['gallery_closed_day'],
            ':category' => $data['gallery_category'],
            ':description' => $data['gallery_description']
        ]);

        return $this->getById($id);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM APIServer_gallery WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

public function getGalleries($filters = []) {
    $sql = "
        SELECT
            g.id AS gallery_id,
            g.gallery_name,
            g.gallery_image,
            g.gallery_latitude,
            g.gallery_longitude,
            g.gallery_address,
            e.id AS exhibition_id,
            e.exhibition_title,
            e.exhibition_poster,
            e.exhibition_status
        FROM APIServer_gallery g
        LEFT JOIN APIServer_exhibition e
            ON g.id = e.gallery_id AND e.exhibition_status = 'exhibited'
        WHERE 1=1
    ";
    $params = [];

    if (!empty($filters['status'])) {
        $sql .= " AND e.exhibition_status = :status";
        $params[':status'] = $filters['status'];
    }

    // 🔧 여기서 region 다중 검색 처리
    if (!empty($filters['regions'])) {
        $regionList = explode(',', $filters['regions']);
        $regionConditions = [];
        foreach ($regionList as $index => $region) {
            $key = ":region$index";
            $regionConditions[] = "g.gallery_address LIKE $key";
            $params[$key] = '%' . trim($region) . '%';
        }
        $sql .= " AND (" . implode(" OR ", $regionConditions) . ")";
    }

    if (!empty($filters['type'])) {
        $sql .= " AND g.gallery_category = :type";
        $params[':type'] = $filters['type'];
    }

    if (!empty($filters['latitude']) && !empty($filters['longitude']) && !empty($filters['distance'])) {
        $sql .= " AND (
            6371000 * ACOS(
                COS(RADIANS(:latitude)) * COS(RADIANS(g.gallery_latitude)) *
                COS(RADIANS(g.gallery_longitude) - RADIANS(:longitude)) +
                SIN(RADIANS(:latitude)) * SIN(RADIANS(g.gallery_latitude))
            )
        ) <= :distance";
        $params[':latitude'] = $filters['latitude'];
        $params[':longitude'] = $filters['longitude'];
        $params[':distance'] = $filters['distance'];
    }

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $galleries = [];
    foreach ($rows as $row) {
        $gid = $row['gallery_id'];
        if (!isset($galleries[$gid])) {
            $galleries[$gid] = [
                'id' => $gid,
                'name' => $row['gallery_name'],
                'image' => $row['gallery_image'],
                'latitude' => $row['gallery_latitude'],
                'longitude' => $row['gallery_longitude'],
                'address' => $row['gallery_address'],
                'exhibitions' => []
            ];
        }

        if (!empty($row['exhibition_id'])) {
            $galleries[$gid]['exhibitions'][] = [
                'id' => $row['exhibition_id'],
                'title' => $row['exhibition_title'],
                'poster' => $row['exhibition_poster'],
                'status' => $row['exhibition_status']
            ];
        }
    }

    return array_values($galleries);
}

    public function getById($id) {
        $sql = "
            SELECT
                g.id AS gallery_id,
                g.gallery_name,
                g.gallery_image,
                g.gallery_address,
                g.gallery_start_time,
                g.gallery_end_time,
                g.gallery_closed_day,
                g.gallery_category,
                g.gallery_description,
                g.gallery_latitude,
                g.gallery_longitude,
                e.id AS exhibition_id,
                e.exhibition_title,
                e.exhibition_poster,
                e.exhibition_status
            FROM APIServer_gallery g
            LEFT JOIN APIServer_exhibition e
                ON g.id = e.gallery_id AND e.exhibition_status = 'exhibited'
            WHERE g.id = :id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) return null;

        $row = $rows[0];
        $gallery = [
            'id' => $row['gallery_id'],
            'gallery_name' => $row['gallery_name'],
            'gallery_image' => $row['gallery_image'],
            'gallery_address' => $row['gallery_address'],
            'gallery_start_time' => $row['gallery_start_time'],
            'gallery_end_time' => $row['gallery_end_time'],
            'gallery_closed_day' => $row['gallery_closed_day'],
            'gallery_category' => $row['gallery_category'],
            'gallery_description' => $row['gallery_description'],
            'gallery_latitude' => $row['gallery_latitude'],
            'gallery_longitude' => $row['gallery_longitude'],
            'exhibitions' => []
        ];

        foreach ($rows as $row) {
            if (!empty($row['exhibition_id'])) {
                $gallery['exhibitions'][] = [
                    'id' => $row['exhibition_id'],
                    'title' => $row['exhibition_title'],
                    'poster' => $row['exhibition_poster'],
                    'status' => $row['exhibition_status']
                ];
            }
        }

        return $gallery;
    }

    public function getGalleriesBySearch($filters = []) {
        $search = $filters['search'];
        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_gallery WHERE gallery_name LIKE :search");
        $stmt->execute([':search' => "%$search%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

