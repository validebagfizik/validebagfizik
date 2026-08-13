<?php
/*
    admin/includes/sidebar.php
    ===================================================================
    Admin panelin sol menüsü. Her admin/*.php dosyasına include edilir.
*/
 $aktifSayfa = basename($_SERVER['SCRIPT_NAME'], '.php');

 $menuGruplari = [
    'Genel' => [
        ['sayfa' => 'index',   'baslik' => 'Gösterge Paneli',   'icon' => 'fa-tachometer-alt'],
    ],
    'İçerik Yönetimi' => [
        ['sayfa' => 'anasayfa', 'baslik' => 'Anasayfa İçeriği',  'icon' => 'fa-house'],
        ['sayfa' => 'egitim',   'baslik' => 'Eğitim Modeli',        'icon' => 'fa-graduation-cap'],
        ['sayfa' => 'yonetim', 'baslik' => 'Yönetmelikler',        'icon' => 'fa-landmark'],
        ['sayfa' => 'soru_ilk', 'baslik' => 'Soru Bankası Tanıtımı','icon' => 'fa-circle-info'],
        ['sayfa' => 'soru',     'baslik' => 'Soru Bankası',         'icon' => 'fa-list-check'],
        ['sayfa' => 'deney',    'baslik' => 'Deneyler',             'icon' => 'fa-flask'],
        ['sayfa' => 'okuma',    'baslik' => 'Okuma Metinleri',      'icon' => 'fa-book-open'],
    ],
    'Etkileşim' => [
        ['sayfa' => 'yorumlar',  'baslik' => 'Yorumlar',   'icon' => 'fa-comments'],
        ['sayfa' => 'kullanici', 'baslik' => 'Kullanıcılar','icon' => 'fa-users'],
        ['sayfa' => 'istatistikler', 'baslik' => 'İstatistikler', 'icon' => 'fa-chart-line'],
    ],
    'Hesap' => [
        ['sayfa' => 'sifre', 'baslik' => 'Şifre Değiştir', 'icon' => 'fa-key'],
    ],
];
?>

<nav class="app-sidebar">
    <div class="brand">
        <i class="fa-solid fa-atom"></i> Fizik Portalı
    </div>

    <?php foreach ($menuGruplari as $grupBaslik => $ogeler): ?>
        <div class="menu-grup-baslik"><?php echo htmlspecialchars($grupBaslik); ?></div>
        <?php foreach ($ogeler as $oge):
            $aktifMi = ($aktifSayfa === $oge['sayfa']);
        ?>
            <a href="<?php echo htmlspecialchars($oge['sayfa']); ?>.php"
               class="menu-link<?php echo $aktifMi ? ' active' : ''; ?>">
                <i class="fa-solid <?php echo htmlspecialchars($oge['icon']); ?>"></i>
                <?php echo htmlspecialchars($oge['baslik']); ?>
            </a>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <!-- EN ALTTA ÖZEL LINKLER -->
    <div class="sidebar-alt">
        <a href="../index.php" class="siteye-don-link" target="_blank">
            <i class="fa-solid fa-globe"></i> Siteye Dön (Yeni Sekme)
        </a>
        <a href="../cikis.php" class="cikis-link">
            <i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap
        </a>
    </div>
</nav>