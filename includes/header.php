<?php
require_once __DIR__ . '/auth.php';

if (!isset($pageTitle)) $pageTitle = "Fizik Portalu";

// Hangi sayfada olduğumuzu otomatik bul ve 'index' ise 'anasayfa' yap
 $script_name = basename($_SERVER['SCRIPT_NAME'], '.php');
if (!isset($currentPage) || empty($currentPage)) {
    if ($script_name === 'index' || $script_name === 'home') {
        $currentPage = 'anasayfa';
    } else {
        $currentPage = $script_name;
    }
}

// --- ZİYARETÇİ SAYACI (İSTATİSTİK İÇİN) ---
 $statsFile = __DIR__ . '/../data/stats.json';
if (file_exists($statsFile)) {
    $statsData = json_decode(file_get_contents($statsFile), true) ?? [];
    $statsData['total_visits'] = ($statsData['total_visits'] ?? 0) + 1;
    if (!isset($statsData['pages']) || !is_array($statsData['pages'])) {
        $statsData['pages'] = [];
    }
    $statsData['pages'][$script_name] = ($statsData['pages'][$script_name] ?? 0) + 1;
    file_put_contents($statsFile, json_encode($statsData, JSON_PRETTY_PRINT));
}

 $isHome = ($currentPage === 'anasayfa' || $currentPage === 'home');
// ------------------------------------------------------------------
// OTOMATİK — aktif tema (Admin panelde hangi tema seçildiyse o gelir)
// ------------------------------------------------------------------
 $gecerliTemalar = ['warm-amber', 'deep-space', 'cyber-ocean', 'forest-matrix'];
 $okunanTema     = file_exists(__DIR__ . '/../aktif_tema.txt') ? trim(file_get_contents(__DIR__ . '/../aktif_tema.txt')) : 'warm-amber';
 $aktifTema      = in_array($okunanTema, $gecerliTemalar, true) ? $okunanTema : 'warm-amber';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap (Dropdown menüler için JS'si lazım) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS Yollaru -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/editor.css" rel="stylesheet">
     
    
    <!-- YENİ EKLENEN: 4 Temalı Sistemin CSS Dosyası -->
    <link href="assets/css/themes.css" rel="stylesheet">

</head>
<!-- YENİ EKLENEN: Body class'ına theme-XXX ekleniyor -->
<body class="theme-<?= htmlspecialchars($aktifTema, ENT_QUOTES, 'UTF-8') ?> <?= $isHome ? 'is-homepage' : '' ?>">

 <div class="page-shell"> 
    <!-- MODERN MENÜ YAPISI -->
    <nav class="site-navbar">
        <div class="nav-container-fluid">
            <a href="index.php" class="brand">Fizik <span>Platformu</span></a>
            <ul class="nav-links">
                <li><a href="index.php" class="<?= $currentPage === 'anasayfa' ? 'active' : '' ?>">Ana Sayfa</a></li>
                <li><a href="deney.php" class="<?= $currentPage === 'deney' ? 'active' : '' ?>">Deney</a></li>
                <li><a href="yonetim.php" class="<?= $currentPage === 'yonetim' ? 'active' : '' ?>">Yönetmelikler</a></li>
                <li><a href="okuma.php" class="<?= $currentPage === 'okuma' ? 'active' : '' ?>">Okuma</a></li>
                <li><a href="soru.php" class="<?= $currentPage === 'soru' ? 'active' : '' ?>">Soru Bankası</a></li>
                <li><a href="sinav_ol.php" class="<?= $currentPage === 'sinav_ol' ? 'active' : '' ?>">Sınav Ol</a></li>
            </ul>
            <div class="nav-auth">
                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="btn-login dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-user-circle"></i> <?= htmlspecialchars($_SESSION['username'] ?? 'Hesabım') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profil.php"><i class="fa fa-user me-2"></i> Profilim</a></li>
                            <?php if (isAdmin()): ?>
                            <li><a class="dropdown-item text-primary fw-bold" href="admin/index.php"><i class="fa fa-cog me-2"></i> Yönetim Paneli</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="cikis.php"><i class="fa fa-sign-out-alt me-2"></i> Çıkış Yap</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="giris.php" class="btn-login">Giriş</a>
                    <a href="kayit_ol.php" class="btn-register">Kayıt Ol</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <?php if ($isHome): ?>
        <!-- Anasayfada #site-content'i burada AÇMIYORUZ, çünkü index.php kendi içinde açacak -->
    <?php else: ?>
        <div id="site-content" class="site-content">
    <?php endif; ?>