<?php
namespace Controllers;

use OpenApi\Annotations as OA;

use Models\ArtistModel;


class ArtistController {
    private $model;
    public function __construct() { $this->model = new ArtistModel(); }

    /**
     * @OA\Get(
     *   path="/api/artist",
     *   summary="작가 목록 조회",
     *   tags={"Artist"},
     *   @OA\Parameter(
     *     name="category", in="query",
     *     description="카테고리(all | onExhibition)", @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200, description="성공",
     *     @OA\JsonContent(type="array", @OA\Items(
     *       @OA\Property(property="id",    type="integer", example=1),
     *       @OA\Property(property="name",  type="string",  example="김길동"),
     *       @OA\Property(property="img",   type="string",  example="image.url"), 	
     *       @OA\Property(property="field", type="string",  example="회화")
     *     ))
     *   )
     * )
     */
    public function getArtistList() {
        $category = $_GET['category'] ?? 'all';
        $artists  = $this->model->fetchArtists($category);

        header('Content-Type: application/json');
        echo json_encode($artists, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Get(
     *   path="/api/artists/{id}",
     *   summary="작가 상세 조회",
     *   tags={"Artist"},
     *   @OA\Parameter(name="id", in="path", required=true,
     *       @OA\Schema(type="integer", example=1)),
     *   @OA\Response(
     *       response=200, description="성공",
     *       @OA\JsonContent(@OA\Property(property="id",   type="integer"),
     *                        @OA\Property(property="name", type="string"),
     *                        @OA\Property(property="field",type="string"))
     *   ),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function getArtistById($id) {
        $artist = $this->model->getById($id);
        if ($artist) {
            header('Content-Type: application/json');
            echo json_encode($artist, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Artist not found']);
        }
    }
}

