<?php
namespace Middlewares;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthMiddleware {
    /**
     * 인증 및 관리자 권한 검증
     * @return object 디코딩된 JWT 페이로드
     */
    public function requireAdmin() {
        $decoded = $this->authenticate();
        if (!isset($decoded->role) || $decoded->role !== 'admin') {
            http_response_code(403);
            echo json_encode(['message' => '관리자 권한이 필요합니다.']);
            exit;
        }
        return $decoded;
    }
    public function authenticate() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['message' => 'Authorization token not found']);
            exit;
        }

        $jwt = $matches[1];
        $secret = 'jwt-secret-key'; # 임시로 작성

        try {
            $decoded = JWT::decode($jwt, new Key($secret, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid token']);
            exit;
        }
    }
}
