<?php
require_once __DIR__ . '/vendor/autoload.php';


use Controllers\ArtistController;
use Controllers\ExhibitionController;
use Controllers\ArtController;
use Controllers\GalleryController;
use Controllers\AnnouncementController;

$requestUri     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod  = $_SERVER['REQUEST_METHOD'];

/* ───────────────────────── Artist ───────────────────────── */

if ($requestMethod === 'GET' && preg_match('#^/api/artists/(\d+)$#', $requestUri, $m)) {
    (new ArtistController())->getArtistById($m[1]);
}
elseif ($requestMethod === 'GET' && preg_match('#^/api/artist$#', $requestUri)) {
    (new ArtistController())->getArtistList();
}

/* ───────────────────────── Exhibition ───────────────────────── */

elseif ($requestMethod === 'GET' && preg_match('#^/api/exhibitions/(\d+)$#', $requestUri, $m)) {
    (new ExhibitionController())->getExhibitionById($m[1]);
}
elseif ($requestMethod === 'GET' && preg_match('#^/api/exhibitions$#', $requestUri)) {
    (new ExhibitionController())->getExhibitionList();
}
elseif ($requestMethod === 'POST' && $requestUri === '/api/exhibitions') {
    (new ExhibitionController())->createExhibition();
}
elseif ($requestMethod === 'PUT' && preg_match('#^/api/exhibitions/(\d+)$#', $requestUri, $m)) {
    (new ExhibitionController())->updateExhibition($m[1]);
}
elseif ($requestMethod === 'DELETE' && preg_match('#^/api/exhibitions/(\d+)$#', $requestUri, $m)) {
    (new ExhibitionController())->deleteExhibition($m[1]);
}

/* ───────────────────────── Art ───────────────────────── */

elseif ($requestMethod === 'GET' && preg_match('#^/api/arts/(\d+)$#', $requestUri, $m)) {
    (new ArtController())->getArtById($m[1]);
}
elseif ($requestMethod === 'GET' && preg_match('#^/api/arts$#', $requestUri)) {
    (new ArtController())->getArtList();
}
elseif ($requestMethod === 'POST' && $requestUri === '/api/arts') {
    (new ArtController())->createArt();
}
elseif ($requestMethod === 'PUT' && preg_match('#^/api/arts/(\d+)$#', $requestUri, $m)) {
    (new ArtController())->updateArt($m[1]);
}
elseif ($requestMethod === 'DELETE' && preg_match('#^/api/arts/(\d+)$#', $requestUri, $m)) {
    (new ArtController())->deleteArt($m[1]);
}

/* ───────────────────────── Gallery ───────────────────────── */

elseif ($requestMethod === 'GET' && preg_match('#^/api/galleries/(\d+)$#', $requestUri, $m)) {
    (new GalleryController())->getGalleryById($m[1]);
}
elseif ($requestMethod === 'GET' && preg_match('#^/api/galleries$#', $requestUri)) {
    (new GalleryController())->getGalleryList();
}

/* ───────────────────────── Announcement ───────────────────────── */

elseif ($requestMethod === 'GET' && preg_match('#^/api/announcements/(\d+)$#', $requestUri, $m)) {
    (new AnnouncementController())->getAnnouncementById($m[1]);
}
elseif ($requestMethod === 'GET' && preg_match('#^/api/announcements$#', $requestUri)) {
    (new AnnouncementController())->getAnnouncementList();
}

/* ───────────────────────── 기본/404 ───────────────────────── */

elseif ($requestUri === '/' || $requestUri === '/index.php') {
    header('Content-Type: text/plain');
    echo 'kau artly';
}
else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not found'], JSON_UNESCAPED_UNICODE);
}

