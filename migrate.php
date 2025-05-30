<?php
$config = require 'config/config.php';

try {
    $pdo = new PDO($config['dsn'], $config['user'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ 데이터베이스 연결 실패: " . $e->getMessage());
}

$tables = [
    // APIServer_user
    "CREATE TABLE IF NOT EXISTS APIServer_user (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // APIServer_announcement
    "CREATE TABLE IF NOT EXISTS APIServer_announcement (
        id INT AUTO_INCREMENT PRIMARY KEY,
        announcement_title VARCHAR(255),
        user_id INT,
        announcement_poster VARCHAR(255),
        announcement_start_datetime DATETIME,
        announcement_end_datetime DATETIME,
        announcement_organizer VARCHAR(255),
        announcement_contact VARCHAR(100),
        announcement_support_detail TEXT,
        announcement_site_url VARCHAR(255),
        announcement_attachment_url VARCHAR(255),
        content TEXT,
        announcement_create_dtm DATETIME DEFAULT CURRENT_TIMESTAMP,
        update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // APIServer_reservation
    "CREATE TABLE IF NOT EXISTS APIServer_reservation (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        session_id INT,
        reservation_datetime DATETIME,
        reservation_number_of_tickets INT,
        reservation_total_price INT,
        reservation_payment_method VARCHAR(50),
        reservation_status ENUM('reserved', 'canceled', 'used'),
        create_dtm DATETIME DEFAULT CURRENT_TIMESTAMP,
        update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // APIServer_session
    "CREATE TABLE IF NOT EXISTS APIServer_session (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exhibition_id INT,
        session_datetime DATETIME,
        session_total_capacity INT,
        session_reservation_capacity INT,
        create_dtm DATETIME DEFAULT CURRENT_TIMESTAMP,
        update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // APIServer_gallery
    "CREATE TABLE IF NOT EXISTS APIServer_gallery (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gallery_name VARCHAR(255),
        gallery_image VARCHAR(255),
        gallery_address VARCHAR(255),
        gallery_start_time DATETIME,
        gallery_end_time DATETIME,
        gallery_closed_day VARCHAR(100),
        gallery_category VARCHAR(100),
        gallery_description TEXT,
        create_dtm DATETIME DEFAULT CURRENT_TIMESTAMP,
        update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // APIServer_exhibition
    "CREATE TABLE IF NOT EXISTS APIServer_exhibition (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exhibition_title VARCHAR(255),
        exhibition_poster VARCHAR(255),
        exhibition_category VARCHAR(100),
        exhibition_start_date DATE,
        exhibition_end_date DATE,
        exhibition_start_time TIME,
        exhibition_end_time TIME,
        exhibition_location VARCHAR(255),
        exhibition_price INT,
        gallery_id INT,
        exhibition_tag VARCHAR(255),
        exhibition_status ENUM('scheduled', 'exhibited', 'ended'),
        create_dtm DATETIME DEFAULT CURRENT_TIMESTAMP,
        update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // APIServer_exhibition_participation
    "CREATE TABLE IF NOT EXISTS APIServer_exhibition_participation (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exhibition_id INT,
        artist_id INT,
        role VARCHAR(100),
        create_dtm DATETIME DEFAULT CURRENT_TIMESTAMP,
        update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // APIServer_artist
    "CREATE TABLE IF NOT EXISTS APIServer_artist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        artist_image VARCHAR(255),
        artist_name VARCHAR(255),
        artist_category VARCHAR(100),
        artist_nation VARCHAR(100),
        artist_description TEXT,
        create_dtm DATETIME DEFAULT CURRENT_TIMESTAMP,
        update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // APIServer_exhibition_art
    "CREATE TABLE IF NOT EXISTS APIServer_exhibition_art (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exhibition_id INT,
        art_id INT,
        display_order INT,
        create_dtm DATETIME DEFAULT CURRENT_TIMESTAMP,
        update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // APIServer_art
    "CREATE TABLE IF NOT EXISTS APIServer_art (
        id INT AUTO_INCREMENT PRIMARY KEY,
        art_image VARCHAR(255),
        art_title VARCHAR(255),
        artist_id INT,
        art_description TEXT,
        art_docent TEXT,
        create_dtm DATETIME DEFAULT CURRENT_TIMESTAMP,
        update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // APIServer_user_book
    "CREATE TABLE IF NOT EXISTS APIServer_user_book (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        book_id INT,
        user_book_payment_method VARCHAR(50),
        user_book_status ENUM('unpaid', 'canceled'),
        create_dtm DATETIME DEFAULT CURRENT_TIMESTAMP,
        update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // APIServer_book
    "CREATE TABLE IF NOT EXISTS APIServer_book (
        id INT AUTO_INCREMENT PRIMARY KEY,
        book_title VARCHAR(255),
        book_poster VARCHAR(255),
        exhibition_id INT,
        create_dtm DATETIME DEFAULT CURRENT_TIMESTAMP,
        update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // APIServer_book_page
    "CREATE TABLE IF NOT EXISTS APIServer_book_page (
        id INT AUTO_INCREMENT PRIMARY KEY,
        book_id INT,
        art_id INT,
        artist_id INT,
        book_page_sequence INT,
        book_page_description TEXT,
        create_dtm DATETIME DEFAULT CURRENT_TIMESTAMP,
        update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
// APIServer_conversation
    "CREATE TABLE IF NOT EXISTS APIServer_conversation (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        role ENUM('user', 'assistant') NOT NULL,
        content TEXT NOT NULL,
        create_dtm DATETIME DEFAULT CURRENT_TIMESTAMP,
        update_dtm DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];


try {
    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }
    echo "✅ 모든 테이블 생성 완료!";
} catch (PDOException $e) {
    echo "❌ 테이블 생성 실패: " . $e->getMessage();
}

