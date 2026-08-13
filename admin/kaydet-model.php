<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['ok' => false, 'mesaj' => 'Sadece POST']);
  exit;
}

$girdi = json_decode(file_get_contents('php://input'), true);
if (!$girdi || empty($girdi['grup'])) {
  echo json_encode(['ok' => false, 'mesaj' => 'Geçersiz veri']);
  exit;
}

$grup = $girdi['grup'];
$izinliAlanlar = [
  'deney'   => ['sinif', 'konu', 'baslik'],
  'okuma'   => ['konu', 'baslik', 'yazar', 'kapak'],
  'soru'    => ['sinif', 'konu', 'baslik', 'dogru'],
  'yonetim' => ['konu', 'baslik'],
];

if (!isset($izinliAlanlar[$grup])) {
  echo json_encode(['ok' => false, 'mesaj' => 'Geçersiz grup']);
  exit;
}

// Sadece izin verilen alanları al
$meta = [];
foreach ($izinliAlanlar[$grup] as $key) {
  $meta[$key] = isset($girdi[$key]) ? trim((string)$girdi[$key]) : '';
}

$yeniKayit = [
  'id'        => uniqid('k_', true),
  'meta'      => $meta,
  'olusturma' => date('Y-m-d H:i:s'),
];

$jsonYolu = __DIR__ . '/../data/' . $grup . '.json';
$dataDizin = dirname($jsonYolu);
if (!is_dir($dataDizin)) mkdir($dataDizin, 0755, true);

$kayitlar = [];
if (file_exists($jsonYolu)) {
  $kayitlar = json_decode(file_get_contents($jsonYolu), true) ?: [];
}
$kayitlar[] = $yeniKayit;

$yazildi = file_put_contents(
  $jsonYolu,
  json_encode($kayitlar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

echo json_encode(['ok' => $yazildi !== false, 'id' => $yeniKayit['id']]);