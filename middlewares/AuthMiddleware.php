<?php
namespace Middlewares;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthMiddleware {

    private static $secret = 'jwt-secret-key';

   public static function decodeToken() {
    $headers = getallheaders();
    error_log("Headers: " . json_encode($headers));  // 추가
    $authHeader = $headers['Authorization'] ?? '';

    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        error_log("Authorization header not found");
        return null;
    }

    try {
        return JWT::decode($matches[1], new Key(self::$secret, 'HS256'));
    } catch (Exception $e) {
        error_log("Token decode error: " . $e->getMessage());
        return null;
    }
}


    public static function getUserId() {
    $decoded = self::decodeToken();
    if ($decoded && isset($decoded->user_id)) {
        return $decoded->user_id;
    }
    if ($decoded && isset($decoded->id)) {  // <-- 이거 추가
        return $decoded->id;
    }
    return null;
}
    public function authenticate() {
        $decoded = self::decodeToken();
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid or missing token']);
            exit;
        }
        return $decoded;
    }
}

