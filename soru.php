<?php
/*
    soru.php  (SİTE KÖKÜNE — senin header.php/footer.php yapını kullanır)
    ===================================================================
    HTML sınıf yapısı style.css'teki isimlere göre kuruldu:
    .soru-layout > .soru-sidebar (.sidebar-card) + .soru-main (.soru-rehber / .soru-sinav)

    Doğru cevap SADECE data/soru.json'daki meta.dogru alanından okunur.
    İçeriğe gömülü "Doğru Cevap Belirle" dropdown'u öğrenciye hiç
    gösterilmez (sorubloklariniAyikla bunu bilerek atlıyor).
    ===================================================================
*/
 $pageTitle = "Soru Bankası — Fizik Portalı";
require __DIR__ . '/includes/header.php';

require_once __DIR__ . '/includes/blok_render.php';

// Tanıtım metni (admin/soru_ilk.php -> data/soru_ilk.json)
 $introJson = __DIR__ . '/data/soru_ilk.json';
 $introBloklar = [];
if (file_exists($introJson)) {
    $introVeri = json_decode(file_get_contents($introJson), true) ?: [];
    $introBloklar = $introVeri['page'] ?? [];
}

// Soru kayıtları (admin/soru.php -> data/soru.json)
 $soruJson = __DIR__ . '/data/soru.json';
 $tumSorular = [];
if (file_exists($soruJson)) {
    $tumSorular = json_decode(file_get_contents($soruJson), true) ?: [];
}

// Sınıf/Konu seçim listelerini kayıtlardan otomatik çıkar
 $siniflar = [];
 $konular = [];
foreach ($tumSorular as $s) {
    $m = $s['meta'] ?? [];
    if (!empty($m['sinif']) && !in_array($m['sinif'], $siniflar, true)) $siniflar[] = $m['sinif'];
    if (!empty($m['konu']) && !in_array($m['konu'], $konular, true)) $konular[] = $m['konu'];
}
sort($siniflar);
sort($konular);

// JS'e sadece gereken alanları gönder
 $jsSorular = array_map(function ($s) {
    return [
        'id'   => $s['id'] ?? '',
        'meta' => $s['meta'] ?? [],
        'page' => $s['page'] ?? [],
    ];
}, array_values($tumSorular));

// RESİM YOLU DÜZELTMESİ:
// Admin panelinde kaydedilen tüm göreli yolları kök dizinden (/uploads/) başlayacak şekilde düzenliyoruz.
 $jsSorularJson = json_encode($jsSorular, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
 $jsSorularJson = str_replace(['../uploads/', './uploads/'], 'uploads/', $jsSorularJson);
 $jsSorularJson = str_replace(['../assets/', './assets/'], 'assets/', $jsSorularJson);
?>

<div class="soru-layout" id="soru-layout-kapsayici">

    <!-- HAMBURGER BUTON -->
    <button type="button" id="soru-menu-toggle" class="kategori-toggle-btn">
        <i class="fa fa-bars"></i> Soru Seç
    </button>

    <!-- KARARTMA -->
    <div id="soru-menu-overlay" class="okuma-menu-overlay"></div>

    <!-- SOL MENÜ (Açılır Kapanır Panel) -->
    <aside class="soru-sidebar" id="soru-sidebar">
        <div class="sidebar-card">
            <div class="sidebar-header">
                <i class="fa-solid fa-sliders"></i> Soru Seç
            </div>
            <div class="sidebar-content">
                <div class="filtre-grup">
                    <label>Sınıf Seviyesi</label>
                    <select id="secim-sinif">
                        <option value="">Tümü</option>
                        <?php foreach ($siniflar as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filtre-grup">
                    <label>Konu</label>
                    <select id="secim-konu">
                        <option value="">Tümü</option>
                        <?php foreach ($konular as $k): ?>
                            <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                  <button type="button" id="quiz-basla-btn" class="btn-kaydet-ana" style="width:100%;">
                    <i class="fa-solid fa-play"></i> Sorulara Başla
                </button>
                <p class="soru-toplam-bilgi"><?= count($tumSorular) ?> soru mevcut.</p>
                
                <!-- Kapatma Yönlendirmesi -->
                <div class="sidebar-kapat-yazi">
                    Menüyü kapatmak için boş bir alana tıklayın.
                </div>
            </div>
        </div>
    </aside>

    <!-- SAĞ İÇERİK -->
    <section class="soru-main">

        <div id="soru-intro" class="soru-rehber editor-content">
            <?php
            $introHtml = renderBloklar($introBloklar);
            // Kök dizin yol düzeltmesi:
          $introHtml = str_replace(['../uploads/', './uploads/'], 'uploads/', $introHtml);
          $introHtml = str_replace(['../assets/', './assets/'], 'assets/', $introHtml);

            echo $introHtml !== '' ? $introHtml : '<p>Fizik sorularını çözmeye başlamak için sol taraftan sınıf ve konu seçip <strong>"Sorulara Başla"</strong> butonuna tıkla.</p>';
            ?>
        </div>

        <div id="soru-quiz-alani" class="soru-sinav" style="display:none;">

            <div class="quiz-ust-cubuk">
                <span id="quiz-ilerleme"></span>
            </div>

            <div id="quiz-govde" class="quiz-govde"></div>

            <div id="quiz-secenekler" class="siklar-alani"></div>

            <div class="quiz-yardim-cubugu">
                <button type="button" id="ipucu-btn" class="quiz-yardim-btn" style="display:none;">
                    <i class="fa-solid fa-key"></i> İpucu
                </button>
                <button type="button" id="cozum-btn" class="quiz-yardim-btn" style="display:none;">
                    <i class="fa-solid fa-lightbulb"></i> Çözümü Gör
                </button>
            </div>
            <div id="ipucu-kutu" class="quiz-yardim-kutu" style="display:none;"></div>
            <div id="cozum-kutu" class="quiz-yardim-kutu" style="display:none;"></div>

            <div id="quiz-topluluk-istatistik" class="quiz-topluluk-istatistik"></div>

            <div class="soru-navigasyon">
                <button type="button" id="quiz-sonraki-btn" class="btn-kaydet-ana" style="display:none;">
                    Sonraki Soru <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>

        </div>

    </section>
</div>

<script>window.SORU_KAYITLARI = <?php echo $jsSorularJson; ?>;</script>
<script src="assets/js/soru-quiz.js"></script>

<!-- Off-canvas Menü Aç/Kapat JS -->
<script>
(function () {
    var toggleBtn = document.getElementById('soru-menu-toggle');
    var sidebar   = document.getElementById('soru-sidebar');
    var overlay   = document.getElementById('soru-menu-overlay');
    var baslaBtn  = document.getElementById('quiz-basla-btn');

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
    
    // "Sorulara Başla" butonuna basınca da menü kapansın
    if (baslaBtn) baslaBtn.addEventListener('click', closeMenu);

    // ESC tuşu ile kapatma
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>