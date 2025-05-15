<?php
// Veritabanı bağlantısı
require_once '../db.php';

// AJAX isteğinden etkinlik ID'sini al
$etkinlik_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($etkinlik_id <= 0) {
    echo '<div class="error">Geçersiz etkinlik ID\'si.</div>';
    exit;
}

try {
    // Etkinlik detaylarını getir
    $stmt = $pdo->prepare("SELECT * FROM etkinlikler WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $etkinlik_id]);
    $etkinlik = $stmt->fetch();

    if (!$etkinlik) {
        echo '<div class="error">Etkinlik bulunamadı.</div>';
        exit;
    }

    // Tarih ve saat formatını düzenle
    $tarih = new DateTime($etkinlik['tarih']);
    $tarih_formati = $tarih->format('d.m.Y');
    
    // HTML çıktısı oluştur
    ?>
    <div class="etkinlik-detay-modal">
        <h2><?= htmlspecialchars($etkinlik['baslik']) ?></h2>
        
        <div class="etkinlik-meta">
            <div class="meta-item">
                <i class="fas fa-calendar-alt"></i> 
                <span><?= $tarih_formati ?></span>
            </div>
            <div class="meta-item">
                <i class="fas fa-clock"></i> 
                <span><?= htmlspecialchars($etkinlik['saat']) ?></span>
            </div>
            <div class="meta-item">
                <i class="fas fa-map-marker-alt"></i> 
                <span><?= htmlspecialchars($etkinlik['yer']) ?></span>
            </div>
        </div>
        
        <div class="etkinlik-aciklama">
            <p><?= nl2br(htmlspecialchars($etkinlik['aciklama'])) ?></p>
        </div>
        
        <?php if (!empty($etkinlik['kayit_link'])): ?>
        <div class="etkinlik-kayit">
            <a href="<?= htmlspecialchars($etkinlik['kayit_link']) ?>" class="kayit-btn" target="_blank">
                <i class="fas fa-user-plus"></i> Kayıt Ol
            </a>
        </div>
        <?php endif; ?>
        
        <div class="etkinlik-footer">
            <a href="etkinlik-detay.php?id=<?= $etkinlik['id'] ?>" class="detay-btn">
                <i class="fas fa-external-link-alt"></i> Etkinlik Sayfasına Git
            </a>
        </div>
    </div>
    
    <style>
    .etkinlik-detay-modal {
        padding: 10px;
    }
    
    .etkinlik-detay-modal h2 {
        color: #1B1F3B;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #6A0DAD;
    }
    
    .etkinlik-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 25px;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .meta-item i {
        color: #6A0DAD;
        font-size: 1.1rem;
    }
    
    .etkinlik-aciklama {
        margin-bottom: 25px;
        line-height: 1.6;
    }
    
    .etkinlik-kayit {
        margin-bottom: 20px;
    }
    
    .kayit-btn {
        display: inline-block;
        background: linear-gradient(135deg, #6A0DAD, #9370DB);
        color: white;
        padding: 10px 20px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .kayit-btn:hover {
        background: linear-gradient(135deg, #4B0082, #6A0DAD);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(106, 13, 173, 0.3);
    }
    
    .etkinlik-footer {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #eee;
        text-align: right;
    }
    
    .detay-btn {
        display: inline-block;
        color: #6A0DAD;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .detay-btn:hover {
        color: #4B0082;
        text-decoration: underline;
    }
    
    @media (max-width: 768px) {
        .etkinlik-meta {
            flex-direction: column;
            gap: 10px;
        }
    }
    </style>
    <?php
} catch (PDOException $e) {
    echo '<div class="error">Veritabanı hatası: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
