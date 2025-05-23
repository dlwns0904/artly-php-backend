<?php
namespace Models;

use \PDO;

class ArtistModel {
    private $pdo;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $this->pdo = new PDO($config['dsn'], $config['user'], $config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function fetchArtists($category) {
        if ($category === 'onExhibition') {
            $sql = "
                SELECT DISTINCT a.id, a.artist_name AS name, a.artist_category AS field
                FROM APIServer_artist a
                JOIN APIServer_exhibition_participation ep ON a.id = ep.artist_id
                JOIN APIServer_exhibition e ON ep.exhibition_id = e.id
                WHERE CURDATE() BETWEEN e.exhibition_start_date AND e.exhibition_end_date
            ";
        } else {
            $sql = "
                SELECT id, artist_name AS name, artist_category AS field
                FROM APIServer_artist
            ";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT id, artist_name AS name, artist_category AS field,
                   artist_image AS imageUrl, artist_nation AS nation,
                   artist_description AS description
            FROM APIServer_artist
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "
            INSERT INTO APIServer_artist (
                artist_name, artist_category, artist_image,
                artist_nation, artist_description
            ) VALUES (
                :name, :category, :image, :nation, :description
            )
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name' => $data['artist_name'],
            ':category' => $data['artist_category'],
            ':image' => $data['artist_image'],
            ':nation' => $data['artist_nation'],
            ':description' => $data['artist_description']
        ]);
        $id = $this->pdo->lastInsertId();
        return $this->getById($id);
    }

    public function update($id, $data) {
        $sql = "
            UPDATE APIServer_artist SET
                artist_name = :name,
                artist_category = :category,
                artist_image = :image,
                artist_nation = :nation,
                artist_description = :description
            WHERE id = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':name' => $data['artist_name'],
            ':category' => $data['artist_category'],
            ':image' => $data['artist_image'],
            ':nation' => $data['artist_nation'],
            ':description' => $data['artist_description']
        ]);
        return $this->getById($id);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM APIServer_artist WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function getArtistsBySearch($filters = []) {
        $search = $filters['search'];
        $stmt = $this->pdo->prepare("SELECT * FROM APIServer_artist WHERE artist_name LIKE :search");
        $stmt->execute([':search' => "%$search%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

