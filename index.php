<?php
// ------------------------------------------------------------------
// 1. TEMA AYARI
// ------------------------------------------------------------------
$gecerliTemalar = ['warm-amber', 'deep-space', 'cyber-ocean', 'forest-matrix'];
$okunanTema     = file_exists(__DIR__ . '/aktif_tema.txt') ? trim(file_get_contents(__DIR__ . '/aktif_tema.txt')) : 'warm-amber';
$aktifTema      = in_array($okunanTema, $gecerliTemalar, true) ? $okunanTema : 'warm-amber';

$pageTitle   = "Fizik Platformu";
$currentPage = "anasayfa";

// ------------------------------------------------------------------
// 2. JSON VERİSİNİ OKU
// ------------------------------------------------------------------
$jsonYolu = __DIR__ . '/data/anasayfa.json'; 
$home = [];

if (file_exists($jsonYolu)) {
    $raw = file_get_contents($jsonYolu);
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $home = $decoded;
    }
}

// ------------------------------------------------------------------
// 3. VERİLERİ DEĞİŞKENLERE ATA (JSON yoksa varsayılan değerleri kullan)
// ------------------------------------------------------------------
$hero = $home['hero'] ?? [
    'title'    => 'evrenin <span class="highlight">yeni</span> dili',
    'subtitle' => 'Oku. Hesapla. Keşfet. Tek portaldan.',
    'cta_text' => 'Keşfetmeye başla',
    'cta_url'  => '#intro'
];

$intro = $home['intro'] ?? [
    'title' => 'formülleri oku, denklemleri çöz, kavramları keşfet',
    'lead'  => 'Fizik Portal ile evrenin kurallarını daha kolay anla',
    'cards' => [
        ['icon' => '∑', 'title' => 'Ücretsiz ve açık', 'text' => 'Hobi amaçlı, tamamen ücretsiz.'],
        ['icon' => 'Φ', 'title' => 'Türkçe anlatım', 'text' => 'Kavramlar yerel dilde, günlük hayattan örneklerle sunuluyor.'],
        ['icon' => 'λ', 'title' => 'Özgün içerik', 'text' => 'Her yazı, formül ve açıklama bu portal için özel hazırlanıyor.']
    ]
];

$features = $home['features'] ?? [];
$faq = $home['faq'] ?? [];

require 'includes/header.php';
?>

