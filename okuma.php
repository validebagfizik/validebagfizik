<?php
 $pageTitle = "Okuma Parçaları — Fizik Portalı";
 $currentPage = "okuma";
require 'includes/header.php';

require_once __DIR__ . '/includes/blok_render.php';

// ---------------------------------------------------------
// 1. OKUMA KAYITLARINI YÜKLE
// ---------------------------------------------------------
 $okumaJson = __DIR__ . '/data/okuma.json';
 $okumaKayitlari = [];
if (file_exists($okumaJson)) {
    $decoded = json_decode(file_get_contents($okumaJson), true);
    if (is_array($decoded)) $okumaKayitlari = $decoded;
}

// ---------------------------------------------------------
// 2. SEÇİLİ KAYDI BUL (?id=...)
// ---------------------------------------------------------
 $seciliId = isset($_GET['id']) ? (string)$_GET['id'] : null;
 $seciliKayit = null;
if ($seciliId !== null) {
    foreach ($okumaKayitlari as $kayit) {
        if (isset($kayit['id']) && $kayit['id'] === $seciliId) {
            $seciliKayit = $kayit;
            break;
        }
    }
}

// Detay görünümü için içeriği render et
 $seciliHtml = '';
if ($seciliKayit) {
    $seciliHtml = renderBloklar($seciliKayit['page'] ?? []);
    $seciliHtml = str_replace('../uploads/', 'uploads/', $seciliHtml);
    $seciliHtml = str_replace('../assets/', 'assets/', $seciliHtml);
}

// ---------------------------------------------------------
// 3. KONU FİLTRESİ (Liste görünümü için)
// ---------------------------------------------------------
 $konuFiltre = isset($_GET['konu']) ? trim((string)$_GET['konu']) : '';
 $konular = [];
foreach ($okumaKayitlari as $kayit) {
    $konu = $kayit['meta']['konu'] ?? '';
    if ($konu !== '' && !in_array($konu, $konular, true)) {
        $konular[] = $konu;
    }
}
sort($konular);

// Filtrelenmiş liste
 $listelenecek = $konuFiltre === ''
    ? $okumaKayitlari
    : array_values(array_filter($okumaKayitlari, function ($k) use ($konuFiltre) {
        return ($k['meta']['konu'] ?? '') === $konuFiltre;
    }));
?>

<?php if (!$seciliKayit): ?>

    <!-- ============================================================
         LİSTE GÖRÜNÜMÜ (Resimli ve Modern)
    ============================================================ -->
    <main class="readings-archive">
        <div class="container">
            
            <!-- 3 Çizgi (Kategoriler) Butonu -->
            <?php if (!empty($konular)): ?>
            <button id="kategori-toggle-btn" class="kategori-toggle-btn">
                <i class="fa fa-bars"></i> Kategoriler
            </button>
            
            <div id="filtre-cubugu-gizli" class="filtre-cubugu-gizli">
                <a href="okuma.php" class="filtre-pil <?php echo $konuFiltre === '' ? 'aktif' : ''; ?>">Tümü</a>
                <?php foreach ($konular as $konu): ?>
                    <a href="okuma.php?konu=<?php echo urlencode($konu); ?>" class="filtre-pil <?php echo $konuFiltre === $konu ? 'aktif' : ''; ?>">
                        <?php echo htmlspecialchars($konu); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <header class="archive-header">
                <h1 class="archive-title">okuma metinleri</h1>
                <p class="archive-lead">Fizik konularını derinlemesine keşfet</p>
            </header>

            <?php if (empty($listelenecek)): ?>
                <div class="empty-state">
                    <p>Henüz okuma parçası eklenmemiş.</p>
                </div>
            <?php else: ?>
            <div class="readings-grid">
                <?php foreach ($listelenecek as $item): 
                    $meta = $item['meta'] ?? [];
                    $id = $item['id'] ?? '';
                    $coverImg = !empty($meta['kapak']) ? htmlspecialchars($meta['kapak'], ENT_QUOTES, 'UTF-8') : '';
                ?>
                <article class="reading-card">
                    <a href="okuma.php?id=<?php echo urlencode($id); ?>" class="card-thumb">
                        <?php if ($coverImg): ?>
                            <img src="<?php echo $coverImg; ?>" alt="<?php echo htmlspecialchars($meta['baslik'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                        <?php endif; ?>
                        <?php if (!empty($meta['konu'])): ?>
                            <span class="card-category-badge"><?php echo htmlspecialchars($meta['konu']); ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="card-body">
                        <h2 class="card-title">
                            <a href="okuma.php?id=<?php echo urlencode($id); ?>">
                                <?php echo htmlspecialchars($meta['baslik'] ?? 'Başlıksız'); ?>
                            </a>
                        </h2>
                        <div class="card-meta-row">
                            <span><i class="fa fa-user"></i> <?php echo htmlspecialchars($meta['yazar'] ?? ''); ?></span>
                            <?php if (!empty($meta['tarih'])): ?>
                                <time><i class="fa fa-clock"></i> <?php echo htmlspecialchars($meta['tarih']); ?></time>
                            <?php endif; ?>
                        </div>
                        <a href="okuma.php?id=<?php echo urlencode($id); ?>" class="card-link">Oku →</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </main>

