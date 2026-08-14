<?php
/*
    admin/kaydet.php
    ===================================================================
    MERKEZİ KAYIT MOTORU + GITHUB API ENTEGRASYONU
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
// Güvenlik ve Sayfa Kontrolü
// ------------------------------------------------------------------
$page = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['page'] ?? '');
$adminDosyaYolu = __DIR__ . '/' . $page . '.php';

if ($page === '' || !file_exists($adminDosyaYolu)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz sayfa adı.']);
    exit;
}

// ------------------------------------------------------------------
// GITHUB API KAYIT FONKSİYONU
// ------------------------------------------------------------------
function githubaKaydet($dosyaAdi, $jsonHamIcerik) {
    $github_user  = "validebagfizik";
    $github_repo  = "validebagfizik";
    $branch       = "main";
    $github_token = getenv('GITHUB_TOKEN') ?: ($_ENV['GITHUB_TOKEN'] ?? '');
    
    // GitHub üzerindeki dosya yolu (Örn: data/okuma.json)
    $github_path  = "data/" . $dosyaAdi . ".json";

    // 1. Dosyanın mevcut SHA kodunu alıyoruz
    $ch = curl_init("https://api.github.com/repos/$github_user/$github_repo/contents/$github_path?ref=$branch");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "User-Agent: PHP-Admin-Panel",
        "Authorization: token $github_token"
    ]);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $file_sha = $response['sha'] ?? null;

    // 2. Yeni içeriği GitHub'a Gönderiyoruz
    $data = [
        "message" => "Admin Panel: $dosyaAdi.json güncellendi",
        "content" => base64_encode($jsonHamIcerik),
        "branch"  => $branch
    ];

    if ($file_sha) {
        $data["sha"] = $file_sha;
    }

    $ch = curl_init("https://api.github.com/repos/$github_user/$github_repo/contents/$github_path");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "User-Agent: PHP-Admin-Panel",
        "Authorization: token $github_token",
        "Content-Type: application/json"
    ]);

    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return isset($result['content']['name']);
}


$pageContent = $_POST['page_content'] ?? '{}';
$veriDizisi  = json_decode($pageContent, true);
if (!is_array($veriDizisi)) {
    $veriDizisi = ['page' => []];
}

$jsonYolu = __DIR__ . '/../data/' . $page . '.json';

// ==================================================================
// ÇOKLU KAYIT SİSTEMİ
// ==================================================================
$cokluKayitSayfalari = ['okuma', 'soru', 'deney', 'yonetim'];

if (in_array($page, $cokluKayitSayfalari, true)) {
    $edit_id = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['edit_id'] ?? '');

    $tumKayitlar = [];
    if (file_exists($jsonYolu)) {
        $tumKayitlar = json_decode(file_get_contents($jsonYolu), true);
        if (!is_array($tumKayitlar)) $tumKayitlar = [];
    }

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

    // Yerel sunucuya yaz (Varsa önbellek için)
    @file_put_contents($jsonYolu, $jsonHam);

    // GitHub API üzerinden Gerçek Kayıt
    if (!githubaKaydet($page, $jsonHam)) {
        echo json_encode(['success' => false, 'message' => 'GitHub API bağlantı hatası! Veri güncellenemedi.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Kayıt başarıyla GitHub ve Render sistemine gönderildi.', 'new_id' => $edit_id]);
    exit;
}

// ==================================================================
// TEKİL KAYIT SİSTEMİ
// ==================================================================
if (!is_array($veriDizisi)) {
    if (isset($veriDizisi['icerik'])) {
        $veriDizisi = ['page' => [['type' => 'paragraf', 'content' => ['html' => $veriDizisi['icerik']]]]];
    } else {
        $veriDizisi = ['page' => []];
    }
}

$veriDizisi['son_guncelleme'] = date('d.m.Y H:i');
$jsonHam = json_encode($veriDizisi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Yerel sunucuya yaz (Varsa önbellek için)
@file_put_contents($jsonYolu, $jsonHam);

// GitHub API üzerinden Gerçek Kayıt
if (!githubaKaydet($page, $jsonHam)) {
    echo json_encode(['success' => false, 'message' => 'GitHub API bağlantı hatası!']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Başarıyla kaydedildi. Render 30 sn içinde güncelleyecek.']);