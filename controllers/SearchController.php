<?php
namespace Controllers;

use OpenApi\Annotations as OA;

use Models\ExhibitionModel;
use Models\GalleryModel;
use Models\ArtistModel;
use Models\AnnouncementModel;

/**
 * @OA\Tag(
 *     name="Search",
 *     description="검색 관련 API"
 * )
 */
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

    /**
     * @OA\Get(
     *     path="/api/search",
     *     summary="검색 결과 조회",
     *     tags={"Search"},
     *     @OA\Parameter(name="search", in="query", description="검색단어", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="exhibitions",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="exhibition_title", type="string"),
     *                     @OA\Property(property="exhibition_poster", type="string"),
     *                     @OA\Property(property="exhibition_category", type="string"),
     *                     @OA\Property(property="exhibition_start_date", type="string", format="date"),
     *                     @OA\Property(property="exhibition_end_date", type="string", format="date"),
     *                     @OA\Property(property="exhibition_start_time", type="string", format="date-time"),
     *                     @OA\Property(property="exhibition_end_time", type="string", format="date-time"),
     *                     @OA\Property(property="exhibition_location", type="string"),
     *                     @OA\Property(property="exhibition_price", type="integer"),
     *                     @OA\Property(property="gallery_id", type="integer"),
     *                     @OA\Property(property="exhibition_tag", type="string"),
     *                     @OA\Property(property="exhibition_status", type="string"),
     *                     @OA\Property(property="create_dtm", type="string", format="date-time"),
     *                     @OA\Property(property="update_dtm", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="galleries",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="gallery_name", type="string"),
     *                     @OA\Property(property="gallery_image", type="string"),
     *                     @OA\Property(property="gallery_address", type="string"),
     *                     @OA\Property(property="gallery_start_time", type="string", format="date-time"),
     *                     @OA\Property(property="gallery_end_time", type="string", format="date-time"),
     *                     @OA\Property(property="gallery_closed_day", type="string", format="date-time"),
     *                     @OA\Property(property="gallery_category", type="string"),
     *                     @OA\Property(property="gallery_description", type="string"),
     *                     @OA\Property(property="create_dtm", type="string", format="date-time"),
     *                     @OA\Property(property="update_dtm", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="artists",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="artist_image", type="string"),
     *                     @OA\Property(property="artist_name", type="string"),
     *                     @OA\Property(property="artist_category", type="string"),
     *                     @OA\Property(property="artist_nation", type="string"),
     *                     @OA\Property(property="artist_description", type="string"),
     *                     @OA\Property(property="create_dtm", type="string", format="date-time"),
     *                     @OA\Property(property="update_dtm", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="announcements",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="user_id", type="integer"),
     *                     @OA\Property(property="announcement_title", type="string"),
     *                     @OA\Property(property="announcement_poster", type="string"),
     *                     @OA\Property(property="announcement_start_datetime", type="string", format="date-time"),
     *                     @OA\Property(property="announcement_end_datetime", type="string", format="date-time"),
     *                     @OA\Property(property="announcement_organizer", type="string"),
     *                     @OA\Property(property="announcement_contact", type="string"),
     *                     @OA\Property(property="announcement_support_detail", type="string"),
     *                     @OA\Property(property="announcement_site_url", type="string"),
     *                     @OA\Property(property="announcement_attachment_url", type="string"),
     *                     @OA\Property(property="content", type="string"),
     *                     @OA\Property(property="create_dtm", type="string", format="date-time"),
     *                     @OA\Property(property="update_dtm", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
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
