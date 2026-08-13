<?php
/*
    admin/deneme.php  (GEÇİCİ AD — gerçek adıyla meb.php/soru.php/deney.php/okuma.php olacak)
    ===================================================================
    ÇOKLU KAYIT SAYFA ŞABLONU — GENEL, ALAN TANIMLARINA GÖRE OTOMATİK

    Kopyalayıp admin/{ISIM}.php yapınca SADECE şu 3 satırı değiştir:
      $pageTitle, $pageDescription, $yeniKayitButonMetni

    Bu sayfaya özel bir alan (mesela "Süre") gerekiyorsa bu dosyaya
    DOKUNMA — includes/alan_tanimlari.php'ye ekle, meta çubuğu ve
    liste tablosu kendiliğinden güncellenir.

    admin/kaydet.php içindeki $cokluKayitSayfalari dizisine yeni ismini
    eklemen gerekiyor (otomatik algılanamayan TEK manuel adım).
    ===================================================================
*/

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$pageName = basename(__FILE__, '.php');
$pageType = $pageName;
$jsonYolu = __DIR__ . '/../data/' . $pageName . '.json';

$pageTitle            = "Deneyler";
$pageDescription      = "Fizik deneylerini güncelleyin, kategorilere ayırın.";
$yeniKayitButonMetni  = "Deney Ekle";

$tumAlanlar = include __DIR__ . '/includes/alan_tanimlari.php';
$sayfaAlanlari = $tumAlanlar[$pageType] ?? ['once' => [], 'sonra' => []];
require_once __DIR__ . '/includes/meta_alan_render.php';

