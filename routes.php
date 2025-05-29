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
use Controllers\LikeController;
use Controllers\SessionController;
use Controllers\BookController;
use Controllers\ChatController;

$requestUri = str_replace('/artly-backend', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));


$requestMethod  = $_SERVER['REQUEST_METHOD'];
echo "<pre>Trying to call Controllers\\SessionController</pre>";
var_dump(class_exists('Controllers\\SessionController')); // true면 OK

#echo "<pre>requestUri: $requestUri\n";
#echo "requestMethod: $requestMethod</pre>";
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

/* ───────────────────────── Like ───────────────────────── */

elseif ($requestMethod === 'POST' && $requestUri === '/api/likes') {
    (new LikeController())->createLike();
}
elseif ($requestMethod === 'DELETE' && $requestUri === '/api/likes') {
    (new LikeController())->deleteLike();
}

/* ───────────────────────── Book ───────────────────────── */

elseif ($requestMethod === 'GET' && preg_match('#^/api/books/(\d+)$#', $requestUri, $m)) {
    (new BookController())->getBookById($m[1]);
}
elseif ($requestMethod === 'POST' && $requestUri === '/api/books') {
    (new BookController())->createBook();
}
elseif ($requestMethod === 'PUT' && preg_match('#^/api/books/(\d+)$#', $requestUri, $m)) {
    (new BookController())->updateBook($m[1]);
}
elseif ($requestMethod === 'DELETE' && preg_match('#^/api/books/(\d+)$#', $requestUri, $m)) {
    (new BookController())->deleteBook($m[1]);
}

/* ───────────────────────── Chat ───────────────────────── */

elseif ($requestMethod === 'POST' && $requestUri === '/api/chats') {
    (new ChatController())->postChat();
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

