<?php
/* ───────── 공통 ───────── */
$requestUri     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod  = $_SERVER['REQUEST_METHOD'];

/* ───────────────────────────────── Artist ───────────────────────────────── */

/* 1) 작가 상세  GET /api/artists/{id} */
if ($requestMethod === 'GET' &&
    preg_match('#^/api/artists/(\d+)$#', $requestUri, $m))
{
    require_once __DIR__.'/controllers/ArtistController.php';
    (new ArtistController())->getArtistById($m[1]);
}

/* 2) 작가 목록  GET /api/artist */
elseif ($requestMethod === 'GET' &&
        preg_match('#^/api/artist$#', $requestUri))
{
    require_once __DIR__.'/controllers/ArtistController.php';
    (new ArtistController())->getArtistList();
}

/* ───────────────────────────────── Exhibition ──────────────────────────── */

/* 전시회 상세 */
elseif ($requestMethod==='GET' &&
        preg_match('#^/api/exhibitions/(\d+)$#', $requestUri, $m))
{
    require_once __DIR__.'/controllers/ExhibitionController.php';
    (new ExhibitionController())->getExhibitionById($m[1]);
}

/* 전시회 목록 */
elseif ($requestMethod==='GET' &&
        preg_match('#^/api/exhibitions$#', $requestUri))
{
    require_once __DIR__.'/controllers/ExhibitionController.php';
    (new ExhibitionController())->getExhibitionList();
}

/* 전시회 등록 */
elseif ($requestMethod==='POST' &&
        $requestUri==='/api/exhibitions')
{
    require_once __DIR__.'/controllers/ExhibitionController.php';
    (new ExhibitionController())->createExhibition();
}

/* 전시회 수정 */
elseif ($requestMethod==='PUT' &&
        preg_match('#^/api/exhibitions/(\d+)$#', $requestUri, $m))
{
    require_once __DIR__.'/controllers/ExhibitionController.php';
    (new ExhibitionController())->updateExhibition($m[1]);
}

/* 전시회 삭제 */
elseif ($requestMethod==='DELETE' &&
        preg_match('#^/api/exhibitions/(\d+)$#', $requestUri, $m))
{
    require_once __DIR__.'/controllers/ExhibitionController.php';
    (new ExhibitionController())->deleteExhibition($m[1]);
}

/* ───────────────────────────────── Art ─────────────────────────────────── */

/* 작품 상세 */
elseif ($requestMethod==='GET' &&
        preg_match('#^/api/arts/(\d+)$#', $requestUri, $m))
{
    require_once __DIR__.'/controllers/ArtController.php';
    (new ArtController())->getArtById($m[1]);
}

/* 작품 목록 */
elseif ($requestMethod==='GET' &&
        preg_match('#^/api/arts$#', $requestUri))
{
    require_once __DIR__.'/controllers/ArtController.php';
    (new ArtController())->getArtList();
}

/* 작품 등록 */
elseif ($requestMethod==='POST' &&
        $requestUri==='/api/arts')
{
    require_once __DIR__.'/controllers/ArtController.php';
    (new ArtController())->createArt();
}

/* 작품 수정 */
elseif ($requestMethod==='PUT' &&
        preg_match('#^/api/arts/(\d+)$#', $requestUri, $m))
{
    require_once __DIR__.'/controllers/ArtController.php';
    (new ArtController())->updateArt($m[1]);
}

/* 작품 삭제 */
elseif ($requestMethod==='DELETE' &&
        preg_match('#^/api/arts/(\d+)$#', $requestUri, $m))
{
    require_once __DIR__.'/controllers/ArtController.php';
    (new ArtController())->deleteArt($m[1]);
}

/* ───────────────────────────── 기본/404 ──────────────────────────────── */

/* 루트 */
elseif ($requestUri==='/' || $requestUri==='/index.php') {
    header('Content-Type: text/plain');  echo 'kau artly';
}

/* 404 */
else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error'=>'Not found'], JSON_UNESCAPED_UNICODE);
}