if (isset($_GET['sil'])) {
    $silId = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['sil']);
    if (file_exists($jsonYolu)) {
        $tumKayitlar = json_decode(file_get_contents($jsonYolu), true) ?: [];
        $yeniListe = array_filter($tumKayitlar, fn($i) => $i['id'] !== $silId);
        file_put_contents($jsonYolu, json_encode(array_values($yeniListe), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
    header("Location: {$pageName}.php");
    exit;
}

$editId = $_GET['duzenle'] ?? '';
$mod     = empty($editId) ? 'liste' : 'duzenle';
$meta    = [];
$editorDataJson = '[]';

if ($mod === 'duzenle' && file_exists($jsonYolu)) {
    $tumKayitlar = json_decode(file_get_contents($jsonYolu), true) ?: [];
    foreach ($tumKayitlar as $item) {
        if (($item['id'] ?? null) === $editId) {
            $meta = $item['meta'] ?? [];
            $sayfaVerisi = $item['page'] ?? [];

            if (is_array($sayfaVerisi)) {
                $editorDataJson = json_encode($sayfaVerisi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } elseif (!empty($sayfaVerisi)) {
                $cevrilmisBloklar = [['type' => 'paragraf', 'content' => ['html' => $sayfaVerisi]]];
                $editorDataJson = json_encode($cevrilmisBloklar, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            break;
        }
    }
}

if ($mod === 'duzenle' && $editorDataJson === '[]') {
    $varsayilanIcerik = '<h2>Başlığınızı buraya yazın</h2><p>İçeriğe başlamak için tıklayın...</p>';
    $editorDataJson = json_encode([['type' => 'paragraf', 'content' => ['html' => $varsayilanIcerik]]], JSON_UNESCAPED_UNICODE);
}

$kayitListesi = [];
if (file_exists($jsonYolu)) {
    $kayitListesi = json_decode(file_get_contents($jsonYolu), true) ?: [];
}
$kayitListesi = array_reverse($kayitListesi);

$listeSutunSayisi = 3;
foreach (array_merge($sayfaAlanlari['once'], $sayfaAlanlari['sonra']) as $alan) {
    if (!empty($alan['liste_baslik'])) $listeSutunSayisi++;
}

$gecerliTemalar = ['warm-amber', 'deep-space', 'cyber-ocean', 'forest-matrix'];
$okunanTema     = file_exists(__DIR__ . '/../aktif_tema.txt') ? trim(file_get_contents(__DIR__ . '/../aktif_tema.txt')) : 'warm-amber';
$aktifTema      = in_array($okunanTema, $gecerliTemalar, true) ? $okunanTema : 'warm-amber';

function listeHucresiBas($alan, $m) {
    $deger = $m[$alan['key']] ?? '-';
    $stil = $alan['liste_stil'] ?? 'normal';
    echo '<td>';
    if ($stil === 'kapak') {
        if (!empty($deger) && $deger !== '-') {
            echo '<img src="../' . htmlspecialchars($deger) . '" class="liste-kapak-thumb" alt="">';
        } else {
            echo '<div class="liste-kapak-thumb liste-kapak-bos"><i class="fa-solid fa-image"></i></div>';
        }
    } elseif ($stil === 'kalin') {
        echo '<strong>' . htmlspecialchars($deger) . '</strong>';
    } else {
        echo htmlspecialchars($deger);
    }
    echo '</td>';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli — <?php echo htmlspecialchars($pageTitle); ?></title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <h1><i class="fa-solid fa-flask"></i> <?php echo htmlspecialchars($pageTitle); ?></h1>
                    <p><?php echo htmlspecialchars($pageDescription); ?></p>
                </div>
                <?php if ($mod === 'liste'): ?>
                    <button type="button" class="btn-kaydet-ana" onclick="yeniBaglamAc()">
                        <i class="fa-solid fa-plus"></i> <?php echo htmlspecialchars($yeniKayitButonMetni); ?>
                    </button>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($pageName); ?>.php" class="btn-onizleme">
                        <i class="fa-solid fa-list"></i> Listeye Dön
                    </a>
                <?php endif; ?>
            </div>

            <div id="duzenleme-alani" style="display: <?php echo $mod === 'duzenle' ? 'block' : 'none'; ?>;">

                <div class="meta-cubugu">
                    <input type="hidden" id="edit_id" value="<?php echo htmlspecialchars($editId); ?>">

                    <?php foreach ($sayfaAlanlari['once'] as $alan) metaAlanRenderla($alan, $meta); ?>

                    <div class="meta-alan">
                        <label><?php echo ($pageType === 'yonetim') ? 'Kategori' : 'Ünite / Konu'; ?></label>
                        <input type="text" id="meta_konu" name="meta_konu" class="meta-input" value="<?php echo htmlspecialchars($meta['konu'] ?? ''); ?>" placeholder="<?php echo ($pageType === 'yonetim') ? 'Örn: Disiplin' : 'Örn: Dinamik'; ?>">
                    </div>

                    <div class="meta-alan meta-alan-genis">
                        <label>Başlık</label>
                        <input type="text" id="meta_baslik" name="meta_baslik" class="meta-input" value="<?php echo htmlspecialchars($meta['baslik'] ?? ''); ?>" placeholder="Bağlam başlığı">
                    </div>

                    <?php foreach ($sayfaAlanlari['sonra'] as $alan) metaAlanRenderla($alan, $meta); ?>
                </div>

                <div class="editor-container">
                    <?php include 'includes/toolbar.php'; ?>
                    <script>window.EDITOR_DATA = <?php echo $editorDataJson; ?>;</script>

                    <div id="word-canvas" class="editor-content" contenteditable="true"></div>
                    <div class="editor-info-bar">
                        <span>Mod: <strong id="editor-mode">Yazım</strong></span>
                        <span>Son hücrede <strong>Tab</strong> tuşu yeni satır ekler.</span>
                    </div>
                </div>

                <div class="admin-action-bar">
                    <a href="<?php echo htmlspecialchars($pageName); ?>.php" class="btn-onizleme">
                        <i class="fa-solid fa-xmark"></i> İptal
                    </a>
                    <button type="button" id="btn-preview" class="btn-onizleme">
                        <i class="fa-solid fa-eye"></i> Öngörünüm
                    </button>
                    <button type="button" id="btn-save-page" class="btn-kaydet-ana">
                        <i class="fa-solid fa-floppy-disk"></i> <?php echo !empty($editId) ? 'Güncelle' : 'Kaydet'; ?>
                    </button>
                </div>
            </div>

            <div id="liste-alani" style="display: <?php echo $mod === 'liste' ? 'block' : 'none'; ?>;">
                <div class="liste-kutusu">
                    <table class="liste-tablosu">
                        <thead>
                            <tr>
                                <?php foreach ($sayfaAlanlari['once'] as $alan): if (!empty($alan['liste_baslik'])): ?>
                                    <th style="width:<?php echo htmlspecialchars($alan['liste_genislik'] ?? 'auto'); ?>"><?php echo htmlspecialchars($alan['liste_baslik']); ?></th>
                                <?php endif; endforeach; ?>
                                <th style="width:160px">Konu</th>
                                <th>Başlık</th>
                                <?php foreach ($sayfaAlanlari['sonra'] as $alan): if (!empty($alan['liste_baslik'])): ?>
                                    <th style="width:<?php echo htmlspecialchars($alan['liste_genislik'] ?? 'auto'); ?>"><?php echo htmlspecialchars($alan['liste_baslik']); ?></th>
                                <?php endif; endforeach; ?>
                                <th style="width:120px" class="text-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($kayitListesi)): ?>
                                <tr>
                                    <td colspan="<?php echo $listeSutunSayisi; ?>" class="liste-bos">
                                        <i class="fa-solid fa-folder-open"></i><br>
                                        Henüz bir kayıt eklenmemiş.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($kayitListesi as $item):
                                    $m = $item['meta'] ?? [];
                                ?>
                                <tr>
                                    <?php foreach ($sayfaAlanlari['once'] as $alan): ?>
                                        <?php if (!empty($alan['liste_baslik'])) listeHucresiBas($alan, $m); ?>
                                    <?php endforeach; ?>
                                    
                                    <td><span class="konu-badge"><?php echo htmlspecialchars($m['konu'] ?? 'Genel'); ?></span></td>
                                    <td class="baslik-hucre"><?php echo htmlspecialchars($m['baslik'] ?? 'Başlıksız'); ?></td>
                                    
                                    <?php foreach ($sayfaAlanlari['sonra'] as $alan): ?>
                                        <?php if (!empty($alan['liste_baslik'])) listeHucresiBas($alan, $m); ?>
                                    <?php endforeach; ?>
                                    
                                    <td class="text-end">
                                        <a href="<?php echo htmlspecialchars($pageName); ?>.php?duzenle=<?php echo htmlspecialchars($item['id']); ?>" class="islem-btn" title="Düzenle"><i class="fa-solid fa-pen"></i></a>
                                        <a href="<?php echo htmlspecialchars($pageName); ?>.php?sil=<?php echo htmlspecialchars($item['id']); ?>" class="islem-btn islem-btn-sil" title="Sil" onclick="return confirm('Bu kaydı silmek istediğinize emin misiniz?')"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <?php include 'includes/modal.php'; ?>

    <script>
        const CURRENT_PAGE = '<?php echo htmlspecialchars($pageName, ENT_QUOTES); ?>';
    </script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/kayit-coklu.js"></script>

</body>
</html>
