<?php
// admin/index.php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

// Tema Ayarı
 $ayar_dosyasi = __DIR__ . '/../aktif_tema.txt';
if (!file_exists($ayar_dosyasi)) file_put_contents($ayar_dosyasi, 'warm-amber');
 $aktif_tema = trim(file_get_contents($ayar_dosyasi));
 $gecerli_temalar = ['warm-amber', 'deep-space', 'cyber-ocean', 'forest-matrix', 'midnight-cherry', 'arctic-frost'];
if (!in_array($aktif_tema, $gecerli_temalar)) $aktif_tema = 'warm-amber';

function countRecords($fileName) {
    $path = __DIR__ . '/../data/' . $fileName;
    if (file_exists($path)) {
        $data = json_decode(file_get_contents($path), true);
        return is_array($data) ? count($data) : 0;
    }
    return 0;
}

 $yonetimCount = countRecords('yonetim.json'); // Düzeltildi: meb.json değil yonetim.json
 $deneyCount = countRecords('deney.json');
 $soruCount = countRecords('soru.json');
 $okumaCount = countRecords('okuma.json');
 $usersCount = countRecords('giris.json');

 $pendingComments = 0;
 $commentsPath = __DIR__ . '/../data/comments.json';
if (file_exists($commentsPath)) {
    $comments = json_decode(file_get_contents($commentsPath), true) ?? [];
    foreach ($comments as $c) {
        if (($c['status'] ?? '') === 'pending') $pendingComments++;
    }
}

 $siteVisits = 0;
 $userLogins = 0;
 $statsPath = __DIR__ . '/../data/stats.json';
if (file_exists($statsPath)) {
    $statsData = json_decode(file_get_contents($statsPath), true) ?? [];
    $siteVisits = $statsData['total_visits'] ?? 0;
    $userLogins = $statsData['total_logins'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gösterge Paneli — Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/editor.css" rel="stylesheet">
    <link href="../assets/css/themes.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        .admin-stat-card { text-decoration: none !important; color: inherit !important; cursor: pointer; position: relative; transition: all 0.2s ease; overflow: hidden; }
        .admin-stat-card:hover { transform: translateY(-4px); border-color: var(--accent-lime) !important; box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
        .stat-card-link { display: block; margin-top: 12px; font-size: 0.75rem; font-weight: 600; color: var(--accent-cyan); opacity: 0.7; transition: opacity 0.2s; }
        .admin-stat-card:hover .stat-card-link { opacity: 1; color: var(--accent-lime); }
    </style>
</head>
<body class="theme-<?php echo htmlspecialchars($aktif_tema); ?>">
    <div class="admin-layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main" style="flex-grow: 1;">
            <header class="admin-topbar">
                <h1><i class="fa fa-tachometer-alt"></i> Gösterge Paneli</h1>
                <div>Admin: <strong><?php echo htmlspecialchars($_SESSION['kullanici_ad'] ?? 'Yönetici'); ?></strong></div>
            </header>

            <div class="admin-content">
                <div class="admin-card">
                    <div class="card-header">
                        <h2><i class="fa fa-th-large"></i> Hoş Geldiniz, <?php echo htmlspecialchars($_SESSION['kullanici_ad'] ?? 'Admin'); ?>!</h2>
                    </div>
                    <div class="card-body">
                        <p style="color: var(--text-muted); margin: 0;">Sitenizin genel durumuna ve istatistiklerine aşağıdan ulaşabilirsiniz. Detaylar için kartlara tıklayın.</p>
                    </div>
                </div>

                <!-- SİTE TRAFİK İSTATİSTİKLERİ (Tıklanınca Detay Sayfasına Gider) -->
                <div class="admin-stats-grid">
                    <a href="istatistikler.php" class="admin-stat-card">
                        <div class="admin-stat-icon cyan">
                            <i class="fa fa-eye"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo number_format($siteVisits, 0, ',', '.'); ?></h3>
                            <p>Toplam Sayfa Görüntülenme</p>
                            <span class="stat-card-link">Detaylı İstatistik <i class="fa fa-arrow-right"></i></span>
                        </div>
                    </a>
                    <a href="istatistikler.php" class="admin-stat-card">
                        <div class="admin-stat-icon lime">
                            <i class="fa fa-sign-in-alt"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo number_format($userLogins, 0, ',', '.'); ?></h3>
                            <p>Toplam Kullanıcı Girişi</p>
                            <span class="stat-card-link">Detaylı İstatistik <i class="fa fa-arrow-right"></i></span>
                        </div>
                    </a>
                    <a href="yorumlar.php" class="admin-stat-card">
                        <div class="admin-stat-icon <?php echo $pendingComments > 0 ? 'gold' : 'lime'; ?>">
                            <i class="fa fa-comments"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo $pendingComments; ?></h3>
                            <p>Bekleyen Yorum</p>
                            <span class="stat-card-link">İncele <i class="fa fa-arrow-right"></i></span>
                        </div>
                    </a>
                    <a href="kullanici.php" class="admin-stat-card">
                        <div class="admin-stat-icon purple">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo $usersCount; ?></h3>
                            <p>Kayıtlı Kullanıcı</p>
                            <span class="stat-card-link">Yönet <i class="fa fa-arrow-right"></i></span>
                        </div>
                    </a>
                </div>

                <!-- İÇERİK İSTATİSTİKLERİ -->
                <div class="admin-stats-grid">
                    <a href="yonetim.php" class="admin-stat-card">
                        <div class="admin-stat-icon purple">
                            <i class="fa fa-gavel"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo $yonetimCount; ?></h3>
                            <p>Yönetmelik</p>
                            <span class="stat-card-link">Düzenle <i class="fa fa-arrow-right"></i></span>
                        </div>
                    </a>
                    <a href="deney.php" class="admin-stat-card">
                        <div class="admin-stat-icon gold">
                            <i class="fa fa-flask"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo $deneyCount; ?></h3>
                            <p>Deney</p>
                            <span class="stat-card-link">Düzenle <i class="fa fa-arrow-right"></i></span>
                        </div>
                    </a>
                    <a href="soru.php" class="admin-stat-card">
                        <div class="admin-stat-icon cyan">
                            <i class="fa fa-clipboard-list"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo $soruCount; ?></h3>
                            <p>Soru</p>
                            <span class="stat-card-link">Düzenle <i class="fa fa-arrow-right"></i></span>
                        </div>
                    </a>
                    <a href="okuma.php" class="admin-stat-card">
                        <div class="admin-stat-icon lime">
                            <i class="fa fa-book-open"></i>
                        </div>
                        <div class="admin-stat-info">
                            <h3><?php echo $okumaCount; ?></h3>
                            <p>Okuma Parçası</p>
                            <span class="stat-card-link">Düzenle <i class="fa fa-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>