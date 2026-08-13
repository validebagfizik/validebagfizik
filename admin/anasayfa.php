<?php
// admin/anasayfa.php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

ini_set('display_errors', 1);
error_reporting(E_ALL);

 $jsonPath = __DIR__ . '/../data/anasayfa.json';
 $dataDir  = dirname($jsonPath);
if (!file_exists($dataDir)) {
    if (!mkdir($dataDir, 0755, true) && !is_dir($dataDir)) {
        die('Klasör oluşturulamadı: ' . $dataDir);
    }
}

// OTOMATİK — aktif tema (Diğer sayfalarla aynı yapı)
 $gecerliTemalar = ['warm-amber', 'deep-space', 'cyber-ocean', 'forest-matrix'];
 $okunanTema     = file_exists(__DIR__ . '/../aktif_tema.txt') ? trim(file_get_contents(__DIR__ . '/../aktif_tema.txt')) : 'warm-amber';
 $aktifTema      = in_array($okunanTema, $gecerliTemalar, true) ? $okunanTema : 'warm-amber';

// Mevcut veriyi oku
 $home = [];
if (file_exists($jsonPath)) {
    $raw = file_get_contents($jsonPath);
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) $home = $decoded;
}

 $hero = $home['hero'] ?? [
    'title'    => 'evrenin yeni dili',
    'subtitle' => 'Oku. Hesapla. Keşfet. Tek portaldan',
    'cta_text' => 'Keşfetmeye başla',
    'cta_url'  => '#konular',
];
 $intro = $home['intro'] ?? [
    'title' => 'formülleri oku, denklemleri çöz, kavramları keşfet',
    'lead'  => 'Fizik Portal ile evrenin kurallarını daha kolay anla',
    'cards' => [],
];
 $features = $home['features'] ?? [];
 $faq      = $home['faq'] ?? [];
 $activeTab = $_GET['tab'] ?? 'hero';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anasayfa Yönetimi — Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
                    <h1><i class="fa-solid fa-house"></i> Anasayfa Yönetimi</h1>
                    <p>Admin: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Yönetici'); ?></strong></p>
                </div>
            </div>

            <div class="admin-content" style="padding: 20px;">
                <div id="alert-container"></div>

                <!-- HERO BİLGİ KARTI -->
                <div class="admin-card">
                    <div class="card-header">
                        <h2><i class="fa fa-info-circle"></i> Anasayfa Bölümleri</h2>
                    </div>
                    <div class="card-body">
                        <p style="color: var(--text-muted); margin: 0;">Aşağıdaki sekmelerden anasayfanın farklı bölümlerini düzenleyebilirsiniz. Değişiklikleri kaydettiğinizde <code>data/home.json</code> dosyası güncellenecektir.</p>
                    </div>
                </div>

                <!-- SEKMELER -->
                <div class="anasayfa-tabs">
                    <a href="?tab=hero" class="anasayfa-tab <?php echo $activeTab === 'hero' ? 'active' : ''; ?>">
                        <i class="fa fa-star"></i> Hero
                    </a>
                    <a href="?tab=intro" class="anasayfa-tab <?php echo $activeTab === 'intro' ? 'active' : ''; ?>">
                        <i class="fa fa-th-large"></i> Giriş
                    </a>
                    <a href="?tab=features" class="anasayfa-tab <?php echo $activeTab === 'features' ? 'active' : ''; ?>">
                        <i class="fa fa-bolt"></i> Özellikler
                    </a>
                    <a href="?tab=faq" class="anasayfa-tab <?php echo $activeTab === 'faq' ? 'active' : ''; ?>">
                        <i class="fa fa-question-circle"></i> SSS
                    </a>
                </div>

                <form id="adminForm">
                    <!-- HERO -->
                    <div class="admin-card tab-panel" data-tab="hero" <?php echo $activeTab !== 'hero' ? 'style="display:none;"' : ''; ?>>
                        <div class="card-header">
                            <h2><i class="fa fa-star"></i> Hero Bölümü</h2>
                        </div>
                        <div class="card-body">
                            <p class="anasayfa-section-desc">Anasayfanın en üstündeki karşılama alanı.</p>
                            
                            <div class="form-group">
                                <label class="form-label">Başlık <small style="color: var(--text-soft);">(HTML destekler)</small></label>
                                <input type="text" name="hero_title" class="form-control" 
                                       value="<?php echo htmlspecialchars($hero['title']); ?>" 
                                       placeholder="Örn: evrenin yeni dili">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Alt başlık</label>
                                <input type="text" name="hero_subtitle" class="form-control" 
                                       value="<?php echo htmlspecialchars($hero['subtitle']); ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">CTA buton metni</label>
                                <input type="text" name="hero_cta_text" class="form-control" 
                                       value="<?php echo htmlspecialchars($hero['cta_text']); ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">CTA buton bağlantısı</label>
                                <input type="text" name="hero_cta_url" class="form-control" 
                                       value="<?php echo htmlspecialchars($hero['cta_url']); ?>"
                                       placeholder="#konular veya /sayfa.php">
                            </div>
                        </div>
                    </div>

                    <!-- INTRO -->
                    <div class="admin-card tab-panel" data-tab="intro" <?php echo $activeTab !== 'intro' ? 'style="display:none;"' : ''; ?>>
                        <div class="card-header">
                            <h2><i class="fa fa-th-large"></i> Giriş Bölümü</h2>
                        </div>
                        <div class="card-body">
                            <p class="anasayfa-section-desc">Hero altındaki tanıtım alanı ve kartlar.</p>

                            <div class="form-group">
                                <label class="form-label">Bölüm başlığı</label>
                                <input type="text" name="intro_title" class="form-control" 
                                       value="<?php echo htmlspecialchars($intro['title']); ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Özet metin (lead)</label>
                                <textarea name="intro_lead" class="form-control" rows="3"><?php echo htmlspecialchars($intro['lead']); ?></textarea>
                            </div>

                            <h3 style="color: var(--text-primary); font-size: 1rem; margin: 24px 0 12px; padding-top: 20px; border-top: 1px solid var(--glass-border);">
                                <i class="fa fa-cards" style="color: var(--accent-cyan);"></i> Giriş Kartları
                            </h3>
                            <p class="anasayfa-section-desc">Üç sütunlu tanıtım kartları.</p>

                            <div class="repeatable-list" id="introCards">
                                <?php foreach ($intro['cards'] as $i => $card): ?>
                                    <div class="repeatable-item">
                                        <div class="item-header">
                                            <strong>Kart #<?php echo $i + 1; ?></strong>
                                            <button type="button" class="btn-remove" title="Kaldır">✕</button>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Simge</label>
                                            <input type="text" name="card_icon[]" class="form-control" 
                                                   value="<?php echo htmlspecialchars($card['icon']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Başlık</label>
                                            <input type="text" name="card_title[]" class="form-control" 
                                                   value="<?php echo htmlspecialchars($card['title']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Açıklama</label>
                                            <textarea name="card_text[]" class="form-control" rows="2"><?php echo htmlspecialchars($card['text']); ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn-add-item" data-target="introCards" data-template="tpl-card">
                                <i class="fa fa-plus"></i> Kart ekle
                            </button>
                        </div>
                    </div>

                    <!-- FEATURES -->
                    <div class="admin-card tab-panel" data-tab="features" <?php echo $activeTab !== 'features' ? 'style="display:none;"' : ''; ?>>
                        <div class="card-header">
                            <h2><i class="fa fa-bolt"></i> Özellikler / Konu Kartları</h2>
                        </div>
                        <div class="card-body">
                            <p class="anasayfa-section-desc">"Fizik yolculuğunda yanında" bölümündeki kayan kartlar.</p>

                            <div class="repeatable-list" id="featureList">
                                <?php foreach ($features as $i => $f): ?>
                                    <div class="repeatable-item">
                                        <div class="item-header">
                                            <strong>Özellik #<?php echo $i + 1; ?></strong>
                                            <button type="button" class="btn-remove" title="Kaldır">✕</button>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">ID (slug)</label>
                                            <input type="text" name="feature_id[]" class="form-control" 
                                                   value="<?php echo htmlspecialchars($f['id']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Etiket</label>
                                            <input type="text" name="feature_label[]" class="form-control" 
                                                   value="<?php echo htmlspecialchars($f['label']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Başlık</label>
                                            <input type="text" name="feature_title[]" class="form-control" 
                                                   value="<?php echo htmlspecialchars($f['title']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Açıklama</label>
                                            <textarea name="feature_text[]" class="form-control" rows="2"><?php echo htmlspecialchars($f['text']); ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn-add-item" data-target="featureList" data-template="tpl-feature">
                                <i class="fa fa-plus"></i> Özellik ekle
                            </button>
                        </div>
                    </div>

                    <!-- FAQ -->
                    <div class="admin-card tab-panel" data-tab="faq" <?php echo $activeTab !== 'faq' ? 'style="display:none;"' : ''; ?>>
                        <div class="card-header">
                            <h2><i class="fa fa-question-circle"></i> Sık Sorulan Sorular</h2>
                        </div>
                        <div class="card-body">
                            <p class="anasayfa-section-desc">Açılır-kapanır SSS öğeleri.</p>

                            <div class="repeatable-list" id="faqList">
                                <?php foreach ($faq as $i => $item): ?>
                                    <div class="repeatable-item">
                                        <div class="item-header">
                                            <strong>Soru #<?php echo $i + 1; ?></strong>
                                            <button type="button" class="btn-remove" title="Kaldır">✕</button>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Soru</label>
                                            <input type="text" name="faq_q[]" class="form-control" 
                                                   value="<?php echo htmlspecialchars($item['q']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Cevap</label>
                                            <textarea name="faq_a[]" class="form-control" rows="3"><?php echo htmlspecialchars($item['a']); ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn-add-item" data-target="faqList" data-template="tpl-faq">
                                <i class="fa fa-plus"></i> Soru ekle
                            </button>
                        </div>
                    </div>

                    <!-- KAYDET BUTONU -->
                    <div class="admin-action-bar">
                        <a href="./" class="btn-onizleme">
                            <i class="fa fa-times"></i> İptal
                        </a>
                        <button type="submit" class="btn-kaydet-ana" id="btn-save">
                            <i class="fa fa-save"></i> Değişiklikleri kaydet
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

