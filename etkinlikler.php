<!DOCTYPE html>
<html lang="tr">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title>Etkinlikler - ASEC Kulübü</title>
    <link rel="stylesheet" href="css/etkinlikler.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="events-container">
            <h2 class="page-title">Etkinliklerimiz</h2>
            <?php
            require_once 'db.php';
            $today = date('Y-m-d');
            $stmt = $pdo->prepare("SELECT * FROM etkinlikler ORDER BY tarih DESC");
            $stmt->execute();
            $etkinlikler = $stmt->fetchAll();
            ?>
            <!-- Yaklaşan Etkinlikler -->
            <section class="events-section">
                <h3 class="section-title">Yaklaşan Etkinlikler</h3>
                <div class="events-grid">
                <?php foreach ($etkinlikler as $etkinlik):
                    if ($etkinlik['tarih'] >= $today): ?>
                    <div class="event-card upcoming">
                        <div class="event-date">
                            <?php $t = strtotime($etkinlik['tarih']); ?>
                            <span class="day"><?= date('d', $t) ?></span>
                            <span class="month"><?= strftime('%B', $t) ?></span>
                        </div>
                        <div class="event-details">
                            <h4><?= htmlspecialchars($etkinlik['baslik']) ?></h4>
                            <p class="event-description"><?= htmlspecialchars(mb_substr($etkinlik['aciklama'], 0, 120)) . (mb_strlen($etkinlik['aciklama']) > 120 ? '...' : '') ?></p>
                            <div class="event-info">
                                <span><i class="fas fa-clock"></i> <?= htmlspecialchars($etkinlik['saat']) ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($etkinlik['yer']) ?></span>
                            </div>
                            <div style="margin-top: 8px; display: flex; gap: 8px;">
                                <a href="etkinlik-detay.php?id=<?= $etkinlik['id'] ?>" class="btn btn-sm btn-info" style="background:#eee;border-radius:4px;padding:5px 12px;font-size:14px;">Detay</a>
<a href="#" class="btn btn-sm btn-info detay-modal-btn" data-id="<?= $etkinlik['id'] ?>" style="background:#e3e6f3;border-radius:4px;padding:5px 12px;font-size:14px;">Hızlı Detay</a>
                                <?php if (!empty($etkinlik['kayit_link'])): ?>
                                    <a href="<?= htmlspecialchars($etkinlik['kayit_link']) ?>" class="register-btn" target="_blank">Kayıt Ol</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; endforeach; ?>
                </div>
            </section>
            <!-- Geçmiş Etkinlikler -->
            <section class="events-section past-events">
                <h3 class="section-title">Geçmiş Etkinlikler</h3>
                <div class="events-grid">
                <?php foreach ($etkinlikler as $etkinlik):
                    if ($etkinlik['tarih'] < $today): ?>
                    <div class="event-card past">
                        <div class="event-date">
                            <?php $t = strtotime($etkinlik['tarih']); ?>
                            <span class="day"><?= date('d', $t) ?></span>
                            <span class="month"><?= strftime('%B', $t) ?></span>
                        </div>
                        <div class="event-details">
                            <h4><?= htmlspecialchars($etkinlik['baslik']) ?></h4>
                            <p class="event-description"><?= htmlspecialchars(mb_substr($etkinlik['aciklama'], 0, 120)) . (mb_strlen($etkinlik['aciklama']) > 120 ? '...' : '') ?></p>
                            <div class="event-info">
                                <span><i class="fas fa-clock"></i> <?= htmlspecialchars($etkinlik['saat']) ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($etkinlik['yer']) ?></span>
                            </div>
                            <div style="margin-top: 8px; display: flex; gap: 8px;">
                                <a href="etkinlik-detay.php?id=<?= $etkinlik['id'] ?>" class="btn btn-sm btn-info" style="background:#eee;border-radius:4px;padding:5px 12px;font-size:14px;">Detay</a>
<a href="#" class="btn btn-sm btn-info detay-modal-btn" data-id="<?= $etkinlik['id'] ?>" style="background:#e3e6f3;border-radius:4px;padding:5px 12px;font-size:14px;">Hızlı Detay</a>
                            </div>
                        </div>
                    </div>
                <?php endif; endforeach; ?>
                </div>
            </section>
            </section>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    
    <!-- Etkinlik Detay Modal -->
    <div id="etkinlikDetayModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalContent" class="modal-body">
                <!-- AJAX ile yüklenecek içerik -->
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Yükleniyor...
                </div>
            </div>
        </div>
    </div>
    
    <script src="javascript/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal elementlerini seç
        const modal = document.getElementById('etkinlikDetayModal');
        const modalContent = document.getElementById('modalContent');
        const closeBtn = document.getElementsByClassName('close')[0];
        
        // Tüm hızlı detay butonlarını seç
        const detayBtns = document.querySelectorAll('.detay-modal-btn');
        
        // Her butona tıklama olayı ekle
        detayBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const etkinlikId = this.getAttribute('data-id');
                
                // Modal'ı göster
                modal.style.display = 'block';
                modalContent.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</div>';
                
                // AJAX ile etkinlik detaylarını getir
                fetch('ajax/etkinlik-detay-getir.php?id=' + etkinlikId)
                    .then(response => response.text())
                    .then(data => {
                        modalContent.innerHTML = data;
                    })
                    .catch(error => {
                        modalContent.innerHTML = '<div class="error">Etkinlik detayları yüklenirken bir hata oluştu.</div>';
                        console.error('Hata:', error);
                    });
            });
        });
        
        // Kapatma düğmesine tıklama olayı
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Modal dışına tıklayarak kapatma
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    });
    </script>
    
    <style>
    /* Modal Stili */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.7);
    }
    
    .modal-content {
        background: #fff;
        margin: 10% auto;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 5px 30px rgba(0,0,0,0.3);
        width: 80%;
        max-width: 700px;
        position: relative;
        animation: modalFadeIn 0.3s;
    }
    
    @keyframes modalFadeIn {
        from {opacity: 0; transform: translateY(-30px);}
        to {opacity: 1; transform: translateY(0);}
    }
    
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        position: absolute;
        right: 15px;
        top: 10px;
    }
    
    .close:hover,
    .close:focus {
        color: #1B1F3B;
        text-decoration: none;
        cursor: pointer;
    }
    
    .modal-body {
        padding: 10px 0;
    }
    
    .loading {
        text-align: center;
        padding: 30px;
        color: #6A0DAD;
    }
    
    .loading i {
        margin-right: 10px;
        font-size: 1.5rem;
    }
    
    .error {
        color: #e74c3c;
        text-align: center;
        padding: 20px;
    }
    </style>
</body>
</html>
