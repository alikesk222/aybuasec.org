<?php
/**
 * ASEC Kulübü - Görsel Optimizasyon Sistemi
 * Bu dosya, görsel optimizasyonu için gerekli fonksiyonları içerir.
 */

// Hata raporlamayı kapat
error_reporting(0);

// URL parametrelerini güvenli bir şekilde al
// Görsel yolunu doğrudan kullanıcı girdisinden almak yerine güvenli bir yöntem kullan
$image_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;
$optimize = isset($_GET['optimize']) ? filter_var($_GET['optimize'], FILTER_SANITIZE_STRING) : false;
$width = isset($_GET['width']) ? filter_var($_GET['width'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2000]]) : null;
$height = isset($_GET['height']) ? filter_var($_GET['height'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2000]]) : null;
$quality = isset($_GET['quality']) ? filter_var($_GET['quality'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]) : 80;

// Görsel ID'sinden güvenli bir şekilde dosya yolunu al
$image_path = getImagePathFromId($image_id);

// Optimize parametresi yoksa veya false ise, doğrudan orijinal görseli göster
if (!$optimize || $optimize !== 'true') {
    header("Location: $image_path");
    exit;
}

// Görsel ID'sinden güvenli bir şekilde dosya yolunu alan fonksiyon
function getImagePathFromId($image_id) {
    // Güvenlik kontrolü
    if ($image_id <= 0) {
        header("HTTP/1.0 400 Bad Request");
        exit("Geçersiz görsel ID'si");
    }
    
    // Veritabanı bağlantısı (güvenli bir şekilde yapılmalı)
    try {
        require_once __DIR__ . '/../db.php';
        global $conn;
        
        // Güvenli SQL sorgusu
        $stmt = $conn->prepare("SELECT image_path FROM images WHERE id = ?");
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $image_path = __DIR__ . '/../uploads/' . basename($row['image_path']);
            
            // Path traversal saldırılarına karşı koruma
            $real_uploads_dir = realpath(__DIR__ . '/../uploads/');
            $real_image_path = realpath($image_path);
            
            if ($real_image_path === false || strpos($real_image_path, $real_uploads_dir) !== 0) {
                header("HTTP/1.0 403 Forbidden");
                exit("Geçersiz görsel yolu");
            }
            
            return $real_image_path;
        } else {
            header("HTTP/1.0 404 Not Found");
            exit("Görsel bulunamadı");
        }
    } catch (Exception $e) {
        header("HTTP/1.0 500 Internal Server Error");
        exit("Sunucu hatası");
    }
}

// Görsel var mı kontrol et
if (!file_exists($image_path)) {
    header("HTTP/1.0 404 Not Found");
    exit("Görsel bulunamadı");
}

// Görsel türünü belirle
$image_info = getimagesize($image_path);
$mime_type = $image_info['mime'];

// Desteklenen görsel formatları
switch ($mime_type) {
    case 'image/jpeg':
        $source_image = imagecreatefromjpeg($image_path);
        break;
    case 'image/png':
        $source_image = imagecreatefrompng($image_path);
        break;
    case 'image/gif':
        $source_image = imagecreatefromgif($image_path);
        break;
    default:
        // Desteklenmeyen format, doğrudan orijinal görseli göster
        header("Content-Type: $mime_type");
        readfile($image_path);
        exit;
}

// Orijinal boyutları al
$original_width = imagesx($source_image);
$original_height = imagesy($source_image);

// Yeni boyutları hesapla
if ($width && !$height) {
    // Sadece genişlik belirtilmişse, oranı koru
    $new_width = $width;
    $new_height = ($original_height / $original_width) * $new_width;
} elseif (!$width && $height) {
    // Sadece yükseklik belirtilmişse, oranı koru
    $new_height = $height;
    $new_width = ($original_width / $original_height) * $new_height;
} elseif ($width && $height) {
    // Her ikisi de belirtilmişse, tam boyut
    $new_width = $width;
    $new_height = $height;
} else {
    // Hiçbiri belirtilmemişse, orijinal boyut
    $new_width = $original_width;
    $new_height = $original_height;
}

// Yeni görsel oluştur
$new_image = imagecreatetruecolor($new_width, $new_height);

// PNG için şeffaflık koruması
if ($mime_type === 'image/png') {
    imagealphablending($new_image, false);
    imagesavealpha($new_image, true);
    $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
    imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
}

// Görseli yeniden boyutlandır
imagecopyresampled(
    $new_image,
    $source_image,
    0, 0, 0, 0,
    $new_width, $new_height,
    $original_width, $original_height
);

// Görseli çıktıla
header("Content-Type: $mime_type");
switch ($mime_type) {
    case 'image/jpeg':
        imagejpeg($new_image, null, $quality);
        break;
    case 'image/png':
        // PNG için kalite 0-9 arası (9 en iyi sıkıştırma)
        $png_quality = ($quality - 100) / 11.111111;
        $png_quality = round(abs($png_quality));
        imagepng($new_image, null, $png_quality);
        break;
    case 'image/gif':
        imagegif($new_image);
        break;
}

// Belleği temizle
imagedestroy($source_image);
imagedestroy($new_image);
