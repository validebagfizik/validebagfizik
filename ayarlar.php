<?php
// Ayarların kaydedileceği dosya yolu
$ayar_dosyasi = 'aktif_tema.txt';

// Eğer varsayılan olarak dosya yoksa "warm-amber" ile oluştur
if (!file_exists($ayar_dosyasi)) {
    file_put_contents($ayar_dosyasi, 'warm-amber');
}

// Post isteği geldiğinde temayı kaydet (AJAX ile çağrılacak)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tema'])) {
    $secilen_tema = preg_replace('/[^a-zA-Z0-7_-]/', '', $_POST['tema']); // Güvenlik temizliği
    
    // Geçerli temalar listesi kontrolü
    $gecerli_temalar = ['warm-amber', 'deep-space', 'cyber-ocean', 'forest-matrix'];
    
    if (in_array($secilen_tema, $gecerli_temalar)) {
        file_put_contents($ayar_dosyasi, $secilen_tema);
        echo json_encode(['success' => true, 'tema' => $secilen_tema]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Geçersiz tema']);
    exit;
}

// Aktif temayı oku
$aktif_tema = trim(file_get_contents($ayar_dosyasi));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Tema Ayarları</title>
    <!-- Sadece ayarlar.css yeterli (içinde tema değişkenleri de var) -->
    <link rel="stylesheet" href="assets/css/ayarlar.css">
</head>
<!-- Dinamik sınıf doğrudan body'e basılıyor -->
<body class="theme-<?php echo $aktif_tema; ?> settings-page">
    <div class="settings-container">
        <header class="settings-header">
            <h2>🎨 Tema & Görünüm Ayarları</h2>
            <p>Sistem genelinde kullanılacak premium renk şablonunu seçin.</p>
        </header>
        
        <div class="themes-grid">
            <!-- Tema 1: Warm Amber (Obsidyen & Altın) -->
            <div class="theme-card <?php echo $aktif_tema === 'warm-amber' ? 'active' : ''; ?>" data-theme="warm-amber">
                <div class="theme-preview warm-amber-preview">
                    <span class="dot d1"></span>
                    <span class="dot d2"></span>
                    <span class="dot d3"></span>
                </div>
                <div class="theme-info">
                    <h3>Obsidyen & Altın</h3>
                    <p>Sıcak kahve tonları ve asil altın vurguların premium uyumu.</p>
                </div>
                <div class="active-badge">✓ Aktif</div>
            </div>

            <!-- Tema 2: Deep Space (Gece Yarısı Mavisi) -->
            <div class="theme-card <?php echo $aktif_tema === 'deep-space' ? 'active' : ''; ?>" data-theme="deep-space">
                <div class="theme-preview deep-space-preview">
                    <span class="dot d1"></span>
                    <span class="dot d2"></span>
                    <span class="dot d3"></span>
                </div>
                <div class="theme-info">
                    <h3>Gece Yarısı Mavisi</h3>
                    <p>Derin okyanus laciverti ve yıldız tozu morunun sofistike dansı.</p>
                </div>
                <div class="active-badge">✓ Aktif</div>
            </div>

            <!-- Tema 3: Cyber Ocean (Kuzey Işıkları) -->
            <div class="theme-card <?php echo $aktif_tema === 'cyber-ocean' ? 'active' : ''; ?>" data-theme="cyber-ocean">
                <div class="theme-preview cyber-ocean-preview">
                    <span class="dot d1"></span>
                    <span class="dot d2"></span>
                    <span class="dot d3"></span>
                </div>
                <div class="theme-info">
                    <h3>Kuzey Işıkları</h3>
                    <p>Buzul mavisi ve aurora yeşilinin ferahlatıcı modern kombinasyonu.</p>
                </div>
                <div class="active-badge">✓ Aktif</div>
            </div>

            <!-- Tema 4: Forest Matrix (Zehirli Yeşil & Grafit) -->
            <div class="theme-card <?php echo $aktif_tema === 'forest-matrix' ? 'active' : ''; ?>" data-theme="forest-matrix">
                <div class="theme-preview forest-matrix-preview">
                    <span class="dot d1"></span>
                    <span class="dot d2"></span>
                    <span class="dot d3"></span>
                </div>
                <div class="theme-info">
                    <h3>Zümrüt & Grafit</h3>
                    <p>Koyu orman yeşili ve sofistike grafit tonlarının modern uyumu.</p>
                </div>
                <div class="active-badge">✓ Aktif</div>
            </div>
        </div>

        <div class="settings-footer">
            <button id="btn-save" class="btn-save-settings">Değişiklikleri Kaydet</button>
            <a href="editor.php" class="btn-back-editor">Editöre Dön ➜</a>
        </div>
    </div>
<script src="assets/js/ayarlar.js"></script>

</body>
</html>