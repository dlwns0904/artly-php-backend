<?php
// 이 줄이 반드시 routes.php 안에 있어야 함!
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// URI 확인용 출력
echo "현재 URI: $requestUri\n";

if (
    $requestUri === '/api/artist' ||
    $requestUri === '/index.php/api/artist' ||
    $requestUri === '/artly-backend/api/artist' ||
    $requestUri === '/artly-backend/index.php/api/artist' ||
    str_contains($requestUri, '/api/artist')
) {
    require_once __DIR__ . '/controllers/ArtistController.php';
    $controller = new ArtistController();
    $controller->getArtistList();
}
elseif (
    $requestUri === '/' ||
    $requestUri === '/index.php' ||
    $requestUri === '/artly-backend/' ||
    $requestUri === '/artly-backend/index.php'
) {
    header('Content-Type: text/plain');
    echo "kau artly";
}

################### 전시회 API #####################

## 전시회 목록 api
elseif (
    ($requestUri === '/api/exhibitions' || str_contains($requestUri, '/api/exhibitions')) &&
    $requestMethod === 'GET' &&
    !preg_match('/\/api\/exhibitions\/\d+/', $requestUri)
) {
    require_once __DIR__ . '/controllers/ExhibitionController.php';
    $controller = new ExhibitionController();
    $controller->getExhibitionList();
}
## 전시회 상세 조회 (GET /api/exhibitions/:id)
elseif (preg_match('/\/api\/exhibitions\/(\d+)/', $requestUri, $matches) && $requestMethod === 'GET') {
    require_once __DIR__ . '/controllers/ExhibitionController.php';
    $controller = new ExhibitionController();
    $controller->getExhibitionById($matches[1]);
}
## 전시회 등록 (POST /api/exhibitions)
elseif ($requestUri === '/api/exhibitions' && $requestMethod === 'POST') {
    require_once __DIR__ . '/controllers/ExhibitionController.php';
    $controller = new ExhibitionController();
    $controller->createExhibition();
}
## 전시회 수정 (PUT /api/exhibitions/:id)
elseif (preg_match('/\/api\/exhibitions\/(\d+)/', $requestUri, $matches) && $requestMethod === 'PUT') {
    require_once __DIR__ . '/controllers/ExhibitionController.php';
    $controller = new ExhibitionController();
    $controller->updateExhibition($matches[1]);
}
## 전시회 삭제 (DELETE /api/exhibitions/:id)
elseif (preg_match('/\/api\/exhibitions\/(\d+)/', $requestUri, $matches) && $requestMethod === 'DELETE') {
    require_once __DIR__ . '/controllers/ExhibitionController.php';
    $controller = new ExhibitionController();
    $controller->deleteExhibition($matches[1]);
}

################### 작품 API #####################

## 작품 목록 api
elseif (
    ($requestUri === '/api/arts' || str_contains($requestUri, '/api/arts')) &&
    $requestMethod === 'GET' &&
    !preg_match('/\/api\/arts\/\d+/', $requestUri)
) {
    require_once __DIR__ . '/controllers/ArtController.php';
    $controller = new ArtController();
    $controller->getArtList();
}
## 작품 상세 조회 (GET /api/arts/:id)
elseif (preg_match('/\/api\/arts\/(\d+)/', $requestUri, $matches) && $requestMethod === 'GET') {
    require_once __DIR__ . '/controllers/ArtController.php';
    $controller = new ArtController();
    $controller->getArtById($matches[1]);
}
## 작품 등록 (POST /api/arts)
elseif ($requestUri === '/api/arts' && $requestMethod === 'POST') {
    require_once __DIR__ . '/controllers/ArtController.php';
    $controller = new ArtController();
    $controller->createArt();
}
## 작품 수정 (PUT /api/arts/:id)
elseif (preg_match('/\/api\/arts\/(\d+)/', $requestUri, $matches) && $requestMethod === 'PUT') {
    require_once __DIR__ . '/controllers/ArtController.php';
    $controller = new ArtController();
    $controller->updateArt($matches[1]);
}
## 작품 삭제 (DELETE /api/arts/:id)
elseif (preg_match('/\/api\/arts\/(\d+)/', $requestUri, $matches) && $requestMethod === 'DELETE') {
    require_once __DIR__ . '/controllers/ArtController.php';
    $controller = new ArtController();
    $controller->deleteArt($matches[1]);
}
else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not found']);
}
