<?php
$host = '127.0.0.1';                        // 또는 'localhost'
$db   = 'soundgramR2_back_250513';         // 실제 DB 이름
$user = 'root';                             // DB 사용자명
$pass = '1howtobiz';                        // DB 비밀번호

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ DB 연결 성공!";
} catch (PDOException $e) {
    die("❌ DB 연결 실패: " . $e->getMessage());
}
?>

