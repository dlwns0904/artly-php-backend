<?php
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 루트("/") 요청: 인사말 출력
if ($requestUri === '/' || $requestUri === '/index.php') {
    header('Content-Type: text/plain');
    echo "🎨 Artly API Server is running!";
}

// 작가 리스트 API
elseif (strpos($requestUri, '/api/artist') === 0) {
    require_once 'controllers/ArtistController.php';
    $controller = new ArtistController();
    $controller->getArtistList();
}

// 그 외 모든 요청: 404 처리
else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not found']);
}
