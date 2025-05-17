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
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $university = trim($_POST['university'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $class = trim($_POST['class'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        
        // E-posta doğrulama
        if (!validateEmail($email)) {
            $error = 'Geçersiz e-posta adresi!';
        } 
        // Telefon doğrulama
        else if (!validatePhone($phone)) {
            $error = 'Geçersiz telefon numarası! Lütfen geçerli bir Türkiye telefon numarası girin.';
        }
        // Şifre eşleşme kontrolü
        else if ($password !== $password2) {
            $error = 'Şifreler eşleşmiyor!';
        } 
        // Şifre güçlülük kontrolü
        else {
            $password_check = validatePassword($password);
            if (!$password_check['valid']) {
                $error = $password_check['message'];
            }
            // Tüm alanların doldurulduğunu kontrol et
            else if (!$name || !$phone || !$email || !$university || !$department || !$class || !$password) {
                $error = 'Lütfen tüm alanları doldurun!';
            } 
            // CAPTCHA doğrulama
            else {
                $captcha_response = $_POST['g-recaptcha-response'] ?? '';
                if (!validateCaptcha($captcha_response)) {
                    $error = 'Robot olmadığınızı doğrulayın!';
                } else {
                    // E-posta daha önce kayıtlı mı?
                    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = 'Bu e-posta adresi zaten kayıtlı!';
                    } else {
                        // Şifreyi hashle ve kaydet
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare('INSERT INTO users (name, phone, email, university, department, class, password) VALUES (?, ?, ?, ?, ?, ?, ?)');
                        $ok = $stmt->execute([$name, $phone, $email, $university, $department, $class, $hashed]);
                        if ($ok) {
                            $success = true;
                        } else {
                            $error = 'Kayıt sırasında bir hata oluştu!';
                        }
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
    <title>Üye Ol - ASEC Kulübü</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="auth-page">
        <div class="auth-container">
            <h2>Üye Ol</h2>
            <form method="post">
                <?php if (!empty($error)) { echo '<div class="alert-error">'.$error.'</div>'; } ?>
                <?php if (!empty($success)) { echo '<div class="alert-success">Kayıt başarılı! Giriş yapabilirsiniz.</div>'; } ?>
                <div class="form-group">
                    <label for="name">İsim Soyisim:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="phone">Telefon:</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="email">E-posta:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="university">Üniversite:</label>
                    <input type="text" id="university" name="university" required>
                </div>
                <div class="form-group">
                    <label for="department">Bölüm:</label>
                    <input type="text" id="department" name="department" required>
                </div>
                <div class="form-group">
                    <label for="class">Sınıf:</label>
                    <input type="text" id="class" name="class" required>
                </div>
                <div class="form-group">
                    <label for="password">Şifre:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password2">Şifre (Tekrar):</label>
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
                <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="6LeLMC8rAAAAAChTj8rlQ_zyjedV3VdnejoNAZy1"></div>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <button type="submit" class="cta-button">Üye Ol</button>
            </form>
            <p>Zaten üye misiniz? <a href="login.php">Giriş Yap</a></p>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/matrix-animation.js"></script>
</body>
</html>
