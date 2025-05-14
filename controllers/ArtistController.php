<?php
require_once __DIR__ . '/../models/ArtistModel.php';

class ArtistController {
    public function getArtistList() {
        $category = $_GET['category'] ?? 'all';
        $model = new ArtistModel();
        $artists = $model->fetchArtists($category);

        header('Content-Type: application/json');
        echo json_encode($artists, JSON_UNESCAPED_UNICODE);
    }
}
