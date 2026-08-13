<?php
/*
    admin/kaydet.php
    ===================================================================
    MERKEZİ KAYIT MOTORU — tüm admin sayfaları (tekil + çoklu) Kaydet
    butonuna basınca buraya POST atar. Sayfa bazlı ayrı kod YAZILMAZ;
    hangi sayfanın tekil hangi sayfanın çoklu olduğunu $cokluKayitSayfalari
    listesinden kendisi öğrenir.

    Yeni bir ÇOKLU sayfa eklediğinde SADECE $cokluKayitSayfalari
    dizisine adını eklemen yeterli. TEKİL sayfalar için hiçbir şey
    eklemene gerek yok — listede olmayan her sayfa otomatik tekil kabul
    edilir.
    ===================================================================
*/
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit;
}

// ------------------------------------------------------------------
// Güvenlik: $page sadece dosya adı karakterleri içerebilir VE
// admin/{page}.php gerçekten var olmalı. Böylece rastgele bir isimle
// sunucuya keyfi json dosyası yazılması engellenir.
// ------------------------------------------------------------------
$page = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['page'] ?? '');
$adminDosyaYolu = __DIR__ . '/' . $page . '.php';

if ($page === '' || !file_exists($adminDosyaYolu)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz sayfa adı.']);
    exit;
}

$pageContent = $_POST['page_content'] ?? '{}';
$veriDizisi  = json_decode($pageContent, true);
if (!is_array($veriDizisi)) {
    $veriDizisi = ['page' => []];
}

$jsonYolu = __DIR__ . '/../data/' . $page . '.json';

// ==================================================================
// ÇOKLU KAYIT SİSTEMİ — tek dosyada dizi, id ile eşleştirme
// ==================================================================
// Buraya sadece SAYFA ADINI eklemen yeterli, başka hiçbir kod değişmez.
$cokluKayitSayfalari = ['okuma', 'soru', 'deney', 'yonetim'];

if (in_array($page, $cokluKayitSayfalari, true)) {
    $edit_id = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['edit_id'] ?? '');

    $tumKayitlar = [];
    if (file_exists($jsonYolu)) {
        $tumKayitlar = json_decode(file_get_contents($jsonYolu), true);
        if (!is_array($tumKayitlar)) $tumKayitlar = [];
    }

    // Formdan gelen tüm meta_* alanlarını otomatik yakala
    // (meta_baslik, meta_konu, meta_gerekce, meta_yazar, meta_sinif, ...)
    $meta = [];
    foreach ($_POST as $key => $val) {
        if (strpos($key, 'meta_') === 0) {
            $metaKey = substr($key, 5);
            $meta[$metaKey] = is_string($val) ? trim($val) : $val;
        }
    }
    $meta['tarih'] = date('d.m.Y H:i:s');

    $yeniKayit = [
        'id'   => $edit_id,
        'meta' => $meta,
        'page' => $veriDizisi['page'] ?? [],
    ];

    $bulundu = false;
    foreach ($tumKayitlar as &$item) {
        if (isset($item['id']) && $item['id'] === $edit_id) {
            // İlk oluşturma tarihini koru, sadece meta'yı güncelle
            $yeniKayit['meta']['tarih'] = $item['meta']['tarih'] ?? $meta['tarih'];
            $item = $yeniKayit;
            $bulundu = true;
            break;
        }
    }
    unset($item);

    if (!$bulundu) {
        $yeniKayit['id'] = $page . '_' . time();
        $tumKayitlar[] = $yeniKayit;
        $edit_id = $yeniKayit['id'];
    }

    $jsonHam = json_encode($tumKayitlar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (file_put_contents($jsonYolu, $jsonHam) === false) {
        echo json_encode(['success' => false, 'message' => 'Sunucu klasöre yazamadı. (CHMOD 755/777 kontrolü yapın)']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Kayıt başarıyla kaydedildi.', 'new_id' => $edit_id]);
    exit;
}

// ==================================================================
// TEKİL KAYIT SİSTEMİ
// ==================================================================
 $pageContent = $_POST['page_content'] ?? '{}';
 $veriDizisi  = json_decode($pageContent, true);
if (!is_array($veriDizisi)) {
    // Eğer veri dizisi değilse, eski tip {icerik: "..."} olabilir, onu da page'e çevir
    if (isset($veriDizisi['icerik'])) {
        $veriDizisi = ['page' => [['type' => 'paragraf', 'content' => ['html' => $veriDizisi['icerik']]]]];
    } else {
        $veriDizisi = ['page' => []];
    }
}

 $veriDizisi['son_guncelleme'] = date('d.m.Y H:i');

 $jsonHam = json_encode($veriDizisi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if (file_put_contents($jsonYolu, $jsonHam) === false) {
    echo json_encode(['success' => false, 'message' => 'Dosya yazma hatası! Klasör izinlerini kontrol edin.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Başarıyla kaydedildi.']);