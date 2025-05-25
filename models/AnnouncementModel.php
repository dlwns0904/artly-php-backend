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

        public function create($data) {
        $sql = "INSERT INTO APIServer_announcement (
                    announcement_title, user_id, announcement_poster,
                    announcement_start_datetime, announcement_end_datetime,
                    announcement_organizer, announcement_contact, announcement_support_detail,
                    announcement_site_url, announcement_attachment_url, announcement_content,
                    announcement_category
                ) VALUES (
                    :title, :user_id, :poster, :start, :end,
                    :organizer, :contact, :support_detail,
                    :site_url, :attachment_url, :content, :category
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':title' => $data['announcement_title'],
            ':user_id' => $data['user_id'],
            ':poster' => $data['announcement_poster'],
            ':start' => $data['announcement_start_datetime'],
            ':end' => $data['announcement_end_datetime'],
            ':organizer' => $data['announcement_organizer'],
            ':contact' => $data['announcement_contact'],
            ':support_detail' => $data['announcement_support_detail'],
            ':site_url' => $data['announcement_site_url'],
            ':attachment_url' => $data['announcement_attachment_url'],
            ':content' => $data['announcement_content'],
            ':category' => $data['announcement_category']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE APIServer_announcement SET
                    announcement_title = :title,
                    user_id = :user_id,
                    announcement_poster = :poster,
                    announcement_start_datetime = :start,
                    announcement_end_datetime = :end,
                    announcement_organizer = :organizer,
                    announcement_contact = :contact,
                    announcement_support_detail = :support_detail,
                    announcement_site_url = :site_url,
                    announcement_attachment_url = :attachment_url,
                    announcement_content = :content,
                    announcement_category = :category
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':title' => $data['announcement_title'],
            ':user_id' => $data['user_id'],
            ':poster' => $data['announcement_poster'],
            ':start' => $data['announcement_start_datetime'],
            ':end' => $data['announcement_end_datetime'],
            ':organizer' => $data['announcement_organizer'],
            ':contact' => $data['announcement_contact'],
            ':support_detail' => $data['announcement_support_detail'],
            ':site_url' => $data['announcement_site_url'],
            ':attachment_url' => $data['announcement_attachment_url'],
            ':content' => $data['announcement_content'],
            ':category' => $data['announcement_category']
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM APIServer_announcement WHERE id = :id");
        $stmt->execute([':id' => $id]);
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

