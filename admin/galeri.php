<?php
/*
    admin/galeri.php — GITHUB API DESTEKLİ RESİM GALERİSİ
    ===================================================================
    Yüklenen resimler işlendikten sonra doğrudan GitHub API ile depoya
    yüklenir. Galeri listesi GitHub API üzerinden çekilir.
    ===================================================================
*/
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$klasorluHedefler = ['soru', 'deney', 'okuma'];
$izinliHedefler   = ['soru', 'deney', 'okuma', 'egitim', 'anasayfa', 'yonetim', 'genel'];

$hedef = $_GET['hedef'] ?? 'genel';
if (!in_array($hedef, $izinliHedefler, true)) {
    $hedef = 'genel';
}
$tip = ($_GET['tip'] ?? 'icerik') === 'kapak' ? 'kapak' : 'icerik';

// GitHub Bilgileri
$github_user  = "validebagfizik";
$github_repo  = "validebagfizik";
$branch       = "main";
$github_token = getenv('GITHUB_TOKEN') ?: ($_ENV['GITHUB_TOKEN'] ?? '');

$githubKlasorYolu = in_array($hedef, $klasorluHedefler, true)
    ? 'uploads/' . $hedef
    : 'uploads';

$mesaj = '';

// ==========================================================================
// GITHUB'A RESİM YÜKLEME FONKSİYONU
// ==========================================================================
function githubaResimYukle($lokalYol, $githubKlasor, $dosyaAdi) {
    global $github_user, $github_repo, $branch, $github_token;

    if (empty($github_token)) return false;

    $github_path = trim($githubKlasor, '/') . '/' . $dosyaAdi;
    $resimIcerik = file_get_contents($lokalYol);

    $data = [
        "message" => "Galeri: Yeni resim eklendi ($github_path)",
        "content" => base64_encode($resimIcerik),
        "branch"  => $branch
    ];

    $ch = curl_init("https://api.github.com/repos/$github_user/$github_repo/contents/$github_path");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "User-Agent: PHP-Admin-Gallery",
        "Authorization: token $github_token",
        "Content-Type: application/json"
    ]);

    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return isset($result['content']['name']);
}

// ==========================================================================
// SABİT YÜKSEKLİKLİ VE ORANTISIZLIK KONTROLLÜ KÜÇÜLTME
// ==========================================================================
function resimKucult($kaynak, $hedefYol, $sabitYukseklik = 800, $kalite = 75) {
    $boyutlar = getimagesize($kaynak);
    if (!$boyutlar) return 'hata';

    $en = $boyutlar[0];
    $boy = $boyutlar[1];
    $tip = $boyutlar['mime'];

    if ($boy <= $sabitYukseklik) {
        if ($tip === 'image/jpeg') {
            $resim = imagecreatefromjpeg($kaynak);
            imagejpeg($resim, $hedefYol, $kalite);
            imagedestroy($resim);
            return 'basarili';
        }
        return copy($kaynak, $hedefYol) ? 'basarili' : 'hata';
    }

    $yeniBoy = $sabitYukseklik;
    $yeniEn  = $en * ($sabitYukseklik / $boy);

    $kabulMinGenislik = 250;
    $kabulMaxGenislik = 2500;
    if ($yeniEn < $kabulMinGenislik || $yeniEn > $kabulMaxGenislik) {
        return 'orantisiz';
    }

    $yeniResim = imagecreatetruecolor((int)$yeniEn, (int)$yeniBoy);
    imagealphablending($yeniResim, false);
    imagesavealpha($yeniResim, true);

    switch ($tip) {
        case 'image/jpeg': $kaynakResim = imagecreatefromjpeg($kaynak); break;
        case 'image/png':  $kaynakResim = imagecreatefrompng($kaynak); break;
        case 'image/gif':  $kaynakResim = imagecreatefromgif($kaynak); break;
        case 'image/webp': $kaynakResim = imagewebpfrompng($kaynak); break; // webp desteği
        default: return 'hata';
    }

    imagecopyresampled($yeniResim, $kaynakResim, 0, 0, 0, 0, (int)$yeniEn, (int)$yeniBoy, $en, $boy);

    $basarili = false;
    switch ($tip) {
        case 'image/jpeg': $basarili = imagejpeg($yeniResim, $hedefYol, $kalite); break;
        case 'image/png':  $basarili = imagepng($yeniResim, $hedefYol, 8); break;
        case 'image/gif':  $basarili = imagegif($yeniResim, $hedefYol); break;
        case 'image/webp': $basarili = imagewebp($yeniResim, $hedefYol, $kalite); break;
    }

    imagedestroy($kaynakResim);
    imagedestroy($yeniResim);

    return $basarili ? 'basarili' : 'hata';
}

