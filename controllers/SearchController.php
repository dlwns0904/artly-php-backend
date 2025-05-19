<?php
namespace Controllers;

use OpenApi\Annotations as OA;

use Models\ExhibitionModel;
use Models\GalleryModel;
use Models\ArtistModel;
use Models\AnnouncementModel;

class SearchController {
    private $exhibitionModel;
    private $galleryModel;
    private $artistModel;
    private $announcementModel;

    public function __construct() {
        $this->exhibitionModel = new ExhibitionModel();
        $this->galleryModel = new GalleryModel();
        $this->artistModel = new ArtistModel();
        $this->announcementModel = new AnnouncementModel();
    }

    public function getResults() {
        // 쿼리 파라미터 읽기
        $filters = [
            'search' => $_GET['search'] ?? null,
        ];

        $exhibitions = $this->exhibitionModel->getExhibitionsBySearch($filters);
        $galleries = $this->galleryModel->getGalleriesBySearch($filters);
        $artists = $this->artistModel->getArtistsBySearch($filters);
        $announcements = $this->announcementModel->getAnnouncementsBySearch($filters);

        header('Content-Type: application/json');
        echo json_encode([
            'exhibitions' => $exhibitions,
            'galleries' => $galleries,
            'artists' => $artists,
            'announcements' => $announcements
        ], JSON_UNESCAPED_UNICODE);
    }
}
