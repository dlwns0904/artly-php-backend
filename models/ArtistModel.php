<?php
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
}
