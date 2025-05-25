<?php
namespace Controllers;

use OpenApi\Annotations as OA;

use Models\AnnouncementModel;


/**
 * @OA\Tag(
 *     name="Announcement",
 *     description="공고 관련 API"
 * )
 */
class AnnouncementController {
    private $model;

    public function __construct() {
        $this->model = new AnnouncementModel();
    }

        /**
     * @OA\Post(
     *     path="/api/announcements",
     *     summary="공고 생성",
     *     tags={"Announcement"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="announcement_title", type="string"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="announcement_poster", type="string"),
     *             @OA\Property(property="announcement_start_datetime", type="string", format="date-time"),
     *             @OA\Property(property="announcement_end_datetime", type="string", format="date-time"),
     *             @OA\Property(property="announcement_organizer", type="string"),
     *             @OA\Property(property="announcement_contact", type="string"),
     *             @OA\Property(property="announcement_support_detail", type="string"),
     *             @OA\Property(property="announcement_site_url", type="string"),
     *             @OA\Property(property="announcement_attachment_url", type="string"),
     *             @OA\Property(property="announcement_content", type="string"),
     *             @OA\Property(property="announcement_category", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="공고 생성 완료"
     *     )
     * )
     */
    public function createAnnouncement() {
        $data = json_decode(file_get_contents("php://input"), true);
        $this->model->create($data);
        http_response_code(201);
        echo json_encode(['message' => 'Announcement created'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Put(
     *     path="/api/announcements/{id}",
     *     summary="공고 수정",
     *     tags={"Announcement"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="announcement_title", type="string"),
     *             ...
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="공고 수정 완료"
     *     )
     * )
     */
    public function updateAnnouncement($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $this->model->update($id, $data);
        echo json_encode(['message' => 'Announcement updated'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Delete(
     *     path="/api/announcements/{id}",
     *     summary="공고 삭제",
     *     tags={"Announcement"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="공고 삭제 완료"
     *     )
     * )
     */
    public function deleteAnnouncement($id) {
        $this->model->delete($id);
        echo json_encode(['message' => 'Announcement deleted'], JSON_UNESCAPED_UNICODE);
    }




    /**
     * @OA\Get(
     *     path="/api/announcements",
     *     summary="공고 목록 조회",
     *     tags={"Announcement"},
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="공고 카테고리 (공모, 프로그램, 채용)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="공고 목록 조회 성공",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function getAnnouncementList() {
        $category = $_GET['category'] ?? null;
        $announcements = $this->model->getAnnouncements($category);
        header('Content-Type: application/json');
        echo json_encode($announcements, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Get(
     *     path="/api/announcements/{id}",
     *     summary="공고 상세 조회",
     *     tags={"Announcement"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="공고 ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="공고 상세 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="announcement_title", type="string"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="announcement_poster", type="string"),
     *             @OA\Property(property="announcement_start_datetime", type="string", format="date-time"),
     *             @OA\Property(property="announcement_end_datetime", type="string", format="date-time"),
     *             @OA\Property(property="announcement_organizer", type="string"),
     *             @OA\Property(property="announcement_contact", type="string"),
     *             @OA\Property(property="announcement_support_detail", type="string"),
     *             @OA\Property(property="announcement_site_url", type="string"),
     *             @OA\Property(property="announcement_attachment_url", type="string"),
     *             @OA\Property(property="announcement_content", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="공고 없음"
     *     )
     * )
     */
    public function getAnnouncementById($id) {
        $announcement = $this->model->getById($id);
        if ($announcement) {
            header('Content-Type: application/json');
            echo json_encode($announcement, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Announcement not found']);
        }
    }
}

