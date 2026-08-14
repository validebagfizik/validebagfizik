<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

// Tema Ayarı
$ayar_dosyasi = __DIR__ . '/../aktif_tema.txt';
if (!file_exists($ayar_dosyasi)) file_put_contents($ayar_dosyasi, 'warm-amber');
$aktif_tema = trim(file_get_contents($ayar_dosyasi));
$gecerli_temalar = ['warm-amber', 'deep-space', 'cyber-ocean', 'forest-matrix', 'midnight-cherry', 'arctic-frost'];
if (!in_array($aktif_tema, $gecerli_temalar)) $aktif_tema = 'warm-amber';

$usersPath = __DIR__ . '/../data/giris.json';
$mesaj = null;
$hata = null;

// GitHub Bilgileri
$github_user  = "validebagfizik";
$github_repo  = "validebagfizik";
$branch       = "main";
$github_token = getenv('GITHUB_TOKEN') ?: ($_ENV['GITHUB_TOKEN'] ?? '');

// GitHub API ile JSON Güncelleme Fonksiyonu
function githubaGirisJsonYukle($jsonHamIcerik) {
    global $github_user, $github_repo, $branch, $github_token;

    if (empty($github_token)) return false;

    $github_path = "data/giris.json";

    // 1. Mevcut SHA Kodunu Al
    $ch = curl_init("https://api.github.com/repos/$github_user/$github_repo/contents/$github_path?ref=$branch");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "User-Agent: PHP-Admin-Pass",
        "Authorization: token $github_token"
    ]);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $file_sha = $response['sha'] ?? null;

    // 2. Yeni İçeriği Gönder
    $data = [
        "message" => "Admin: Kullanıcı şifresi güncellendi",
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
        "User-Agent: PHP-Admin-Pass",
        "Authorization: token $github_token",
        "Content-Type: application/json"
    ]);

    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return isset($result['content']['name']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eskiSifre = $_POST['eski_sifre'] ?? '';
    $yeniSifre = $_POST['yeni_sifre'] ?? '';
    $yeniSifreTekrar = $_POST['yeni_sifre_tekrar'] ?? '';
    
    $kullanicilar = json_decode(file_get_contents($usersPath), true) ?: [];
    $bulundu = false;
    
    foreach ($kullanicilar as &$k) {
        if ($k['id'] === $_SESSION['kullanici_id']) {
            $bulundu = true;
            
            if (!password_verify($eskiSifre, $k['sifre_hash'] ?? '')) {
                $hata = 'Mevcut şifreniz yanlış.';
            } elseif (mb_strlen($yeniSifre) < 6) {
                $hata = 'Yeni şifre en az 6 karakter olmalıdır.';
            } elseif ($yeniSifre !== $yeniSifreTekrar) {
                $hata = 'Yeni şifreler eşleşmiyor.';
            } else {
                $k['sifre_hash'] = password_hash($yeniSifre, PASSWORD_DEFAULT);
                $yeniJsonHam = json_encode($kullanicilar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                
                // Yerel dosyayı güncelle
                @file_put_contents($usersPath, $yeniJsonHam);
                
                // GitHub üzerindeki giris.json dosyasını güncelle
                if (githubaGirisJsonYukle($yeniJsonHam)) {
                    $mesaj = 'Şifreniz başarıyla güncellendi ve GitHub deposuna kaydedildi.';
                } else {
                    $hata = 'Şifre yerelde değişti fakat GitHub bağlantı hatası oluştu.';
                }
            }
            break;
        }
    }
    unset($k);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Değiştir — Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/editor.css" rel="stylesheet"> 
    <link href="../assets/css/themes.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="theme-<?php echo htmlspecialchars($aktif_tema); ?>">
    <div class="admin-layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main" style="flex-grow: 1;">
            <header class="admin-topbar">
                <h1><i class="fa fa-key"></i> Şifre Değiştir</h1>
                <div>Admin: <strong><?php echo htmlspecialchars($_SESSION['kullanici_ad'] ?? 'Yönetici'); ?></strong></div>
            </header>

            <div class="admin-content">
                <div class="admin-card" style="max-width: 600px; margin: 0 auto;">
                    <div class="card-header">
                        <h2><i class="fa fa-lock"></i> Güvenlik Ayarları</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($hata): ?>
                            <div class="admin-alert admin-alert-error" style="margin-bottom: 20px;">
                                <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($hata); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($mesaj): ?>
                            <div class="admin-alert admin-alert-success" style="margin-bottom: 20px;">
                                <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($mesaj); ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="sifre.php">
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">Mevcut Şifre</label>
                                <input type="password" name="eski_sifre" class="form-control" required>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">Yeni Şifre</label>
                                <input type="password" name="yeni_sifre" class="form-control" required>
                                <small style="color: var(--text-soft); font-size: 0.8rem;">En az 6 karakter olmalıdır.</small>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 24px;">
                                <label class="form-label">Yeni Şifre (Tekrar)</label>
                                <input type="password" name="yeni_sifre_tekrar" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn-kaydet-ana" style="width: 100%;">
                                <i class="fa fa-save"></i> Şifreyi Güncelle
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>