<!-- ŞABLONLAR -->
<template id="tpl-card">
    <div class="repeatable-item">
        <div class="item-header">
            <strong>Yeni Kart</strong>
            <button type="button" class="btn-remove" title="Kaldır">✕</button>
        </div>
        <div class="form-group">
            <label class="form-label">Simge</label>
            <input type="text" name="card_icon[]" class="form-control" value="">
        </div>
        <div class="form-group">
            <label class="form-label">Başlık</label>
            <input type="text" name="card_title[]" class="form-control" value="">
        </div>
        <div class="form-group">
            <label class="form-label">Açıklama</label>
            <textarea name="card_text[]" class="form-control" rows="2"></textarea>
        </div>
    </div>
</template>

<template id="tpl-feature">
    <div class="repeatable-item">
        <div class="item-header">
            <strong>Yeni Özellik</strong>
            <button type="button" class="btn-remove" title="Kaldır">✕</button>
        </div>
        <div class="form-group">
            <label class="form-label">ID (slug)</label>
            <input type="text" name="feature_id[]" class="form-control" value="">
        </div>
        <div class="form-group">
            <label class="form-label">Etiket</label>
            <input type="text" name="feature_label[]" class="form-control" value="">
        </div>
        <div class="form-group">
            <label class="form-label">Başlık</label>
            <input type="text" name="feature_title[]" class="form-control" value="">
        </div>
        <div class="form-group">
            <label class="form-label">Açıklama</label>
            <textarea name="feature_text[]" class="form-control" rows="2"></textarea>
        </div>
    </div>
