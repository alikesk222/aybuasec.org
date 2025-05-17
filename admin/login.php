<?php
require_once 'includes/config.php';

// CSRF token için SecurityHelper sınıfını kullan
$csrf_token = \ASEC\Security\SecurityHelper::generateCSRFToken();

// GEÇİCİ KOD BLOĞU - Admin oluşturma
$create_table_sql = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)";
mysqli_query($conn, $create_table_sql);

$username = "admin";
$password = "admin123";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Mevcut admin kullanıcısını kontrol et
$check_sql = "SELECT id FROM admin_users WHERE username = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "s", $username);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

// Eğer admin kullanıcısı yoksa oluştur
if(mysqli_stmt_num_rows($check_stmt) == 0) {
    $insert_sql = "INSERT INTO admin_users (username, password) VALUES (?, ?)";
    if($stmt = mysqli_prepare($conn, $insert_sql)){
        mysqli_stmt_bind_param($stmt, "ss", $username, $hashed_password);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
mysqli_stmt_close($check_stmt);
// GEÇİCİ KOD BLOĞU SONU

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

$username = $password = "";
$username_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // CSRF kontrolü - SecurityHelper sınıfını kullan
    if(!isset($_POST['csrf_token']) || !\ASEC\Security\SecurityHelper::validateCSRFToken($_POST['csrf_token'])) {
        die("Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.");
    }
    
    // Brute force koruması - 5 başarısız giriş denemesinden sonra 15 dakika bekleme süresi
    $max_attempts = 5;
    $lockout_time = 15 * 60; // 15 dakika (saniye cinsinden)
    
    // Başarısız giriş denemelerini kontrol et
    if (isset($_SESSION['login_attempts']) && isset($_SESSION['last_attempt_time'])) {
        if ($_SESSION['login_attempts'] >= $max_attempts) {
            $time_elapsed = time() - $_SESSION['last_attempt_time'];
            if ($time_elapsed < $lockout_time) {
                $remaining_time = $lockout_time - $time_elapsed;
                $minutes = floor($remaining_time / 60);
                $seconds = $remaining_time % 60;
                $login_err = "Çok fazla başarısız giriş denemesi. Lütfen {$minutes} dakika {$seconds} saniye sonra tekrar deneyin.";
                // Hata mesajını göster ve formu işleme
                goto show_form;
            } else {
                // Bekleme süresi dolmuşsa, sayacı sıfırla
                $_SESSION['login_attempts'] = 0;
            }
        }
    } else {
        // İlk giriş denemesi için sayaçları oluştur
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }
    
    if(empty(trim($_POST["username"]))){
        $username_err = "Kullanıcı adını giriniz.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    if(empty(trim($_POST["password"]))){
        $password_err = "Şifrenizi giriniz.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    if(empty($username_err) && empty($password_err)){
        $sql = "SELECT id, username, password FROM admin_users WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            session_start();
                            
                            // Başarılı giriş - sayacı sıfırla
                            $_SESSION['login_attempts'] = 0;
                            
                            // Oturum güvenliğini artır
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = htmlspecialchars($username);
                            $_SESSION["login_time"] = time();
                            $_SESSION["ip_address"] = $_SERVER['REMOTE_ADDR'];
                            $_SESSION["user_agent"] = $_SERVER['HTTP_USER_AGENT'];
                            
                            // Güvenli yönlendirme
                            header("location: dashboard.php");
                            exit();
                        } else{
                            // Başarısız giriş - sayacı artır
                            $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;
                            $_SESSION['last_attempt_time'] = time();
                            
                            $login_err = "Geçersiz kullanıcı adı veya şifre.";
                        }
                    }
                } else{
                    $login_err = "Geçersiz kullanıcı adı veya şifre.";
                }
            } else{
                echo "Bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($conn);
}
?>
 
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>ASEC Admin - Giriş</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .wrapper {
            width: 360px;
            padding: 20px;
            margin: 100px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn-primary {
            width: 100%;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2 class="text-center mb-4">ASEC Admin</h2>
        <p class="text-center mb-4">Yönetim paneline giriş yapın</p>

        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . htmlspecialchars($login_err) . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <!-- CSRF token ekliyoruz -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <div class="form-group">
                <label>Kullanıcı Adı</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Şifre</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Giriş Yap">
            </div>
        </form>
    </div>    
</body>
</html> 