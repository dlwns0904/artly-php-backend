<?php
$host = '127.0.0.1'; // 또는 EC2 도메인/IP로 변경 가능
$db   = 'soundgramR2_back_250513';  // 실제 DB 이름
$user = 'root';                     // DB 계정
$pass = '1howtobiz';                 // DB 비밀번호

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ DB 연결 성공!<br>";
} catch (PDOException $e) {
    die("❌ DB 연결 실패: " . $e->getMessage());
}
?>