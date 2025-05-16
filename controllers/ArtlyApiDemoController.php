<?php
namespace Controllers;
use OpenApi\Annotations as OA;

/*
 * @OA\Tag(
 *     name="Search",
 *     description="검색 관련 API"
 * )
 */
class ArtlyApiDemoController {


    /**
     * @OA\Get(
     *     path="/api/search",
     *     tags={"Search"},
     *     summary="통합 검색 (작가, 전시회, 갤러리, 공고) (개발 중)",
     *     @OA\Parameter(name="query", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="검색 결과 반환",
     *         @OA\JsonContent(
     *             @OA\Property(property="results", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="thumbnailUrl", type="string"),
     *                 @OA\Property(property="type", type="string", enum={"exhibition", "artist", "gallery", "announcement"})
     *             ))
     *         )
     *     )
     * )
     */
    public function search() {}
}

