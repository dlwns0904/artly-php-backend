<?php
namespace Controllers;

use Models\UserModel;
use Firebase\JWT\JWT;

class AuthController {
    private $model;
    private $jwtSecret = 'jwt-secret-key'; # 임시로 작성

    public function __construct() {
        $this->model = new UserModel();
    }
    
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = $this->model->getByLoginId($data['login_id']);

        # 로그인 실패
        if (!$user || $user['login_pwd'] !== $data['login_pwd']) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid credentials']);
            return;
        }

        $jwt = $this->createJwtToken($user, 60); # jwt 토큰 생성 / 유효시간 60분
        echo json_encode([
            'message' => 'Login successful',
            'jwt' => $jwt,
            'data' => $user
        ], JSON_UNESCAPED_UNICODE);
    }
    
    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        $newUser = $this->model->create($data);

        if ($newUser) {
            http_response_code(201);
            echo json_encode([
                'message' => 'User registered successfully',
                'data' => $newUser
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Registration failed']);
        }
    }

    public function createJwtToken($user, $time) {
        $payload = [
                    'iat' => time(),                 // 발급 시간
                    'exp' => time() + ($time * 60),  // 만료 시간 / time 파라미터의 단위는 minute
                    'user_id' => $user['id'],
                    'login_id' => $user['login_id'],
        ];

        $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');
        return $jwt;
    }
}
