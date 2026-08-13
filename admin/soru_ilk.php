<?php
/*
    admin/soru_ilk.php
    ===================================================================
    TEKİL sayfa. soru_ilk.php gibi başka bir tekil sayfa eklersen bu
    dosyanın BİREBİR aynısını kopyala, sadece $pageTitle ve
    $pageDescription satırlarını değiştir. Başka hiçbir şeye dokunma —
    sayfa adı dosya adından otomatik çıkarılıyor.
    ===================================================================
*/

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

// OTOMATİK — elle değiştirilmez:
 $pageName = basename(__FILE__, '.php'); // "soru_ilk"
 $dataFile = __DIR__ . '/../data/' . $pageName . '.json';

// MANUEL — sadece bu iki satır senin:
 $pageTitle       = "Soru Bankası Tanıtımı";
 $pageDescription = "Soru seçim ekranından önce kullanıcıya gösterilecek tanıtım metnini buradan düzenleyebilirsiniz.";

// Kayıtlı içeriği oku (Block JSON sistemine göre)
 $editorDataJson = '[]';
 $sonGuncelleme = null;

if (file_exists($dataFile)) {
    $veri = json_decode(file_get_contents($dataFile), true) ?: [];
    $sonGuncelleme = $veri['son_guncelleme'] ?? null;
    
    // Yeni Blok Sistemi (page dizisi varsa)
    if (isset($veri['page']) && is_array($veri['page'])) {
        $editorDataJson = json_encode($veri['page'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } 
    // Eski HTML string sistemi (geriye dönük uyumluluk için bloğa sar)
    elseif (isset($veri['icerik']) && !empty($veri['icerik'])) {
        $eskiHtml = $veri['icerik'];
        $cevrilmisBloklar = [['type' => 'paragraf', 'content' => ['html' => $eskiHtml]]];
        $editorDataJson = json_encode($cevrilmisBloklar, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

// Eğer dosya boşsa (ilk kez açılıyorsa) varsayılan karşılama metnini block olarak gönder
if ($editorDataJson === '[]') {
    $varsayilanIcerik = '<h2>Başlığınızı buraya yazın</h2><p>İçeriğe başlamak için tıklayın...</p>';
    $editorDataJson = json_encode([['type' => 'paragraf', 'content' => ['html' => $varsayilanIcerik]]], JSON_UNESCAPED_UNICODE);
}

// OTOMATİK — aktif tema (whitelist kontrollü, admin paneli de 4 temalı sisteme bağlı)
 $gecerliTemalar = ['warm-amber', 'deep-space', 'cyber-ocean', 'forest-matrix'];
 $okunanTema     = file_exists(__DIR__ . '/../aktif_tema.txt') ? trim(file_get_contents(__DIR__ . '/../aktif_tema.txt')) : 'warm-amber';
 $aktifTema      = in_array($okunanTema, $gecerliTemalar, true) ? $okunanTema : 'warm-amber';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli — <?php echo htmlspecialchars($pageTitle); ?></title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Sıra önemli: themes.css -> editor.css -> admin.css -->
    <link rel="stylesheet" href="../assets/css/themes.css">
    <link rel="stylesheet" href="../assets/css/editor.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="theme-<?php echo htmlspecialchars($aktifTema, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="admin-layout-wrapper">

        <div class="admin-sidebar-zone">
            <?php include 'includes/sidebar.php'; ?>
        </div>

        <div class="admin-main-content">

            <div class="admin-page-header">
                <div>
                    <h1><i class="fa-solid fa-circle-info"></i> <?php echo htmlspecialchars($pageTitle); ?></h1>
                    <p><?php echo htmlspecialchars($pageDescription); ?></p>
                </div>
                <?php if ($sonGuncelleme): ?>
                    <span class="admin-son-guncelleme">Son güncelleme: <?php echo htmlspecialchars($sonGuncelleme); ?></span>
                <?php endif; ?>
            </div>

            <div class="editor-container">
                <?php include 'includes/toolbar.php'; ?>

                <!-- VERİYİ JS'E GÜVENLİ ŞEKİLDE AKTARIYORUZ -->
                <script>window.EDITOR_DATA = <?php echo $editorDataJson; ?>;</script>

                <!-- EDİTÖRÜN İÇİ BOŞ BAŞLIYOR, JS VERİYİ BURAYA DOLDURACAK -->
                <div id="word-canvas" class="editor-content" contenteditable="true"></div>

                <div class="editor-info-bar">
                    <span>Mod: <strong id="editor-mode">Yazım</strong></span>
                    <span>Son hücrede <strong>Tab</strong> tuşu yeni satır ekler.</span>
                </div>
            </div>

            <div class="admin-action-bar">
                <button type="button" id="btn-preview" class="btn-onizleme">
                    <i class="fa-solid fa-eye"></i> Öngörünüm
                </button>
                <button type="button" id="btn-save-page" class="btn-kaydet-ana">
                    <i class="fa-solid fa-floppy-disk"></i> Değişiklikleri Kaydet
                </button>
            </div>

        </div>
    </div>

    <!-- OTOMATİK: sayfa adı dosya adından geliyor -->
    <script>const CURRENT_PAGE = '<?php echo htmlspecialchars($pageName, ENT_QUOTES); ?>';</script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/kayit-tekil.js"></script>

</body>
</html>