// ==========================================================================
// YÜKLEME İŞLEMİ
// ==========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resim'])) {
    $dosya = $_FILES['resim'];
    $izinliUzantilar = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $dosyaUzantisi = strtolower(pathinfo($dosya['name'], PATHINFO_EXTENSION));

    if (in_array($dosyaUzantisi, $izinliUzantilar, true)) {
        $yeniDosyaAdi = uniqid('img_') . '.' . $dosyaUzantisi;
        $geciciHedefYol = sys_get_temp_dir() . '/' . $yeniDosyaAdi;

        $sonuc = resimKucult($dosya['tmp_name'], $geciciHedefYol, 800, 75);

        if ($sonuc === 'basarili') {
            // Resmi GitHub'a yükle
            if (githubaResimYukle($geciciHedefYol, $githubKlasorYolu, $yeniDosyaAdi)) {
                $mesaj = '<div class="galeri-mesaj galeri-mesaj-basarili"><i class="fa-solid fa-circle-check"></i> Resim başarıyla GitHub depolamasına eklendi. Render yayına alıyor...</div>';
            } else {
                $mesaj = '<div class="galeri-mesaj galeri-mesaj-hata"><i class="fa-solid fa-circle-xmark"></i> Resim işlendi ancak GitHub\'a yüklenemedi. GITHUB_TOKEN kontrol edin.</div>';
            }
            @unlink($geciciHedefYol);
        } elseif ($sonuc === 'orantisiz') {
            $mesaj = '<div class="galeri-mesaj galeri-mesaj-uyari"><i class="fa-solid fa-triangle-exclamation"></i> Resmin oranı çok bozuk (800px yükseklikte 250px\'den dar veya 2500px\'den geniş oluyor). Lütfen kırpıp tekrar deneyin.</div>';
        } else {
            $mesaj = '<div class="galeri-mesaj galeri-mesaj-hata"><i class="fa-solid fa-circle-xmark"></i> Resim işlenirken sunucu hatası oluştu.</div>';
        }
    } else {
        $mesaj = '<div class="galeri-mesaj galeri-mesaj-hata"><i class="fa-solid fa-triangle-exclamation"></i> Sadece JPG, PNG, GIF ve WEBP yüklenebilir.</div>';
    }
}

// ==========================================================================
// MEVCUT RESİMLERİ GITHUB API ÜZERİNDEN OKU
// ==========================================================================
$mevcutResimler = [];
$ch = curl_init("https://api.github.com/repos/$github_user/$github_repo/contents/$githubKlasorYolu?ref=$branch");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "User-Agent: PHP-Admin-Gallery",
    "Authorization: token $github_token"
]);
$ghDosyalar = json_decode(curl_exec($ch), true);
curl_close($ch);

