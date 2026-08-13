<?php
/*
    admin/includes/meta_alan_render.php
    ===================================================================
    alan_tanimlari.php'deki bir alan tanımını meta çubuğuna basar.
    "name" özniteliği HER ZAMAN buradan geldiği için unutulması artık
    mümkün değil — daha önce yaşadığımız "kayıt sıfırlanıyor" hatası
    bu dosya sayesinde bir daha yaşanamaz.
    ===================================================================
*/
function metaAlanRenderla($alan, $meta) {
    $ad = 'meta_' . $alan['key'];
    $deger = $meta[$alan['key']] ?? ($alan['default'] ?? '');
    ?>
    <div class="meta-alan">
        <label><?php echo htmlspecialchars($alan['label']); ?></label>
        <?php if ($alan['type'] === 'select'): ?>
            <select id="<?php echo $ad; ?>" name="<?php echo $ad; ?>" class="meta-input">
                <?php foreach ($alan['options'] as $opt): ?>
                    <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo ($opt === $deger) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($opt); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php elseif ($alan['type'] === 'text'): ?>
            <input type="text" id="<?php echo $ad; ?>" name="<?php echo $ad; ?>" class="meta-input"
                   value="<?php echo htmlspecialchars($deger); ?>"
                   placeholder="<?php echo htmlspecialchars($alan['placeholder'] ?? ''); ?>">
        <?php elseif ($alan['type'] === 'kapak'): ?>
            <input type="hidden" id="<?php echo $ad; ?>" name="<?php echo $ad; ?>" value="<?php echo htmlspecialchars($deger); ?>">
            <button type="button" class="kapak-sec-btn" id="kapakSecBtn" onclick="kapakSec()">
                <img id="kapakOnizleme" src="<?php echo $deger ? '../' . htmlspecialchars($deger) : ''; ?>"
                     style="<?php echo empty($deger) ? 'display:none;' : ''; ?>" alt="">
                <span id="kapakSecMetni" style="<?php echo !empty($deger) ? 'display:none;' : ''; ?>">
                    <i class="fa-solid fa-image"></i> Galeri
                </span>
            </button>
        <?php endif; ?>
    </div>
    <?php
}
