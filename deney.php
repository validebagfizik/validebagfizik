<?php
/*
    deney.php (SİTE KÖKÜNE)
    Off-canvas (Açılır Kapanır) Sınıf ve Deney Seçim Menüsü
*/
require_once __DIR__ . '/includes/blok_render.php';

 $jsonYolu = __DIR__ . '/data/deney.json';
 $deneyKayitlari = [];
if (file_exists($jsonYolu)) {
    $deneyKayitlari = json_decode(file_get_contents($jsonYolu), true) ?: [];
}

// URL'den ?id=... gelmişse onu seç, yoksa null (boş ekran göster)
 $secilenId = $_GET['id'] ?? '';
 $secilenKayit = null;

if ($secilenId) {
    foreach ($deneyKayitlari as $item) {
        if (($item['id'] ?? null) === $secilenId) {
            $secilenKayit = $item;
            break;
        }
    }
}

// JS'e aktarılacak veriyi ve sınıfları hazırla
 $jsDeneyVeri = [];
 $siniflar = [];
foreach ($deneyKayitlari as $item) {
    $sinif = $item['meta']['sinif'] ?? 'Diğer';
    if (!in_array($sinif, $siniflar, true)) {
        $siniflar[] = $sinif;
    }
    
    $itemHtml = renderBloklar($item['page'] ?? []);
    $itemHtml = str_replace('../uploads/', 'uploads/', $itemHtml);
    $itemHtml = str_replace('../assets/', 'assets/', $itemHtml);
    
    $jsDeneyVeri[$item['id']] = [
        'meta' => $item['meta'] ?? [],
        'html' => $itemHtml
    ];
}
sort($siniflar);

// HEADER'A GÖNDERİLECEK DEĞİŞKENLER
 $pageTitle = "Deneyler - Fizik Portalı";
 $currentPage = "deney"; 
require 'includes/header.php';
?>

<div class="soru-layout deneyler-layout">

    <!-- HAMBURGER BUTON -->
    <button type="button" id="deney-menu-toggle" class="kategori-toggle-btn">
        <i class="fa fa-bars"></i> Sınıf ve Deney Seç
    </button>

    <!-- KARARTMA -->
    <div id="deney-menu-overlay" class="okuma-menu-overlay"></div>

      <!-- SOL MENÜ (Sınıflar Kategorilere Ayrılmış Halde) -->
    <aside class="soru-sidebar" id="deney-sidebar">
        <div class="sidebar-card">
            <div class="sidebar-header">
                <i class="fa fa-flask"></i> Deneyler
                <!-- X butonu kaldırıldı -->
            </div>
            <div class="sidebar-content" id="deney-menu">
                <?php if (empty($siniflar)): ?>
                    <p class="empty-state">Henüz deney eklenmemiş.</p>
                <?php else: ?>
                    <?php foreach ($siniflar as $sinif): ?>
                        <details class="kategori-group">
                            <summary class="kategori-summary">
                                <i class="fa fa-folder-open"></i> <?php echo htmlspecialchars($sinif); ?>
                            </summary>
                            <div class="kategori-items">
                                <?php foreach ($deneyKayitlari as $item): 
                                    $m = $item['meta'] ?? [];
                                    if (($m['sinif'] ?? 'Diğer') === $sinif):
                                        $isActive = ($secilenId === $item['id']) ? 'active' : '';
                                ?>
                                    <button type="button" class="meb-baslik-btn <?php echo $isActive; ?>" data-id="<?php echo htmlspecialchars($item['id']); ?>" style="display: flex; justify-content: space-between; align-items: center;">
                                        <span><?php echo htmlspecialchars($m['baslik'] ?? 'Başlıksız'); ?></span>
                                        <i class="fa fa-arrow-right" style="font-size: 12px; opacity: 0.5;"></i> <!-- Ok işareti eklendi -->
                                    </button>
                                <?php endif; endforeach; ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <!-- Kapatma Yönlendirmesi -->
                <div class="sidebar-kapat-yazi">
                    Menüyü kapatmak için boş bir alana tıklayın.
                </div>
            </div>
        </div>
    </aside>

    <!-- SAĞ İÇERİK -->
    <main class="soru-main">
        <div id="deney-icerik" class="reading-container site-block">
            
            <?php if ($secilenKayit): ?>
                <div class="reading-meta">
                    <div class="detay-geri">
                        <a href="deney.php"><i class="fa fa-arrow-left"></i> Menüye Dön</a>
                    </div>
                    <?php if (!empty($secilenKayit['meta']['sinif'])): ?>
                        <span class="badge"><?php echo htmlspecialchars($secilenKayit['meta']['sinif']); ?></span>
                    <?php endif; ?>
                    <h1 class="reading-title"><?php echo htmlspecialchars($secilenKayit['meta']['baslik'] ?? 'Başlıksız'); ?></h1>
                </div>
                <div class="reading-content">
                    <?php echo $jsDeneyVeri[$secilenId]['html'] ?? '<p>İçerik bulunamadı.</p>'; ?>
                </div>
            <?php else: ?>
                <div class="deney-bos-ekran">
                    <i class="fa fa-flask"></i>
                    <h2>Deney Seçilmedi</h2>
                    <p>Lütfen sol taraftaki <strong>"Sınıf ve Deney Seç"</strong> menüsüne tıklayarak incelemek istediğiniz deneyi seçiniz.</p>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<script>
    window.DENEY_VERI = <?php echo json_encode($jsDeneyVeri, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>

<script>
(function () {
    var toggleBtn = document.getElementById('deney-menu-toggle');
    var closeBtn  = document.getElementById('deney-menu-close');
    var sidebar   = document.getElementById('deney-sidebar');
    var overlay   = document.getElementById('deney-menu-overlay');
    var menu      = document.getElementById('deney-menu');
    var icerikAlani = document.getElementById('deney-icerik');

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
    if (closeBtn)  closeBtn.addEventListener('click', closeMenu);
    if (overlay)   overlay.addEventListener('click', closeMenu);

    if (menu) {
        menu.addEventListener('click', function (e) {
            var item = e.target.closest('.meb-baslik-btn');
            if (!item) return;

            var id = item.getAttribute('data-id');
            
            if (window.DENEY_VERI && window.DENEY_VERI[id]) {
                var tumItemlar = menu.querySelectorAll('.meb-baslik-btn');
                tumItemlar.forEach(function(el) { el.classList.remove('active'); });
                item.classList.add('active');

                var veri = window.DENEY_VERI[id];
                var meta = veri.meta || {};
                
                var html = '<div class="reading-meta">';
                html += '<div class="detay-geri"><a href="deney.php"><i class="fa fa-arrow-left"></i> Menüye Dön</a></div>';
                if(meta.sinif) html += '<span class="badge">' + meta.sinif + '</span>';
                html += '<h1 class="reading-title">' + (meta.baslik || 'Başlıksız') + '</h1>';
                html += '</div>';
                html += '<div class="reading-content">';
                html += veri.html || '<p>İçerik bulunamadı.</p>';
                html += '</div>';

                icerikAlani.innerHTML = html;
                
                closeMenu();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });
})();
</script>

<?php require 'includes/footer.php'; ?>