if (is_array($ghDosyalar)) {
    foreach ($ghDosyalar as $dosya) {
        if (isset($dosya['name'], $dosya['type']) && $dosya['type'] === 'file') {
            $uzanti = strtolower(pathinfo($dosya['name'], PATHINFO_EXTENSION));
            if (in_array($uzanti, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $mevcutResimler[] = $dosya['name'];
            }
        }
    }
}

$gecerliTemalar = ['warm-amber', 'deep-space', 'cyber-ocean', 'forest-matrix'];
$okunanTema     = file_exists(__DIR__ . '/../aktif_tema.txt') ? trim(file_get_contents(__DIR__ . '/../aktif_tema.txt')) : 'warm-amber';
$aktifTema      = in_array($okunanTema, $gecerliTemalar, true) ? $okunanTema : 'warm-amber';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Galeri — <?php echo htmlspecialchars(ucfirst($hedef)); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <link rel="stylesheet" href="../assets/css/galeri.css">
</head>
<body class="theme-<?php echo htmlspecialchars($aktifTema, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="galeri-wrapper">
        <div class="galeri-baslik">
            <h3><i class="fa-solid fa-images"></i> <?php echo htmlspecialchars(ucfirst($hedef)); ?> Klasörü Görselleri</h3>
            <span class="galeri-sayac"><?php echo count($mevcutResimler); ?> görsel</span>
        </div>

        <?php echo $mesaj; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="galeri-yukleme-alani">
            <div>
                <label>Bilgisayardan yeni görsel yükle</label>
                <small>Yükseklik otomatik 800px'e sabitlenir, genişlik orantılı ayarlanır.</small>
                
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
                    <label for="galeri-dosya" style="cursor: pointer; background: var(--glass); border: 1px solid var(--glass-border); color: var(--text-primary); padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.15s ease;">
                        <i class="fa-solid fa-upload"></i> Dosya Seç
                    </label>
                    
                    <input type="file" id="galeri-dosya" name="resim" accept="image/*" required style="display: none;" onchange="document.getElementById('galeri-dosya-adi').innerText = this.files[0] ? this.files[0].name : 'Dosya seçilmedi'">
                    
                    <span id="galeri-dosya-adi" style="font-size: 13px; color: var(--text-soft); font-style: italic;">Dosya seçilmedi</span>
                </div>
            </div>
            <button type="submit" class="btn-galeri-yukle"><i class="fa-solid fa-cloud-arrow-up"></i> Yükle</button>
        </form>

        <?php if (empty($mevcutResimler)): ?>
            <p class="galeri-bos">Bu klasörde henüz resim yok. Yukarıdan ilk resmi yükleyebilirsin.</p>
        <?php else: ?>
            <div class="galeri-grid">
                <?php foreach ($mevcutResimler as $resim):
                    $resimYolu  = ($hedef !== 'genel' && in_array($hedef, $klasorluHedefler, true))
                        ? 'uploads/' . $hedef . '/' . $resim
                        : 'uploads/' . $resim;
                ?>
                    <div class="galeri-kart" onclick="resmiSec('../<?php echo htmlspecialchars($resimYolu); ?>')">
                        <img src="../<?php echo htmlspecialchars($resimYolu); ?>" alt="">
                        <div class="galeri-kart-overlay"><i class="fa-solid fa-check"></i> Seç</div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function resmiSec(dosyaYolu) {
            if (!window.opener || window.opener.closed) {
                alert('Ana pencere bulunamadı!');
                return;
            }
            const tip = new URLSearchParams(window.location.search).get('tip');

            if (tip === 'kapak_popup' && typeof window.opener.popupKapakResmiAl === 'function') {
                window.opener.popupKapakResmiAl(dosyaYolu);
            } else if (tip === 'kapak' && typeof window.opener.kapakResmiAl === 'function') {
                window.opener.kapakResmiAl(dosyaYolu);
            } else if (typeof window.opener.galeridenResmiAl === 'function') {
                window.opener.galeridenResmiAl(dosyaYolu);
            } else {
                alert('Ana sayfada uygun bir alıcı fonksiyon bulunamadı.');
                return;
            }
            window.close();
        }
    </script>
</body>
</html>