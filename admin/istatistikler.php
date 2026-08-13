<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

// Tema Ayarı
 $ayar_dosyasi = __DIR__ . '/../aktif_tema.txt';
if (!file_exists($ayar_dosyasi)) file_put_contents($ayar_dosyasi, 'warm-amber');
 $aktif_tema = trim(file_get_contents($ayar_dosyasi));
 $gecerli_temalar = ['warm-amber', 'deep-space', 'cyber-ocean', 'forest-matrix', 'midnight-cherry', 'arctic-frost'];
if (!in_array($aktif_tema, $gecerli_temalar)) $aktif_tema = 'warm-amber';

 $statsPath = __DIR__ . '/../data/stats.json';
 $stats = ['total_visits' => 0, 'total_logins' => 0, 'pages' => []];
if (file_exists($statsPath)) {
    $stats = json_decode(file_get_contents($statsPath), true) ?? $stats;
}

 $sayfalar = $stats['pages'] ?? [];
arsort($sayfalar); // Çok ziyaret edilenden aza doğru sırala
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detaylı İstatistikler — Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/editor.css" rel="stylesheet">
    <link href="../assets/css/themes.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        .stat-row { display: flex; justify-content: space-between; padding: 15px 20px; border-bottom: 1px solid var(--glass-border); }
        .stat-row:last-child { border-bottom: none; }
        .stat-page-name { font-weight: 600; color: var(--text-primary); }
        .stat-page-count { background: rgba(200, 245, 66, 0.15); color: var(--accent-lime); padding: 4px 12px; border-radius: 999px; font-weight: 700; font-size: 0.9rem; }
    </style>
</head>
<body class="theme-<?php echo htmlspecialchars($aktif_tema); ?>">
    <div class="admin-layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main" style="flex-grow: 1;">
            <header class="admin-topbar">
                <h1><i class="fa fa-chart-line"></i> Detaylı İstatistikler</h1>
                <div>Admin: <strong><?php echo htmlspecialchars($_SESSION['kullanici_ad'] ?? 'Yönetici'); ?></strong></div>
            </header>

            <div class="admin-content">
                <div class="admin-card">
                    <div class="card-header">
                        <h2><i class="fa fa-globe"></i> Sayfa Görüntülenme Detayları</h2>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($sayfalar)): ?>
                            <div class="admin-empty" style="padding: 40px; text-align: center;">
                                <i class="fa fa-chart-bar" style="font-size: 2rem; opacity: 0.3; margin-bottom: 15px;"></i>
                                <p>Henüz sayfa görüntülenmesi kaydedilmemiş.</p>
                                <small style="color: var(--text-soft);">Kullanıcılar sitede dolaştıkça bu liste dolacaktır.</small>
                            </div>
                        <?php else: ?>
                            <?php foreach ($sayfalar as $sayfaAdi => $hit): ?>
                                <div class="stat-row">
                                    <span class="stat-page-name"><i class="fa fa-file-alt" style="color: var(--accent-cyan); margin-right: 10px;"></i> <?php echo htmlspecialchars($sayfaAdi); ?></span>
                                    <span class="stat-page-count"><?php echo number_format($hit, 0, ',', '.'); ?> Ziyaret</span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>