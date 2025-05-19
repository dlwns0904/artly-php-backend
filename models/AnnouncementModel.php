<?php
namespace Models;

use \PDO;

class AnnouncementModel {
    private $pdo;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $this->pdo = new PDO($config['dsn'], $config['user'], $config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAnnouncements($category = null) {
        $sql = "SELECT id, announcement_title AS title FROM APIServer_announcement WHERE 1=1";
        $params = [];

        if (!empty($category)) {
            $sql .= " AND announcement_category = :category";
            $params[':category'] = $category;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT id, announcement_title, user_id, announcement_poster, 
                   announcement_start_datetime, announcement_end_datetime,
                   announcement_organizer, announcement_contact, announcement_support_detail,
                   announcement_site_url, announcement_attachment_url, announcement_content
            FROM APIServer_announcement
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAnnouncementsBySearch($filters = []) {
        $search = $filters['search'];
        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_announcement WHERE announcement_title LIKE :search");
        $stmt->execute([':search' => "%$search%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

