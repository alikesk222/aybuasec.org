<?php
// CSS artık doğrudan sayfalarda yükleniyor, burada bir şey yapmaya gerek yok
if (!defined('HEADER_CSS_LOADED')) {
    define('HEADER_CSS_LOADED', true);
}
?>
<header>
    <div class="header-container">
        <div class="mobile-menu">
            <i class="fas fa-bars"></i>
            <i class="fas fa-times" style="display: none;"></i>
        </div>
        <div class="logo">
            <a href="index.php">
                <img src="images/gallery/logo3.png" alt="ASEC Logo" id="site-logo">
            </a>
            <link rel="icon" href="images/gallery/logo3.png" type="image/png">
        </div>
        <nav>
            <ul class="nav-links">
                <li><a href="index"><i class="fas fa-home"></i> Ana Sayfa</a></li>
                <li class="dropdown">
                    <a href="#"><i class="fas fa-info-circle"></i> Hakkımızda <i class="fas fa-chevron-down"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="hakkimizda">Kulüp Hakkında</a></li>
                        <li><a href="takimlar"><i class="fas fa-users"></i> Takımlar</a></li>
                        <li><a href="galeri"><i class="fas fa-images"></i> Galeri</a></li>
                    </ul>
                </li>
                <li><a href="duyurular"><i class="fas fa-bullhorn"></i> Duyurular</a></li>
                <li><a href="etkinlikler"><i class="fas fa-calendar-alt"></i> Etkinlikler</a></li>
                <li><a href="blog"><i class="fas fa-blog"></i> Blog</a></li>
                <li><a href="iletisim"><i class="fas fa-envelope"></i> İletişim</a></li>
            </ul>
        </nav>
        <?php
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (isset($_SESSION['user'])) {
        ?>
            <div class="auth-buttons">
                <a href="profilim" class="btn-login"><i class="fas fa-user"></i> Profilim</a>
                <a href="logout" class="btn-register"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
            </div>
        <?php } else { ?>
            <div class="auth-buttons">
                <a href="login" class="btn-login">Giriş Yap</a>
                <a href="register" class="btn-register">Üye Ol</a>
            </div>
        <?php } ?>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobil menü açma/kapama
        const mobileMenu = document.querySelector('.mobile-menu');
        const navLinks = document.querySelector('.nav-links');
        const menuBars = document.querySelector('.fa-bars');
        const menuClose = document.querySelector('.fa-times');
        
        mobileMenu.addEventListener('click', function() {
            const nav = document.querySelector('nav');
            nav.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            
            if (mobileMenu.classList.contains('active')) {
                menuBars.style.display = 'none';
                menuClose.style.display = 'block';
            } else {
                menuBars.style.display = 'block';
                menuClose.style.display = 'none';
            }
        });
        
        // Dropdown menüleri mobil görünümde açma/kapama
        const dropdowns = document.querySelectorAll('.dropdown');
        
        dropdowns.forEach(dropdown => {
            const dropdownLink = dropdown.querySelector('a');
            
            dropdownLink.addEventListener('click', function(e) {
                if (window.innerWidth <= 968) {
                    e.preventDefault();
                    dropdown.classList.toggle('active');
                }
            });
        });
    });
</script>