<?php else: ?>

    <!-- ============================================================
         DETAY (KAĞIT) GÖRÜNÜMÜ
    ============================================================ -->
    <?php $meta = $seciliKayit['meta'] ?? []; ?>

    <main class="reading-main">
        <div class="reading-container">
            
            <?php if (!empty($meta['kapak'])): ?>
                <div class="reading-cover">
                    <img src="<?php echo htmlspecialchars($meta['kapak']); ?>" alt="Kapak">
                </div>
            <?php endif; ?>
            
            <div class="reading-meta">
                <div class="detay-geri" style="margin-bottom: 16px;">
                    <a href="okuma.php" style="text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 14px;">
                        <i class="fa fa-arrow-left"></i> Tüm Okuma Parçalarına Dön
                    </a>
                </div>
                
                <?php if (!empty($meta['konu'])): ?>
                    <span class="badge"><?php echo htmlspecialchars($meta['konu']); ?></span>
                <?php endif; ?>
                
                <h1 class="reading-title"><?php echo htmlspecialchars($meta['baslik'] ?? 'Başlıksız'); ?></h1>
                
                <div class="reading-author-date">
                    <?php if (!empty($meta['yazar'])): ?>
                        <span><i class="fa fa-user"></i> <?php echo htmlspecialchars($meta['yazar']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($meta['tarih'])): ?>
                        <span><i class="fa fa-clock"></i> <?php echo htmlspecialchars($meta['tarih']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="reading-content site-block">
                <?php echo $seciliHtml !== '' ? $seciliHtml : '<p>İçerik bulunamadı.</p>'; ?>
            </div>

            <!-- YORUM BÖLÜMÜ -->
            <section class="yorum-section" style="padding: 40px 48px 64px; max-width: 100%; margin: 0;">
                <div class="yorum-container" style="max-width: 100%;">
                    <div class="yorum-card">
                        <div class="yorum-header">
                            <i class="fa fa-comments"></i>
                            <span>Yorumlar ve Görüşleriniz</span>
                        </div>
                        <div class="yorum-body">
                            <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                                <form id="comment-form" class="yorum-form">
                                    <input type="hidden" id="comment-madde-id" value="<?php echo htmlspecialchars($seciliKayit['id']); ?>">
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

        </div>
    </main>

<?php endif; ?>

<script src="assets/js/yonetim.js"></script>

<!-- 3 Çizgi butonuna basınca kategorileri açıp kapatmak için JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toggleBtn = document.getElementById('kategori-toggle-btn');
        var filtreCubugu = document.getElementById('filtre-cubugu-gizli');

        if (toggleBtn && filtreCubugu) {
            toggleBtn.addEventListener('click', function() {
                filtreCubugu.classList.toggle('aktif');
            });
        }
    });
</script>

<?php require 'includes/footer.php'; ?>