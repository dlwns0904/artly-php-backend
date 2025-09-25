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
        $sql = "SELECT id, gallery_name AS name, gallery_image AS image FROM APIServer_gallery WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM APIServer_exhibition e
                WHERE e.gallery_id = APIServer_gallery.id
                AND e.exhibition_status = :status
            )";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['region'])) {
            $sql .= " AND gallery_address LIKE :region";
            $params[':region'] = '%' . $filters['region'] . '%';
        }

        if (!empty($filters['type'])) {
            $sql .= " AND gallery_category = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['latitude']) && !empty($filters['longitude']) && !empty($filters['distance'])) {
            $sql .= " AND (
                6371000 * ACOS(
                    COS(RADIANS(:latitude)) * COS(RADIANS(gallery_latitude)) *
                    COS(RADIANS(gallery_longitude) - RADIANS(:longitude)) +
                    SIN(RADIANS(:latitude)) * SIN(RADIANS(gallery_latitude))
                )
            ) <= :distance";
            $params[':latitude'] = $filters['latitude'];
            $params[':longitude'] = $filters['longitude'];
            $params[':distance'] = $filters['distance'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT id, gallery_name, gallery_image, gallery_address, gallery_start_time,
                   gallery_end_time, gallery_closed_day, gallery_category, gallery_description
            FROM APIServer_gallery
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getGalleriesBySearch($filters = []) {
        $search = $filters['search'];
        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_gallery WHERE gallery_name LIKE :search");
        $stmt->execute([':search' => "%$search%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
