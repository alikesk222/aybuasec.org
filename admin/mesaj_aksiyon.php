<?php
// mesaj_aksiyon.php: okundu, sil, yıldız işlemleri için

// Güvenlik tanımlaması
define('ASEC_SECURITY', true);
define('ASEC_LOADED', true);

// Oturum başlat
session_start();

// Oturum kontrolü
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// CSRF kontrolü
if(!isset($_POST['csrf_token']) || !\ASEC\Security\SecurityHelper::validateCSRFToken($_POST['csrf_token'])) {
    die("Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.");
}

// Veritabanı bağlantısı için global $conn değişkenini kullan
global $conn;

// PDO bağlantısı oluştur
try {
    // Veritabanı bilgilerini güvenli bir şekilde al
    $db_config = require_once __DIR__ . '/../includes/db_config.php';
    
    // PDO bağlantısı oluştur
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Hata mesajını güvenli bir şekilde göster
    error_log('Veritabanı bağlantı hatası: ' . $e->getMessage());
    die('Veritabanı bağlantısı kurulamadı. Lütfen daha sonra tekrar deneyin.');
}

// Kullanıcı girdilerini güvenli bir şekilde al ve doğrula
$id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : 0;
$action = isset($_POST['action']) ? filter_var($_POST['action'], FILTER_SANITIZE_STRING) : '';

// İzin verilen işlemler listesi
$allowed_actions = ['okundu', 'sil', 'yildiz'];

// Geçersiz istek kontrolü
if ($id <= 0 || !in_array($action, $allowed_actions)) {
    echo 'Geçersiz istek.';
    exit;
}

if ($action === 'okundu') {
    $pdo->prepare('UPDATE mesajlar SET okundu=1 WHERE id=?')->execute([$id]);
    echo 'ok';
} elseif ($action === 'sil') {
    $pdo->prepare('DELETE FROM mesajlar WHERE id=?')->execute([$id]);
    echo 'silindi';
} elseif ($action === 'yildiz') {
    $pdo->prepare('UPDATE mesajlar SET yildiz=1-yildiz WHERE id=?')->execute([$id]);
    echo 'yildiz';
} else {
    echo 'Bilinmeyen aksiyon';
}