</template>

<template id="tpl-faq">
    <div class="repeatable-item">
        <div class="item-header">
            <strong>Yeni Soru</strong>
            <button type="button" class="btn-remove" title="Kaldır">✕</button>
        </div>
        <div class="form-group">
            <label class="form-label">Soru</label>
            <input type="text" name="faq_q[]" class="form-control" value="">
        </div>
        <div class="form-group">
            <label class="form-label">Cevap</label>
            <textarea name="faq_a[]" class="form-control" rows="3"></textarea>
        </div>
    </div>
</template>

<script src="../assets/js/main.js"></script>
<script>
(function() {
    'use strict';

    // Dinamik kart ekleme
    document.querySelectorAll('.btn-add-item').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.target;
            const tplId    = btn.dataset.template;
            const list     = document.getElementById(targetId);
            const tpl      = document.getElementById(tplId);
            if (!list || !tpl) return;
            const clone = tpl.content.firstElementChild.cloneNode(true);
            list.appendChild(clone);
            clone.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });

    // Kaldır butonu
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-remove')) {
            const item = e.target.closest('.repeatable-item');
            if (!item) return;
            if (confirm('Bu öğeyi kaldırmak istediğine emin misin?')) {
                item.style.transition = 'opacity .2s, transform .2s';
                item.style.opacity = '0';
                item.style.transform = 'translateX(-10px)';
                setTimeout(() => item.remove(), 200);
            }
        }
    });

    // Slug otomatik
    document.querySelectorAll('input[name="feature_title[]"]').forEach((input, idx) => {
        input.addEventListener('input', () => {
            const idInputs = document.querySelectorAll('input[name="feature_id[]"]');
            if (idInputs[idx] && idInputs[idx].value.trim() === '') {
                idInputs[idx].value = slugify(input.value);
            }
        });
    });

    function slugify(text) {
        const trMap = { 'ı':'i','ğ':'g','ü':'u','ş':'s','ö':'o','ç':'c','İ':'I','Ğ':'G','Ü':'U','Ş':'S','Ö':'O','Ç':'C' };
        return text
            .toLowerCase()
            .split('').map(c => trMap[c] || c).join('')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    // AJAX Kaydet
    const form = document.getElementById('adminForm');
    const btnSave = document.getElementById('btn-save');
    const alertContainer = document.getElementById('alert-container');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const data = {
            hero: {
                title: formData.get('hero_title') || '',
                subtitle: formData.get('hero_subtitle') || '',
                cta_text: formData.get('hero_cta_text') || '',
                cta_url: formData.get('hero_cta_url') || ''
            },
            intro: { title: formData.get('intro_title') || '', lead: formData.get('intro_lead') || '', cards: [] },
            features: [],
            faq: []
        };

        const cardIcons = formData.getAll('card_icon[]');
        const cardTitles = formData.getAll('card_title[]');
        const cardTexts = formData.getAll('card_text[]');
        cardIcons.forEach((icon, idx) => {
            const title = cardTitles[idx] || '';
            const text = cardTexts[idx] || '';
            if (icon || title || text) data.intro.cards.push({ icon, title, text });
        });

        const featIds = formData.getAll('feature_id[]');
        const featLabels = formData.getAll('feature_label[]');
        const featTitles = formData.getAll('feature_title[]');
        const featTexts = formData.getAll('feature_text[]');
        featIds.forEach((id, idx) => {
            const label = featLabels[idx] || '';
            const title = featTitles[idx] || '';
            const text = featTexts[idx] || '';
            if (id || label || title || text) data.features.push({ id, label, title, text });
        });

        const faqQs = formData.getAll('faq_q[]');
        const faqAs = formData.getAll('faq_a[]');
        faqQs.forEach((q, idx) => {
            const a = faqAs[idx] || '';
            if (q || a) data.faq.push({ q, a });
        });

        btnSave.disabled = true;
        const originalHtml = btnSave.innerHTML;
        btnSave.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Kaydediliyor...';

        try {
            const response = await fetch('kaydet.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ page: 'anasayfa', page_content: JSON.stringify(data) })
            });
            const result = await response.json();
            if (result.success) showAlert('success', result.message || 'Kaydedildi!');
            else showAlert('error', result.message || 'Kaydetme hatası!');
        } catch (error) {
            showAlert('error', 'Sunucu hatası: ' + error.message);
        } finally {
            btnSave.disabled = false;
            btnSave.innerHTML = originalHtml;
        }
    });

    function showAlert(type, message) {
        const alert = document.createElement('div');
        alert.className = `admin-alert admin-alert-${type === 'success' ? 'success' : 'error'}`;
        alert.innerHTML = `<i class="fa fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i><span>${message}</span>`;
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alert);
        setTimeout(() => {
            alert.style.transition = 'opacity .3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    }
})();
</script>
</body>
</html>