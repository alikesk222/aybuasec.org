<?php
/**
 * ASEC Kulübü - Form Doğrulama Fonksiyonları
 * Bu dosya form doğrulama işlemleri için gerekli fonksiyonları içerir
 */

// E-posta adresinin geçerli olup olmadığını kontrol eder
function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // E-posta adresinin domain kısmını kontrol et
    $domain = substr(strrchr($email, "@"), 1);
    if (!checkdnsrr($domain, "MX")) {
        return false;
    }
    
    return true;
}

// Telefon numarasını doğrular
function validatePhone($phone) {
    // Sadece rakamları al
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Türkiye telefon numarası formatı kontrolü (10 veya 11 rakam)
    if (strlen($phone) < 10 || strlen($phone) > 11) {
        return false;
    }
    
    // 05 ile başlamalı (11 haneli ise)
    if (strlen($phone) == 11 && substr($phone, 0, 2) != '05') {
        return false;
    }
    
    // 5 ile başlamalı (10 haneli ise)
    if (strlen($phone) == 10 && substr($phone, 0, 1) != '5') {
        return false;
    }
    
    return true;
}

// Şifre güçlülüğünü kontrol eder
function validatePassword($password) {
    // En az 8 karakter
    if (strlen($password) < 8) {
        return [
            'valid' => false,
            'message' => 'Şifre en az 8 karakter uzunluğunda olmalıdır.'
        ];
    }
    
    // En az bir büyük harf
    if (!preg_match('/[A-Z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Şifre en az bir büyük harf içermelidir.'
        ];
    }
    
    // En az bir küçük harf
    if (!preg_match('/[a-z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Şifre en az bir küçük harf içermelidir.'
        ];
    }
    
    // En az bir rakam
    if (!preg_match('/[0-9]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Şifre en az bir rakam içermelidir.'
        ];
    }
    
    // En az bir özel karakter
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Şifre en az bir özel karakter içermelidir.'
        ];
    }
    
    return [
        'valid' => true,
        'message' => 'Şifre geçerli.'
    ];
}

// Giriş denemelerini kontrol eder ve sınırlar
function checkLoginAttempts($pdo, $email, $max_attempts = 5, $lockout_time = 1800) {
    // Giriş denemelerini kontrol et
    $stmt = $pdo->prepare('SELECT attempts, last_attempt FROM login_attempts WHERE email = ?');
    $stmt->execute([$email]);
    $attempt = $stmt->fetch();
    
    $current_time = time();
    
    if ($attempt) {
        // Kilitlenme süresi geçti mi kontrol et
        if ($attempt['attempts'] >= $max_attempts && ($current_time - $attempt['last_attempt']) < $lockout_time) {
            // Hesap hala kilitli
            $remaining = $lockout_time - ($current_time - $attempt['last_attempt']);
            $minutes = ceil($remaining / 60);
            return [
                'locked' => true,
                'message' => "Çok fazla başarısız giriş denemesi. Lütfen $minutes dakika sonra tekrar deneyin."
            ];
        }
        
        // Kilitlenme süresi geçmişse veya henüz kilitlenmemişse
        if (($current_time - $attempt['last_attempt']) > $lockout_time) {
            // Süre geçmişse sıfırla
            $stmt = $pdo->prepare('UPDATE login_attempts SET attempts = 1, last_attempt = ? WHERE email = ?');
            $stmt->execute([$current_time, $email]);
        } else {
            // Deneme sayısını artır
            $stmt = $pdo->prepare('UPDATE login_attempts SET attempts = attempts + 1, last_attempt = ? WHERE email = ?');
            $stmt->execute([$current_time, $email]);
        }
    } else {
        // İlk deneme ise kayıt oluştur
        $stmt = $pdo->prepare('INSERT INTO login_attempts (email, attempts, last_attempt) VALUES (?, 1, ?)');
        $stmt->execute([$email, $current_time]);
    }
    
    return ['locked' => false];
}

// Başarılı girişten sonra giriş denemelerini sıfırlar
function resetLoginAttempts($pdo, $email) {
    $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE email = ?');
    $stmt->execute([$email]);
}

// CSRF token oluşturur
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF token doğrular
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Captcha doğrulama
function validateCaptcha($captcha_response) {
    if (empty($captcha_response)) {
        return false;
    }
    
    // Google reCAPTCHA için
    // Anahtarı güvenli bir şekilde sakla (env dosyasından veya veritabanından al)
    $secret_key = getSecretKeyFromConfig();
    
    // Anahtar yoksa doğrulama başarısız
    if (empty($secret_key)) {
        error_log('reCAPTCHA anahtarı bulunamadı');
        return false;
    }
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $secret_key,
        'response' => $captcha_response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    // cURL kullan (daha güvenli)
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log('reCAPTCHA doğrulama hatası: ' . $error);
        return false;
    }
    
    $result_json = json_decode($result, true);
    return isset($result_json['success']) && $result_json['success'] === true;
}

// Güvenli bir şekilde reCAPTCHA anahtarını al
function getSecretKeyFromConfig() {
    // Anahtarı .env dosyasından veya güvenli bir kaynaktan al
    // Örnek: .env dosyası kullanımı
    $env_file = __DIR__ . '/../.env';
    if (file_exists($env_file)) {
        $env_content = file_get_contents($env_file);
        preg_match('/RECAPTCHA_SECRET_KEY=([^\n]+)/', $env_content, $matches);
        if (isset($matches[1])) {
            return trim($matches[1]);
        }
    }
    
    // Veritabanından alma örneği (alternatif yöntem)
    // global $conn;
    // $stmt = $conn->prepare("SELECT value FROM config WHERE name = 'recaptcha_secret_key'");
    // $stmt->execute();
    // $result = $stmt->get_result();
    // if ($row = $result->fetch_assoc()) {
    //     return $row['value'];
    // }
    
    return null; // Anahtar bulunamadı
}
?>
