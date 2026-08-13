<?php
/*
    soru_istatistik_kaydet.php  (site kökü)
    ===================================================================
    Öğrenci bir soruyu cevapladığında JS buraya POST atar. Her sorunun
    kaç kişi tarafından doğru/yanlış cevaplandığını data/soru_istatistik.json
    içinde biriktirir ve güncel oranı geri döner — böylece "Bu soruyu
    %X kişi doğru yaptı" bilgisi anlık gösterilebilir.
    ===================================================================
*/
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit;
}

$soruId = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['soru_id'] ?? '');
$sonuc  = ($_POST['sonuc'] ?? '') === 'dogru' ? 'dogru' : 'yanlis';

if ($soruId === '') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz soru id.']);
    exit;
}

$dosyaYolu = __DIR__ . '/data/soru_istatistik.json';
$tumIstatistik = [];
if (file_exists($dosyaYolu)) {
    $tumIstatistik = json_decode(file_get_contents($dosyaYolu), true) ?: [];
}

if (!isset($tumIstatistik[$soruId]) || !is_array($tumIstatistik[$soruId])) {
    $tumIstatistik[$soruId] = ['dogru' => 0, 'yanlis' => 0];
}
$tumIstatistik[$soruId][$sonuc] = ($tumIstatistik[$soruId][$sonuc] ?? 0) + 1;

file_put_contents($dosyaYolu, json_encode($tumIstatistik, JSON_PRETTY_PRINT));

$kayit  = $tumIstatistik[$soruId];
$toplam = $kayit['dogru'] + $kayit['yanlis'];

echo json_encode([
    'success' => true,
    'dogru'   => $kayit['dogru'],
    'toplam'  => $toplam,
]);
