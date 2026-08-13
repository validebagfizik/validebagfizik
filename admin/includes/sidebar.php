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
<style>
    .app-sidebar {
        width: 260px;
        min-height: 100vh;
        background: var(--bg-deep);
        border-right: 1px solid var(--glass-border);
        padding: 24px 16px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
    }
    .app-sidebar .brand {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--text-primary);
        font-weight: 700;
        font-size: 18px;
        margin-bottom: 28px;
        padding: 0 8px;
    }
    .app-sidebar .brand i { color: var(--primary-color); }
    .app-sidebar .menu-grup-baslik {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-soft);
        margin: 20px 8px 8px;
    }
    .app-sidebar .menu-grup-baslik:first-of-type { margin-top: 0; }
    .app-sidebar .menu-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 8px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 2px;
        transition: all 0.15s ease;
    }
    .app-sidebar .menu-link i { width: 18px; text-align: center; color: var(--text-soft); }
    .app-sidebar .menu-link:hover {
        background: var(--glass);
        color: var(--text-primary);
    }
    .app-sidebar .menu-link.active {
        background: var(--glass);
        color: var(--active-color);
        border: 1px solid var(--glass-border);
    }
    .app-sidebar .menu-link.active i { color: var(--active-color); }
    
    /* Alt kısımdaki Çıkış ve Siteye Dön butonları için */
    .app-sidebar .sidebar-alt {
        margin-top: auto; /* En alta itler */
        padding-top: 20px;
        border-top: 1px solid var(--glass-border);
    }
    .app-sidebar .cikis-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 8px;
        color: #f87171;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
    }
    .app-sidebar .cikis-link:hover { background: rgba(248, 113, 113, 0.1); }
    
    .app-sidebar .siteye-don-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 8px;
        color: var(--accent-cyan);
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .app-sidebar .siteye-don-link:hover { background: rgba(0, 187, 249, 0.1); }
</style>

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