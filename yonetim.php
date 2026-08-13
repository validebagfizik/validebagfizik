<?php
 $pageTitle = "Yönetmelikler ve Eğitim Modeli — Fizik Portalı";
 $currentPage = "yonetim";
require 'includes/header.php';

require_once __DIR__ . '/includes/blok_render.php';

// 1. Eğitim Modeli İntro (İlk açılışta gösterilecek)
 $egitimJson = __DIR__ . '/data/egitim.json';
 $egitimBloklar = [];
if (file_exists($egitimJson)) {
    $egitimVeri = json_decode(file_get_contents($egitimJson), true) ?: [];
    $egitimBloklar = $egitimVeri['page'] ?? [];
}

 $egitimHtml = renderBloklar($egitimBloklar);
 $egitimHtml = str_replace('../uploads/', 'uploads/', $egitimHtml);
 $egitimHtml = str_replace('../assets/', 'assets/', $egitimHtml);

// 2. Yönetim Kayıtları (Kategorilere ayırmak için JS'e göndereceğiz)
 $yonetimJson = __DIR__ . '/data/yonetim.json';
 $yonetimKayitlari = [];
if (file_exists($yonetimJson)) {
    $decoded = json_decode(file_get_contents($yonetimJson), true);
    if (is_array($decoded)) $yonetimKayitlari = $decoded;
}

// Render edilmiş HTML'leri PHP'de hazırlayalım, JS sadece göstersin (daha hızlı çalışır)
 $jsYonetimVeri = [];

// Eğitim modelini 'intro' olarak ekle
 $jsYonetimVeri['intro'] = [
    'meta' => ['baslik' => 'Eğitim Modeli', 'konu' => 'Genel'],
    'html' => $egitimHtml
];

foreach ($yonetimKayitlari as $item) {
    $itemHtml = renderBloklar($item['page'] ?? []);
    $itemHtml = str_replace('../uploads/', 'uploads/', $itemHtml);
    $itemHtml = str_replace('../assets/', 'assets/', $itemHtml);
    
    $jsYonetimVeri[$item['id']] = [
        'meta' => $item['meta'] ?? [],
        'html' => $itemHtml
    ];
}
?>

<!-- İçerik şimdi tam olarak style.css içindeki .soru-layout sınıfından genişliği alıyor -->
   <div class="soru-layout">

    <!-- HAMBURGER BUTON -->
    <button type="button" id="yonetim-menu-toggle" class="kategori-toggle-btn">
        <i class="fa fa-bars"></i> Kategoriler
    </button>

    <!-- KARARTMA -->
    <div id="yonetim-menu-overlay" class="okuma-menu-overlay"></div>

    <!-- SOL MENÜ (Kategoriler) - tıklayınca açılan panel -->
    <aside class="soru-sidebar" id="yonetim-sidebar">
        <div class="sidebar-card">
            <div class="sidebar-header">
                <i class="fa fa-folder-open"></i> Yönetmelikler
            </div>
            <div class="sidebar-content" id="yonetim-menu">
                <p class="empty-state" style="padding: 20px 0; font-size: 14px;">Yükleniyor...</p>
            </div>
        </div>
    </aside>

    <!-- SAĞ İÇERİK -->
    <main class="soru-main">
        <div id="yonetim-icerik" class="soru-rehber site-block detay-kagit" style="padding: 40px; margin-bottom: 40px;">
            <?php echo $egitimHtml !== '' ? $egitimHtml : '<p>İçerik yükleniyor...</p>'; ?>
        </div>

        <!-- YORUM BÖLÜMÜ -->
        <section class="yorum-section" style="padding: 40px 0;">
            <div class="yorum-container">
                <div class="yorum-card">
                    <div class="yorum-header">
                        <i class="fa fa-comments"></i>
                        <span>Yorumlar ve Görüşleriniz</span>
                    </div>
                    <div class="yorum-body">
                        <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                            <form id="comment-form" class="yorum-form">
                                <input type="hidden" id="comment-madde-id" value="intro">
                                <textarea id="comment-text" class="yorum-textarea" rows="4" placeholder="Yorumunuz..." required></textarea>
                                <button type="submit" class="yorum-submit"><i class="fa fa-paper-plane"></i> Gönder</button>
                            </form>
                        <?php else: ?>
                            <div class="yorum-login">
                                <i class="fa fa-lock"></i>
                                <h3>Yorum yapmak için giriş yapmalısınız</h3>
                                <div class="yorum-login-buttons">
                                    <a href="giris.php" class="btn-login">Giriş Yap</a>
                                    <a href="kayit.php" class="btn-register">Kayıt Ol</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="yorumlar-listesi" id="comments-section"></div>
            </div>
        </section>
    </main>
</div>

<script>
    window.YONETIM_VERI = <?php echo json_encode($jsYonetimVeri, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
<script src="assets/js/yonetim.js"></script>

<script>
(function () {
    var toggleBtn = document.getElementById('yonetim-menu-toggle');
    var sidebar   = document.getElementById('yonetim-sidebar');
    var overlay   = document.getElementById('yonetim-menu-overlay');
    var menu      = document.getElementById('yonetim-menu');

    function openMenu() {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (toggleBtn) toggleBtn.addEventListener('click', openMenu);
    if (overlay)   overlay.addEventListener('click', closeMenu);

    // Kategori/madde menüsünde bir öğeye tıklanınca panel otomatik kapanır.
    if (menu) {
        menu.addEventListener('click', function (e) {
            var item = e.target.closest('a, button, [data-id], li');
            if (item) closeMenu();
        });
    }

    // ESC tuşu ile kapatma
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });
})();
</script>

<?php require 'includes/footer.php'; ?>