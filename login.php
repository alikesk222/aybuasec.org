<?php
require_once 'db.php';
require_once 'includes/validation.php';

// CSRF token oluştur - security.php içinde session_start() zaten çağrılıyor
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token doğrulama
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        // E-posta doğrulama
        if (!validateEmail($email)) {
            $error = 'Geçersiz e-posta adresi!';
        } else {
            // CAPTCHA doğrulama
            $captcha_response = $_POST['g-recaptcha-response'] ?? '';
            if (!validateCaptcha($captcha_response)) {
                $error = 'Robot olmadığınızı doğrulayın!';
            } else {
                // Giriş denemesi kontrolü
                $login_check = checkLoginAttempts($pdo, $email);
                if ($login_check['locked']) {
                    $error = $login_check['message'];
                } else {
                    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();
                    
                    if ($user && password_verify($password, $user['password'])) {
                        // Başarılı giriş, denemeleri sıfırla
                        resetLoginAttempts($pdo, $email);
                        $_SESSION['user'] = $user['email'];
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        header('Location: index.php');
                        exit;
                    } else {
                        $error = 'E-posta veya şifre hatalı!';
                    }
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
    <title>Giriş Yap - ASEC Kulübü</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="auth-page">
        <div class="auth-container">
            <h2>Giriş Yap</h2>
            <form method="post">
                <?php if (!empty($error)) { echo '<div class="alert-error">'.$error.'</div>'; } ?>
                <div class="form-group">
                    <label for="email">E-posta:</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Şifre:</label>
                    <input type="password" id="password" name="password" required>
                    <div class="password-info">
                        <small>Şifrenizi mi unuttunuz? <a href="sifremi-unuttum.php">Şifremi Sıfırla</a></small>
                    </div>
                </div>
                <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="6LeLMC8rAAAAAChTj8rlQ_zyjedV3VdnejoNAZy1"></div>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <button type="submit" class="cta-button">Giriş Yap</button>
            </form>
            <p>Hesabınız yok mu? <a href="register.php">Üye Ol</a></p>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/matrix-animation.js"></script>
</body>
</html>
