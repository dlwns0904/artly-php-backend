<?php
require_once __DIR__ . '/vendor/autoload.php';


use Controllers\ArtistController;
use Controllers\ExhibitionController;
use Controllers\ArtController;
use Controllers\GalleryController;
use Controllers\AnnouncementController;
use Controllers\UserController;
use Controllers\AuthController;
use Controllers\SearchController;
use Controllers\ReservationController;
use Controllers\SessionController;
use Controllers\LikeController;



$requestUri = $_SERVER['REDIRECT_URL'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod  = $_SERVER['REQUEST_METHOD'];



/* ───────────────────────── Artist ───────────────────────── */

if ($requestMethod === 'GET' && preg_match('#^/api/artists/(\d+)$#', $requestUri, $m)) {
    (new ArtistController())->getArtistById($m[1]);
}
elseif ($requestMethod === 'GET' && preg_match('#^/api/artist$#', $requestUri)) {
    (new ArtistController())->getArtistList();
}
elseif ($requestMethod === 'POST' && $requestUri === '/api/artists') {
    (new ArtistController())->createArtist();
}
elseif ($requestMethod === 'PUT' && preg_match('#^/api/artists/(\d+)$#', $requestUri, $m)) {
    (new ArtistController())->updateArtist($m[1]);
}
elseif ($requestMethod === 'DELETE' && preg_match('#^/api/artists/(\d+)$#', $requestUri, $m)) {
    (new ArtistController())->deleteArtist($m[1]);
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
elseif ($requestMethod === 'POST' && $requestUri === '/api/galleries') {
    (new GalleryController())->createGallery();
}
elseif ($requestMethod === 'PUT' && preg_match('#^/api/galleries/(\d+)$#', $requestUri, $m)) {
    (new GalleryController())->updateGallery($m[1]);
}
elseif ($requestMethod === 'DELETE' && preg_match('#^/api/galleries/(\d+)$#', $requestUri, $m)) {
    (new GalleryController())->deleteGallery($m[1]);
}


/* ───────────────────────── Announcement ───────────────────────── */

elseif ($requestMethod === 'GET' && preg_match('#^/api/announcements/(\d+)$#', $requestUri, $m)) {
    (new AnnouncementController())->getAnnouncementById($m[1]);
}
elseif ($requestMethod === 'GET' && preg_match('#^/api/announcements$#', $requestUri)) {
    (new AnnouncementController())->getAnnouncementList();
}
elseif ($requestMethod === 'POST' && $requestUri === '/api/announcements') {
    (new AnnouncementController())->createAnnouncement();
}
elseif ($requestMethod === 'PUT' && preg_match('#^/api/announcements/(\d+)$#', $requestUri, $m)) {
    (new AnnouncementController())->updateAnnouncement($m[1]);
}
elseif ($requestMethod === 'DELETE' && preg_match('#^/api/announcements/(\d+)$#', $requestUri, $m)) {
    (new AnnouncementController())->deleteAnnouncement($m[1]);
}

/* ───────────────────────── User ───────────────────────── */

elseif ($requestMethod === 'POST' && $requestUri === '/api/auth/register') {
    (new AuthController())->register();
}
elseif ($requestMethod === 'POST' && $requestUri === '/api/auth/login') {
    (new AuthController())->login();
}
elseif ($requestMethod === 'PUT' && $requestUri === '/api/users/me') {
    (new UserController())->updateMe();
}
elseif ($requestMethod === 'GET' && $requestUri === '/api/users/me') {
    (new UserController())->getMe();
}
elseif ($requestMethod === 'GET' && $requestUri === '/api/users/me/exhibitions') {
    (new UserController())->getMyReservations();
}
elseif ($requestMethod === 'GET' && $requestUri === '/api/users/me/purchases') {
    (new UserController())->getMyPurchases();
}
elseif ($requestMethod === 'GET' && $requestUri === '/api/users/me/likes') {
    (new UserController())->getMyLikes();
}
/* ───────────────────────── Search ───────────────────────── */

elseif ($requestMethod === 'GET' && $requestUri === '/api/search') {
    (new SearchController())->getResults();
}

/* ───────────────────────── Session ───────────────────────── */
elseif ($requestMethod === 'GET' && preg_match('#^/api/exhibitions/(\d+)/sessions$#', $requestUri, $m)) {
    (new \Controllers\SessionController())->getSessionsByDate($m[1]);
}

/* ───────────────────────── Reservation ───────────────────────── */

// 예약 생성 (POST /api/reservations)
elseif ($requestMethod === 'POST' && $requestUri === '/api/reservations') {
    (new ReservationController())->createReservation();
}

// 예약 취소 (DELETE /api/reservations/{id})
elseif ($requestMethod === 'DELETE' && preg_match('#^/api/reservations/(\d+)$#', $requestUri, $m)) {
    (new ReservationController())->cancelReservation($m[1]);
}

/* ───────────────────────── Like ───────────────────────── */

elseif ($requestMethod === 'POST' && $requestUri === '/api/likes') {
    (new LikeController())->createLike();
}
elseif ($requestMethod === 'DELETE' && $requestUri === '/api/likes') {
    (new LikeController())->deleteLike();
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

