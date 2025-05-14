<?php
// 이 줄이 반드시 routes.php 안에 있어야 함!
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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
else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not found']);
}
