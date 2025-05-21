<?php
namespace Models;

use \PDO;


class ArtModel {
    private $pdo;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $this->pdo = new PDO($config['dsn'], $config['user'], $config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAll($filters = []) {
        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_art");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_art WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO APIServer_art 
            (art_image, artist_id, art_title, art_description, art_docent, create_dtm, update_dtm)
            VALUES (:image, :artist_id, :title, :description, :docent, NOW(), NOW())");

        $stmt->execute([
            ':image' => $data['art_image'],
            ':artist_id' => $data['artist_id'],
            ':title' => $data['art_title'],
            ':description' => $data['art_description'],
            ':docent' => $data['art_docent']
        ]);
        
        // 생성된 데이터의 ID 가져오기
        $id = $this->pdo->lastInsertId();
        
        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_art WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE APIServer_art SET
            art_image = :image,
            artist_id = :artist_id,
            art_title = :title,
            art_description = :description,
            art_docent = :docent,
            update_dtm = NOW()
            WHERE id = :id");

        return $stmt->execute([
            ':image' => $data['art_image'],
            ':artist_id' => $data['artist_id'],
            ':title' => $data['art_title'],
            ':description' => $data['art_description'],
            ':docent' => $data['art_docent'],
            ':id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM APIServer_art WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
