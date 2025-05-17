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

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
    // 갤러리 상세 정보
    $stmt = $this->pdo->prepare("
        SELECT id, gallery_name, gallery_image, gallery_address, gallery_start_time,
               gallery_end_time, gallery_closed_day, gallery_category, gallery_description
        FROM APIServer_gallery
        WHERE id = :id
    ");
    $stmt->execute([':id' => $id]);
    $gallery = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gallery) {
        return null;
    }

    // + 해당 갤러리의 전시 중인 전시들
    $stmt2 = $this->pdo->prepare("
        SELECT id, exhibition_title, exhibition_poster, exhibition_start_date, exhibition_end_date
        FROM APIServer_exhibition
        WHERE gallery_id = :id AND exhibition_status = 'exhibited'
    ");
    $stmt2->execute([':id' => $id]);
    $exhibitions = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // 결과에 +
    $gallery['exhibitions'] = $exhibitions;

    // 반환
    return $gallery;
}


}

