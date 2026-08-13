<?php
/*
    sinav_ol.php  (SİTE KÖKÜ)
    ===================================================================
    Kurulum: Sınıf (tekli) -> Konu (çoklu) -> Soru sayısı -> Başlat.
    Sınav modunda cevaplar ANINDA gösterilmez, sonunda toplu skor +
    doğru/yanlış özet listesi çıkar. Motor: assets/js/sinav-motoru.js
    ===================================================================
*/
 $pageTitle = "Sınav Ol — Fizik Portalı";
require 'includes/header.php';

 $soruJson = __DIR__ . '/data/soru.json';
 $tumSorular = [];
if (file_exists($soruJson)) {
    $tumSorular = json_decode(file_get_contents($soruJson), true) ?: [];
}

 $jsSorular = array_map(function ($s) {
    return [
        'id'   => $s['id'] ?? '',
        'meta' => $s['meta'] ?? [],
        'page' => $s['page'] ?? [],
    ];
}, array_values($tumSorular));

// RESİM YOLU DÜZELTMESİ: soru.php'deki mantıkla aynı
 $jsSorularJson = json_encode($jsSorular, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
 $jsSorularJson = str_replace(['../uploads/', './uploads/'], 'uploads/', $jsSorularJson);
 $jsSorularJson = str_replace(['../assets/', './assets/'], 'assets/', $jsSorularJson);
?>

<!-- CSS linkleri kaldırıldı, style.css hepsini kapsıyor -->

<div class="sinav-sayfa">

    <div id="sinav-kurulum" class="sinav-kurulum">
        <h1><i class="fa-solid fa-graduation-cap"></i> Sınav Ol</h1>
        <p class="sinav-aciklama">Kendini test et — sınıfını ve konularını seç, sınava başla.</p>

        <div class="kurulum-adim">
            <label class="baslik-label">Hangi sınıf seviyesinden sınava girmek istiyorsunuz?</label>
            <div class="secim-grup" id="sinif-secim-grup"></div>
        </div>

        <div class="kurulum-adim">
            <label class="baslik-label">Lütfen sınav olmak istediğiniz konuları seçin:</label>
            <div class="secim-grup" id="konu-secim-grup"></div>
        </div>

        <div class="kurulum-adim">
            <label class="baslik-label">Kaç soruluk sınav istiyorsunuz?</label>
            <div class="soru-sayisi-satiri">
                <input type="number" id="soru-sayisi-input" value="10" min="1">
                <span id="soru-sayisi-bilgi" class="soru-sayisi-bilgi"></span>
            </div>
        </div>

        <button type="button" id="sinav-basla-btn" class="btn-kaydet-ana">
            <i class="fa-solid fa-play"></i> Sınavı Başlat
        </button>
    </div>

    <div id="sinav-alani" class="sinav-alani" style="display:none;">
        <div class="sinav-ust-cubuk">
            <span id="sinav-ilerleme"></span>
        </div>

        <div id="sinav-govde" class="sinav-govde editor-content"></div>

        <div id="sinav-secenekler" class="sinav-secenekler"></div>

        <div class="sinav-alt-cubuk">
            <button type="button" id="sinav-geri-btn" class="quiz-yardim-btn" style="display:none;">
                <i class="fa-solid fa-arrow-left"></i> Geri
            </button>
            <div style="flex:1"></div>
            <button type="button" id="sinav-ileri-btn" class="btn-kaydet-ana" style="display:none;">
                İleri <i class="fa-solid fa-arrow-right"></i>
            </button>
            <button type="button" id="sinav-bitir-btn" class="btn-kaydet-ana" style="display:none;">
                <i class="fa-solid fa-flag-checkered"></i> Sınavı Bitir
            </button>
        </div>
    </div>

    <div id="sinav-sonuc" style="display:none;"></div>

</div>

<script>window.SORU_KAYITLARI = <?php echo $jsSorularJson; ?>;</script>
<script src="assets/js/sinav-motoru.js"></script>

<?php require 'includes/footer.php'; ?>