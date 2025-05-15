<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$email = $_SESSION['user'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}
// Profil güncelleme işlemi
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $university = trim($_POST['university'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $birthdate = $_POST['birthdate'] ?? null;
    $address = trim($_POST['address'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $achievements = trim($_POST['achievements'] ?? '');
    $avatarFile = $user['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/avatar/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
            $avatarFile = $filename;
        }
    }
    $stmt = $pdo->prepare('UPDATE users SET name=?, phone=?, university=?, department=?, class=?, birthdate=?, address=?, bio=?, instagram=?, linkedin=?, achievements=?, avatar=? WHERE id=?');
    $stmt->execute([$name, $phone, $university, $department, $class, $birthdate, $address, $bio, $instagram, $linkedin, $achievements, $avatarFile, $user['id']]);
    // Sayfa yenile (güncel bilgileri görmek için)
    header('Location: profilim.php');
    exit;
}
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title>Profilim - ASEC Kulübü</title>
    <link rel="stylesheet" href="css/profilim.css">
</head>
<body>
<?php include 'header.php'; ?>
<main class="profile-main">
    <div class="profil-container">
        <div class="profil-header">
            <div class="profil-avatar" id="profil-avatar">
                <?php if (!empty($user['avatar']) && file_exists('uploads/avatar/' . $user['avatar'])): ?>
                    <img src="uploads/avatar/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Profil Fotoğrafı" style="width:72px;height:72px;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="profil-header-info">
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <div class="profil-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></div>
                <button class="cta-button" id="profil-guncelle-btn" style="margin-top:0.3rem;">Profili Düzenle</button>
            </div>
        </div>
        <!-- Profil Düzenle Modalı -->
        <div id="profil-modal" class="profil-modal" style="display:none;">
            <div class="profil-modal-content">
                <span class="profil-modal-close" id="profil-modal-close">&times;</span>
                <h3>Profili Düzenle</h3>
                <form id="profil-update-form" method="post" enctype="multipart/form-data">
                    <div class="profil-form-row">
                        <div class="profil-form-group">
                            <label>İsim Soyisim:</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                        </div>
                        <div class="profil-form-group">
                            <label>Telefon:</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                    </div>
                    <div class="profil-form-row">
                        <div class="profil-form-group">
                            <label>Üniversite:</label>
                            <input type="text" name="university" value="<?php echo htmlspecialchars($user['university']); ?>">
                        </div>
                        <div class="profil-form-group">
                            <label>Bölüm:</label>
                            <input type="text" name="department" value="<?php echo htmlspecialchars($user['department']); ?>">
                        </div>
                    </div>
                    <div class="profil-form-row">
                        <div class="profil-form-group">
                            <label>Sınıf:</label>
                            <input type="text" name="class" value="<?php echo htmlspecialchars($user['class']); ?>">
                        </div>
                        <div class="profil-form-group">
                            <label>Doğum Tarihi:</label>
                            <input type="date" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="profil-form-row">
                        <div class="profil-form-group">
                            <label>Adres:</label>
                            <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                        </div>
                        <div class="profil-form-group">
                            <label>Kısa Biyografi:</label>
                            <textarea name="bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="profil-form-row">
                        <div class="profil-form-group">
                            <label>Sosyal Medya (Instagram):</label>
                            <input type="text" name="instagram" value="<?php echo htmlspecialchars($user['instagram'] ?? ''); ?>">
                        </div>
                        <div class="profil-form-group">
                            <label>Sosyal Medya (LinkedIn):</label>
                            <input type="text" name="linkedin" value="<?php echo htmlspecialchars($user['linkedin'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="profil-form-row">
                        <div class="profil-form-group">
                            <label>Başarılar:</label>
                            <input type="text" name="achievements" value="<?php echo htmlspecialchars($user['achievements'] ?? ''); ?>">
                        </div>
                        <div class="profil-form-group">
                            <label>Profil Fotoğrafı:</label>
                            <input type="file" name="avatar" accept="image/*">
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="cta-button">Kaydet</button>
                </form>
            </div>
        </div>
        <div class="profil-info-list">
            <div class="profil-info-item"><span class="profil-label">Telefon</span><span class="profil-value"><?php echo htmlspecialchars($user['phone']); ?></span></div>
            <div class="profil-info-item"><span class="profil-label">Üniversite</span><span class="profil-value"><?php echo htmlspecialchars($user['university']); ?></span></div>
            <div class="profil-info-item"><span class="profil-label">Bölüm</span><span class="profil-value"><?php echo htmlspecialchars($user['department']); ?></span></div>
            <div class="profil-info-item"><span class="profil-label">Sınıf</span><span class="profil-value"><?php echo htmlspecialchars($user['class']); ?></span></div>
            <div class="profil-info-item"><span class="profil-label">Doğum Tarihi</span><span class="profil-value"><?php echo htmlspecialchars($user['birthdate'] ?? '(Eklenmedi)'); ?></span></div>
            <div class="profil-info-item"><span class="profil-label">Adres</span><span class="profil-value"><?php echo isset($user['address']) && trim($user['address']) !== '' ? htmlspecialchars($user['address']) : '(Eklenmedi)'; ?></span></div>
            <div class="profil-info-item"><span class="profil-label">Kısa Biyografi</span><span class="profil-value"><?php echo htmlspecialchars($user['bio'] ?? '(Eklenmedi)'); ?></span></div>
            <div class="profil-info-item"><span class="profil-label">Sosyal Medya (Instagram)</span><span class="profil-value"><?php echo htmlspecialchars($user['instagram'] ?? '(Eklenmedi)'); ?></span></div>
            <div class="profil-info-item"><span class="profil-label">Sosyal Medya (LinkedIn)</span><span class="profil-value"><?php echo htmlspecialchars($user['linkedin'] ?? '(Eklenmedi)'); ?></span></div>
            <div class="profil-info-item"><span class="profil-label">Başarılar</span><span class="profil-value"><?php echo htmlspecialchars($user['achievements'] ?? '(Eklenmedi)'); ?></span></div>
        </div>
        <div class="profil-cv-section">
            <label for="cv"><i class="fas fa-file-pdf"></i> CV Yükle (PDF):</label>
            <form method="post" enctype="multipart/form-data" id="cv-upload-form">
                <input type="file" name="cv" id="cv" accept="application/pdf">
                <button type="submit" class="cta-button">CV Yükle</button>
            </form>
            <?php
        // CV yükleme işlemi
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/cv/';
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
            $ext = pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
            $filename = 'cv_' . $user['id'] . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['cv']['tmp_name'], $targetPath)) {
                // Veritabanına kaydet
                $stmt = $pdo->prepare('UPDATE users SET cv = ? WHERE id = ?');
                $stmt->execute([$filename, $user['id']]);
                echo '<div class="alert-success">CV başarıyla yüklendi.</div>';
                // Sayfa yenile (tekrar yüklemeyi önlemek için)
                header("Refresh:0");
            } else {
                echo '<div class="alert-error">CV yüklenemedi!</div>';
            }
        }
        // Veritabanından CV dosya adını al
        $cvFile = $user['cv'];
        if ($cvFile && file_exists(__DIR__ . '/uploads/cv/' . $cvFile)) {
            echo '<div class="form-group"><b>Yüklenen CV:</b> <a href="uploads/cv/' . htmlspecialchars($cvFile) . '" target="_blank">CV Dosyasını Görüntüle</a></div>';
        }
        // Geriye dönük uyumluluk için eski yolu da kontrol et
        else {
            $cvPath = __DIR__ . '/uploads/cv/cv_' . $user['id'] . '.pdf';
            if (file_exists($cvPath)) {
                echo '<div class="form-group"><b>Yüklenen CV:</b> <a href="uploads/cv/cv_' . $user['id'] . '.pdf" target="_blank">CV Dosyasını Görüntüle</a></div>';
            }
        }
        ?>
    </div>
</main>
<?php include 'footer.php'; ?>
<script src="js/profilim.js"></script>
</body>
</html>
