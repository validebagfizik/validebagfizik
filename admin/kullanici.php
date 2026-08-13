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
 $kullanicilar = [];
if (file_exists($usersPath)) {
    $kullanicilar = json_decode(file_get_contents($usersPath), true) ?: [];
}

// Rol Değiştirme, Engelleme veya Silme İşlemleri
if (isset($_GET['islem']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $islem = $_GET['islem'];
    $kendisiMi = ($id === ($_SESSION['kullanici_id'] ?? ''));
    
    foreach ($kullanicilar as &$k) {
        if ($k['id'] === $id) {
            if ($islem === 'rol') {
                $yeniRol = $_GET['rol'] ?? 'kullanici';
                if (($yeniRol === 'admin' || $yeniRol === 'kullanici') && !$kendisiMi) {
                    $k['rol'] = $yeniRol;
                }
            } elseif ($islem === 'engelle' && !$kendisiMi) {
                $k['durum'] = 'engelli';
            } elseif ($islem === 'aktif' && !$kendisiMi) {
                $k['durum'] = 'aktif';
            } elseif ($islem === 'sil' && !$kendisiMi) {
                $k = null; 
            }
            break;
        }
    }
    unset($k);
    
    $kullanicilar = array_filter($kullanicilar);
    file_put_contents($usersPath, json_encode(array_values($kullanicilar), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: kullanici.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcılar — Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <link rel="stylesheet" href="../assets/css/editor.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    
</head>
<body class="theme-<?php echo htmlspecialchars($aktif_tema); ?>">
    <div class="admin-layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main" style="flex-grow: 1;">
            <header class="admin-topbar">
                <h1><i class="fa fa-users"></i> Kullanıcı Yönetimi</h1>
                <div>Admin: <strong><?php echo htmlspecialchars($_SESSION['kullanici_ad'] ?? 'Yönetici'); ?></strong></div>
            </header>

            <div class="admin-content">
                <div class="admin-card">
                    <div class="card-header">
                        <h2><i class="fa fa-user-list"></i> Kayıtlı Kullanıcılar</h2>
                    </div>
                    <div class="card-body p-0" style="overflow-x: auto;">
                        <?php if (empty($kullanicilar)): ?>
                            <div class="admin-empty" style="padding: 40px; text-align: center;">
                                <i class="fa fa-user-slash" style="font-size: 2rem; opacity: 0.3; margin-bottom: 15px;"></i>
                                <p>Kayıtlı kullanıcı bulunamadı.</p>
                            </div>
                        <?php else: ?>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Ad Soyad</th>
                                        <th>E-posta</th>
                                        <th>Durum</th>
                                        <th>Rol</th>
                                        <th style="text-align: right;">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kullanicilar as $k): 
                                        $kendisiMi = ($k['id'] === ($_SESSION['kullanici_id'] ?? ''));
                                        $durum = $k['durum'] ?? 'aktif';
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($k['ad_soyad'] ?? 'İsimsiz'); ?></strong>
                                                <?php if ($kendisiMi): ?>
                                                    <span style="font-size: 0.75rem; color: var(--text-soft);">(Sen)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="email-cell"><?php echo htmlspecialchars($k['email'] ?? ''); ?></td>
                                            <td>
                                                <?php if ($durum === 'engelli'): ?>
                                                    <span class="durum-engelli"><i class="fa fa-ban"></i> Engelli</span>
                                                <?php else: ?>
                                                    <span class="durum-aktif"><i class="fa fa-check-circle"></i> Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (($k['rol'] ?? '') === 'admin'): ?>
                                                    <span class="status-badge status-approved">Admin</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-pending">Kullanıcı</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="user-actions">
                                                    <?php if (!$kendisiMi): ?>
                                                        <?php if (($k['rol'] ?? '') === 'admin'): ?>
                                                            <a href="kullanici.php?islem=rol&rol=kullanici&id=<?php echo htmlspecialchars($k['id']); ?>" class="btn-rol-kullanici">
                                                                <i class="fa fa-user"></i> Kullanıcı Yap
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="kullanici.php?islem=rol&rol=admin&id=<?php echo htmlspecialchars($k['id']); ?>" class="btn-rol-admin">
                                                                <i class="fa fa-user-shield"></i> Admin Yap
                                                            </a>
                                                        <?php endif; ?>

                                                        <?php if ($durum === 'engelli'): ?>
                                                            <a href="kullanici.php?islem=aktif&id=<?php echo htmlspecialchars($k['id']); ?>" class="btn-aktif">
                                                                <i class="fa fa-unlock"></i> Aktif Yap
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="kullanici.php?islem=engelle&id=<?php echo htmlspecialchars($k['id']); ?>" class="btn-engelle">
                                                                <i class="fa fa-ban"></i> Engelle
                                                            </a>
                                                        <?php endif; ?>

                                                        <a href="kullanici.php?islem=sil&id=<?php echo htmlspecialchars($k['id']); ?>" class="btn-sil" onclick="return confirm('Bu kullanıcıyı kalıcı olarak silmek istediğinize emin misiniz?')">
                                                            <i class="fa fa-trash"></i> Sil
                                                        </a>
                                                    <?php else: ?>
                                                        <span style="color: var(--text-soft); font-size: 0.85rem;">Kendi hesabınızda işlem yapılamaz.</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>