<?php
/**
 * ASEC Kulübü - Güvenlik Ayarları
 * Bu dosya, güvenlik başlıklarını ve ayarlarını içerir.
 */

// Güvenlik başlıklarını ayarla
function setSecurityHeaders() {
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

// CSRF token oluşturma ve doğrulama fonksiyonları
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Güvenlik başlıklarını uygula
setSecurityHeaders();
?>
