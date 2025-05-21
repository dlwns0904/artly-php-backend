<?php
namespace Models;

use \PDO;


class LikeModel {
    private $pdo;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $this->pdo = new PDO($config['dsn'], $config['user'], $config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function create($userId, $data) {
        $stmt = $this->pdo->prepare("INSERT INTO APIServer_like
            (user_id, liked_id, liked_type, create_dtm, update_dtm)
            VALUES (:user_id, :liked_id, :liked_type, NOW(), NOW())");

        return $stmt->execute([
            ':user_id' => $userId,
            ':liked_id' => $data['liked_id'],
            ':liked_type' => $data['liked_type']
        ]);
    }

    public function delete($userId, $data) {
        $stmt = $this->pdo->prepare(
            "DELETE FROM APIServer_like 
             WHERE user_id = :user_id and liked_id = :liked_id and liked_type = :liked_type");
             
        return $stmt->execute([
            ':user_id' => $userId,
            ':liked_id' => $data['liked_id'],
            ':liked_type' => $data['liked_type']
        ]);
    }

    public function targetExists($data) {
        $likedId = $data['liked_id'];
        $likedType = $data['liked_type'];

        $allowedTables = [
            'gallery' => 'APIServer_gallery',
            'exhibition' => 'APIServer_exhibition',
            'artist' => 'APIServer_artist'
        ];

        if (!array_key_exists($likedType, $allowedTables)) {
            return false;
        }

        $table = $allowedTables[$likedType];
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE id = :id");
             
        $stmt->execute([':id' => $likedId]);
        return $stmt->fetchColumn() > 0;
    }
}

