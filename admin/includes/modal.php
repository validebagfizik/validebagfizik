<?php
/*
    admin/includes/modal.php
    ===================================================================
    "Yeni Kayıt Ekle" popup'ı. Hangi alanların çıkacağını artık ELLE
    YAZMIYORUZ — alan_tanimlari.php'den okuyup kendiliğinden üretiyor.
    Yeni bir çoklu sayfaya özel alan eklemek istersen SADECE
    alan_tanimlari.php'yi düzenle, bu dosyaya dokunma.
    ===================================================================
*/
$pageType = $pageType ?? 'genel';
$tumAlanlar = include __DIR__ . '/alan_tanimlari.php';
$sayfaAlanlari = $tumAlanlar[$pageType] ?? ['once' => [], 'sonra' => []];

// JS'e (kayit-coklu.js) bu sayfanın özel alan anahtarlarını bildiriyoruz —
// popupTamam() bu listeyi okuyup hangi meta_* alanlarını göndereceğini
// kendisi çıkarır, elle kod yazmaya gerek kalmaz.
$tumAnahtarlar = array_merge(
    array_column($sayfaAlanlari['once'], 'key'),
    array_column($sayfaAlanlari['sonra'], 'key')
);

function popupAlanRenderla($alan) {
    $inputId = 'popup-' . $alan['key'];
    ?>
    <div class="popup-alan">
        <label><?php echo htmlspecialchars($alan['label']); ?></label>
        <?php if ($alan['type'] === 'select'): ?>
            <select class="popup-select" id="<?php echo $inputId; ?>">
                <?php foreach ($alan['options'] as $opt): ?>
                    <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo ($opt === ($alan['default'] ?? '')) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($opt); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php elseif ($alan['type'] === 'text'): ?>
            <input type="text" class="popup-input" id="<?php echo $inputId; ?>"
                   placeholder="<?php echo htmlspecialchars($alan['placeholder'] ?? ''); ?>">
        <?php elseif ($alan['type'] === 'kapak'): ?>
            <input type="hidden" id="<?php echo $inputId; ?>">
            <button type="button" class="kapak-sec-btn" id="popupKapakSecBtn" onclick="popupKapakSec()">
                <img id="popupKapakOnizleme" src="" style="display:none;" alt="">
                <span id="popupKapakSecMetni"><i class="fa-solid fa-image"></i> Galeri</span>
            </button>
        <?php endif; ?>
    </div>
    <?php
}
?>
<script>window.OZEL_ALAN_ANAHTARLARI = <?php echo json_encode($tumAnahtarlar); ?>;</script>

<div class="popup-overlay" id="yeniBaglamModal">
    <div class="popup-box">
        <div class="popup-header">
            <h5><i class="fa-solid fa-file-circle-plus"></i> Yeni Bağlam Başlığı Oluştur</h5>
            <button type="button" class="popup-close" onclick="baglamKapat()" aria-label="Kapat">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="popup-body">
            <form id="baglam-baslik-formu" onsubmit="return false">

                <?php foreach ($sayfaAlanlari['once'] as $alan) popupAlanRenderla($alan); ?>

                <div class="popup-alan">
                    <label><?php echo ($pageType === 'yonetim') ? 'Kategori' : 'Ünite / Konu Adı'; ?></label>
                    <input type="text" class="popup-input" id="popup-konu"
                           placeholder="<?php echo ($pageType === 'yonetim') ? 'Örn: Disiplin Yönetmeliği' : 'Örn: Dinamik'; ?>" required>
                </div>

                <div class="popup-alan">
                    <label>Bağlam Başlığı / Adı</label>
                    <input type="text" class="popup-input" id="popup-baslik" placeholder="Başlığı girin" required>
                </div>

                <?php foreach ($sayfaAlanlari['sonra'] as $alan) popupAlanRenderla($alan); ?>

            </form>
        </div>

        <div class="popup-footer">
            <button type="button" class="popup-btn popup-btn-ghost" onclick="baglamKapat()">Vazgeç</button>
            <button type="button" class="popup-btn popup-btn-primary" onclick="popupTamam('<?php echo htmlspecialchars($pageType, ENT_QUOTES); ?>')">
                <i class="fa-solid fa-check"></i> Tamam
            </button>
        </div>
    </div>
</div>
