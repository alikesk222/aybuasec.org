<?php
require_once 'db.php';
require_once 'includes/validation.php';

// CSRF token oluştur - security.php içinde session_start() zaten çağrılıyor
$csrf_token = generateCSRFToken();

// Token kontrolü
$token = $_GET['token'] ?? '';
$valid_token = false;
$email = '';

if (!empty($token)) {
    // Token geçerli mi kontrol et
    $stmt = $pdo->prepare('SELECT email, expires FROM password_resets WHERE token = ?');
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if ($reset && $reset['expires'] > time()) {
        $valid_token = true;
        $email = $reset['email'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token doğrulama
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        
        // Token geçerli mi kontrol et
        $stmt = $pdo->prepare('SELECT email, expires FROM password_resets WHERE token = ?');
        $stmt->execute([$token]);
        $reset = $stmt->fetch();
        
        if (!$reset || $reset['expires'] <= time()) {
            $error = 'Geçersiz veya süresi dolmuş şifre sıfırlama bağlantısı!';
        } else {
            // Şifre güçlülük kontrolü
            $password_check = validatePassword($password);
            if (!$password_check['valid']) {
                $error = $password_check['message'];
            } 
            // Şifre eşleşme kontrolü
            else if ($password !== $password2) {
                $error = 'Şifreler eşleşmiyor!';
            } else {
                // Şifreyi hashle ve güncelle
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
                $ok = $stmt->execute([$hashed, $reset['email']]);
                
                if ($ok) {
                    // Kullanılan token'ı sil
                    $stmt = $pdo->prepare('DELETE FROM password_resets WHERE token = ?');
                    $stmt->execute([$token]);
                    
                    $success = true;
                } else {
                    $error = 'Şifre güncelleme sırasında bir hata oluştu!';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title>Şifre Sıfırla - ASEC Kulübü</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="auth-page">
        <div class="auth-container">
            <h2>Şifre Sıfırla</h2>
            
            <?php if (!empty($success)): ?>
                <div class="alert-success">
                    <p>Şifreniz başarıyla güncellendi!</p>
                    <p>Artık yeni şifrenizle <a href="login.php">giriş yapabilirsiniz</a>.</p>
                </div>
            <?php elseif (!$valid_token && empty($_POST)): ?>
                <div class="alert-error">
                    <p>Geçersiz veya süresi dolmuş şifre sıfırlama bağlantısı!</p>
                    <p>Lütfen <a href="sifremi-unuttum.php">şifremi unuttum</a> sayfasından yeni bir sıfırlama bağlantısı talep edin.</p>
                </div>
            <?php else: ?>
                <p class="auth-intro">Lütfen yeni şifrenizi belirleyin.</p>
                <form method="post">
                    <?php if (!empty($error)) { echo '<div class="alert-error">'.$error.'</div>'; } ?>
                    <div class="form-group">
                        <label for="password">Yeni Şifre:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="password2">Yeni Şifre (Tekrar):</label>
                        <input type="password" id="password2" name="password2" required>
                    </div>
                    <div class="form-group">
                        <div class="password-requirements">
                            <small>Şifreniz en az:</small>
                            <ul>
                                <li id="length-check">8 karakter uzunluğunda</li>
                                <li id="upper-check">Bir büyük harf</li>
                                <li id="lower-check">Bir küçük harf</li>
                                <li id="number-check">Bir rakam</li>
                                <li id="special-check">Bir özel karakter içermelidir</li>
                            </ul>
                        </div>
                    </div>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="cta-button">Şifremi Güncelle</button>
                </form>
            <?php endif; ?>
            
            <p>Şifrenizi hatırladınız mı? <a href="login.php">Giriş Yap</a></p>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/matrix-animation.js"></script>
</body>
</html>
