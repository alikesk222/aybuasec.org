<!DOCTYPE html>
<html lang="tr">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title>ASEC Kulübü - Siber Güvenlik ve Yazılım Topluluğu</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/mobile-optimizations.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <section class="hero">
            <div class="hero-container">
                <div class="binary-spiral-container">
                    <div class="binary-spiral"></div>
                </div>
                
                <div class="animated-asec-container">
                    <div class="animated-asec">
                        <span class="letter" data-text="A">A</span>
                        <span class="letter" data-text="S">S</span>
                        <span class="letter" data-text="E">E</span>
                        <span class="letter" data-text="C">C</span>
                    </div>
                </div>
                
                <div class="hero-content">
                    <h2>Geleceğin Teknolojisini Şekillendiriyoruz</h2>
                    <p>ASEC Kulübü ile projeler geliştirin, etkinliklere katılın ve kariyer yolculuğunuzda ilerleyin!</p>
                    <div class="cta-buttons">
                        <a href="register" class="btn-primary">Hemen Katıl</a>
                        <a href="hakkimizda" class="btn-secondary">Daha Fazla Bilgi</a>
                    </div>
                </div>
            </div>
        </section>
        <!--neler sunuyoruz kısmı-->
        <section class="features">
            <h2>Neler Sunuyoruz?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Proje Geliştirme</h3>
                    <p>Proje Komisyonumuz ile fikirlerinizi hayata geçirin. Projenizin başlangıcından tamamlanmasına kadar tüm süreçlerde rehberlik sağlıyor, yenilikçi ve sürdürülebilir projeler geliştirmenize destek oluyoruz.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-code"></i>
                    <h3>Mesleki Etkinlikler</h3>
                    <p>Etkinlik Departmanımız ile sektörden uzmanlarla düzenlenen konferanslar, seminerler ve bilgilendirme etkinlikleri sayesinde yazılım ve inovasyon alanındaki en güncel gelişmeleri takip edin.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <h3>Kariyer Fırsatları</h3>
                    <p>Staj, İş ve Burs Departmanımız aracılığıyla güncel staj olanakları, iş ilanları ve burs fırsatlarına erişin. CV hazırlama desteği ve kariyer rehberliği ile sektöre daha donanımlı adım atın.</p>
                </div>
            </div>
        </section>
        
        <!--yaklaşan etkinlikler-->
        <section class="upcoming-events">
            <h2>Yaklaşan Etkinlikler</h2>
            <div class="events-slider">
                <?php
                require_once 'db.php';
                $today = date('Y-m-d');
                // Yaklaşan etkinlikleri tarihe göre sıralayarak en yakın 3 etkinliği getir
                $stmt = $pdo->prepare("SELECT * FROM etkinlikler WHERE tarih >= :today ORDER BY tarih ASC LIMIT 3");
                $stmt->execute(['today' => $today]);
                $yaklasan_etkinlikler = $stmt->fetchAll();
                
                if (count($yaklasan_etkinlikler) > 0) {
                    foreach ($yaklasan_etkinlikler as $etkinlik) {
                        $tarih = new DateTime($etkinlik['tarih']);
                        $tarih_formati = $tarih->format('d') . ' ' . strftime('%B', $tarih->getTimestamp());
                        ?>
                        <div class="event-card">
                            <div class="event-date"><?= $tarih_formati ?></div>
                            <h3><?= htmlspecialchars($etkinlik['baslik']) ?></h3>
                            <p><?= htmlspecialchars(substr($etkinlik['aciklama'], 0, 150)) . (strlen($etkinlik['aciklama']) > 150 ? '...' : '') ?></p>
                            <a href="etkinlik-detay.php?id=<?= $etkinlik['id'] ?>" class="event-button">Detaylar</a>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="no-events">
                        <i class="fas fa-calendar-alt" style="font-size: 2.5rem; color: #6A0DAD; margin-bottom: 1rem;"></i>
                        <p>Şu anda yaklaşan etkinlik bulunmamaktadır.</p>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="view-all-events">
                <a href="etkinlikler.php" class="view-all-button">Tüm Etkinlikleri Gör</a>
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/animation-controller.js"></script>
    <script src="javascript/script.js"></script>
    <script src="javascript/matrix-animation.js"></script>
    <script src="javascript/binary-spiral.js"></script>
</body>
</html>
