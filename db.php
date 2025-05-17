<?php
// db.php – PDO ile veritabanı bağlantı dosyası

// Özel istisna sınıfı tanımla
class SecurityFileNotFoundException extends \Exception {
    public function __construct($message = "Güvenlik dosyası bulunamadı", $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Güvenlik sınıfını ekle - namespace kullanarak
use \ASEC\Security\SecurityHelper;

// Security.php dosyasını dahil et - modern yaklaşım
(function() {
    // Güvenlik sınıfını doğrudan burada tanımla
    namespace ASEC\Security;
    
    /**
     * SecurityHelper sınıfı
     * Tüm güvenlik fonksiyonlarını içeren yardımcı sınıf
     */
    class SecurityHelper
    {
        /**
         * Güvenlik başlıklarını ayarla
         */
        public static function setSecurityHeaders() {
            // Content Security Policy (CSP) - XSS ve veri enjeksiyon saldırılarına karşı koruma
            $csp = "default-src 'self'; " .
                   "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://www.google.com https://www.gstatic.com https://img1.wsimg.com; " .
                   "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; " .
                   "img-src 'self' data: https:; " .
                   "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; " .
                   "connect-src 'self'; " .
                   "frame-src 'self' https://www.google.com; " .
                   "object-src 'none';";
            
            header("Content-Security-Policy: $csp");
            
            // X-Frame-Options - Clickjacking saldırılarına karşı koruma
            header("X-Frame-Options: SAMEORIGIN");
            
            // X-Content-Type-Options - MIME sniffing saldırılarına karşı koruma
            header("X-Content-Type-Options: nosniff");
            
            // X-XSS-Protection - XSS saldırılarına karşı ek koruma (modern tarayıcılarda CSP ile değiştirildi)
            header("X-XSS-Protection: 1; mode=block");
            
            // Referrer-Policy - Referrer bilgisinin nasıl gönderileceğini kontrol eder
            header("Referrer-Policy: strict-origin-when-cross-origin");
            
            // Strict-Transport-Security (HSTS) - HTTPS kullanımını zorunlu kılar
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
            
            // Güvenli çerezler için ayarlar
            ini_set('session.cookie_httponly', 1); // HttpOnly flag
            ini_set('session.cookie_secure', 1);   // Secure flag
            ini_set('session.cookie_samesite', 'Lax'); // SameSite attribute
            
            // CORS ayarları - Etki alanları arası istekleri kısıtla
            header("Access-Control-Allow-Origin: https://aybuasec.org");
            header("Access-Control-Allow-Methods: GET, POST");
            header("Access-Control-Allow-Headers: Content-Type, Authorization");
        }

        /**
         * CSRF token oluştur
         * @return string
         */
        public static function generateCSRFToken() {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        }

        /**
         * CSRF token doğrula
         * @param string $token
         * @return bool
         */
        public static function validateCSRFToken($token) {
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }
    }
})();

// Güvenlik başlıklarını uygula
SecurityHelper::setSecurityHeaders();

$serverName = $_SERVER['SERVER_NAME'] ?? '';
$isLocal = ($serverName === 'localhost' || $serverName === '127.0.0.1');

// Ortama göre bağlantı bilgileri
if ($isLocal) {
    $host = 'localhost';
    $db   = 'db_asec';
    $user = 'root';
    $pass = '';
} else {
    $host = 'localhost'; // GoDaddy’de genelde "localhost" çalışır
    $db   = 'db_asec';
    $user = 'alikesk222';
    $pass = 'Aybu.asec*25##';
}

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('❌ Veritabanı bağlantı hatası: ' . $e->getMessage());
}
?>
