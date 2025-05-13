<?php
require_once 'config/config.php';

$sql = "
CREATE TABLE IF NOT EXISTS APIServer_user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gallery_id INT,
    login_id VARCHAR(100) NOT NULL,
    login_pwd VARCHAR(255) NOT NULL,
    user_name VARCHAR(100),
    user_gender VARCHAR(10),
    user_age INT,
    user_email VARCHAR(100),
    user_phone VARCHAR(20),
    user_img VARCHAR(255),
    user_keyword TEXT,
    admin_flag TINYINT DEFAULT 0,
    last_login_time DATETIME,
    reg_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    $pdo->exec($sql);
    echo "✅ APIServer_user 테이블 생성 완료!";
} catch (PDOException $e) {
    echo "❌ 테이블 생성 실패: " . $e->getMessage();
}
?>