<!-- HERO BÖLÜMÜ -->
<section class="hero">
    <div class="hero-copy">
        <h1 class="hero-title"><?= $hero['title'] ?></h1>
        <p class="hero-subtitle"><?= htmlspecialchars($hero['subtitle'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <a href="<?= htmlspecialchars($hero['cta_url'], ENT_QUOTES, 'UTF-8') ?>" class="cta-button">
        <?= htmlspecialchars($hero['cta_text'], ENT_QUOTES, 'UTF-8') ?>
    </a>
    
    <div class="hero-center">
        <div class="atom-glow"></div>
        <div class="atom-icon">
            <svg viewBox="0 0 100 100" fill="none">
                <ellipse cx="50" cy="50" rx="45" ry="15" stroke="var(--accent-cyan)" stroke-width="1.5" transform="rotate(0 50 50)"/>
                <ellipse cx="50" cy="50" rx="45" ry="15" stroke="var(--accent-cyan)" stroke-width="1.5" transform="rotate(60 50 50)"/>
                <ellipse cx="50" cy="50" rx="45" ry="15" stroke="var(--accent-cyan)" stroke-width="1.5" transform="rotate(120 50 50)"/>
                <circle cx="50" cy="50" r="6" fill="var(--accent-purple)"/>
            </svg>
        </div>
    </div>

    <div class="hero-cards">
        <div class="float-card card-formula">
            <span class="card-label">Formül</span>
            <div class="formula">E = mc²</div>
            <span class="card-meta">Einstein · Özel Görelilik</span>
        </div>
        <div class="float-card card-graph">
            <span class="card-label">Grafik</span>
            <svg class="mini-graph" viewBox="0 0 120 60" fill="none">
                <path d="M10 50 Q30 10, 50 30 T90 20 T110 40" stroke="var(--accent-purple)" stroke-width="2" fill="none"/>
                <circle cx="50" cy="30" r="3" fill="var(--accent-lime)"/>
            </svg>
            <span class="card-meta">Basit Harmonik Hareket</span>
        </div>
        <div class="float-card card-constant">
            <span class="card-label">Sabit</span>
            <div class="constant">c = 299 792 458 m/s</div>
            <span class="card-meta">Işık hızı</span>
        </div>
    </div>
</section>

<!-- DİNAMİK İÇERİK -->
<div id="site-content">
    
    <!-- INTRO -->
    <section class="intro" id="intro">
        <h2 class="section-title"><?= htmlspecialchars($intro['title'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="section-lead"><?= htmlspecialchars($intro['lead'], ENT_QUOTES, 'UTF-8') ?></p>
        <div class="intro-grid">
            <?php foreach ($intro['cards'] as $card): ?>
            <article class="intro-card">
                <div class="intro-icon"><?= $card['icon'] ?></div>
                <h3><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($card['text'], ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- FEATURES -->
    <?php if (!empty($features)): ?>
    <section class="features" id="konular">
        <h2 class="section-title">fizik yolculuğunda yanında</h2>
        <div class="features-track">
            <?php foreach ($features as $i => $feature): ?>
            <article class="feature-card <?= $i === 0 ? 'is-active' : '' ?>" data-feature="<?= htmlspecialchars($feature['id'], ENT_QUOTES, 'UTF-8') ?>">
                <span class="feature-label"><?= htmlspecialchars($feature['label'], ENT_QUOTES, 'UTF-8') ?></span>
                <h3><?= htmlspecialchars($feature['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($feature['text'], ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="feature-dots">
            <?php foreach ($features as $i => $feature): ?>
            <button type="button" class="feature-dot <?= $i === 0 ? 'is-active' : '' ?>" data-index="<?= $i ?>"></button>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- FAQ -->
    <?php if (!empty($faq)): ?>
    <section class="faq" id="sss">
        <h2 class="section-title">sık sorulan sorular</h2>
        <div class="faq-list">
            <?php foreach ($faq as $i => $item): ?>
            <details class="faq-item" <?= $i === 0 ? 'open' : '' ?>>
                <summary><?= htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8') ?></summary>
                <p><?= htmlspecialchars($item['a'], ENT_QUOTES, 'UTF-8') ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dots = document.querySelectorAll('.feature-dot');
    const cards = document.querySelectorAll('.feature-card');
    let currentFeature = 0;
    let featureInterval;

    function showFeature(index) {
        // Kartları ve noktaları güncelle
        cards.forEach((card, i) => {
            card.classList.toggle('is-active', i === index);
        });
        dots.forEach((dot, i) => {
            dot.classList.toggle('is-active', i === index);
        });
        currentFeature = index;
    }

    // Noktalara tıklanınca o kartı göster
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showFeature(index);
            resetInterval(); // Tıklanınca otomatik geçiş süresini sıfırla
        });
    });

    // Otomatik geçiş fonksiyonu (5 saniyede bir)
    function startAutoSlide() {
        featureInterval = setInterval(() => {
            let nextIndex = (currentFeature + 1) % cards.length;
            showFeature(nextIndex);
        }, 5000);
    }

    // Süreyi sıfırla (kullanıcı tıkladığında otomatik geçişin hemen atlamaması için)
    function resetInterval() {
        clearInterval(featureInterval);
        startAutoSlide();
    }

    // Eğer kart varsa otomatik geçişi başlat
    if (cards.length > 0) {
        startAutoSlide();
    }
});
</script>
<?php require 'includes/footer.php'; ?>