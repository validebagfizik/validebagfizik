<?php
/**
 * kayit_ol.php
 * data/giris.json içine yeni kullanıcı ekleyen basit kayıt sayfası.
 */

require_once __DIR__ . '/includes/kullanici_islemleri.php';

if (ki_giris_yapilmis_mi()) {
    header('Location: index.php');
    exit;
}

$hata = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ki_csrf_dogrula($_POST['csrf_token'] ?? null)) {
        $hata = 'Oturum süresi doldu, lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $adSoyad = trim($_POST['ad_soyad'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sifre = (string) ($_POST['sifre'] ?? '');
        $sifreTekrar = (string) ($_POST['sifre_tekrar'] ?? '');

        if ($sifre !== $sifreTekrar) {
            $hata = 'Şifreler birbiriyle uyuşmuyor.';
        } else {
            $sonuc = ki_kayit_ol($adSoyad, $email, $sifre);
            if ($sonuc['basarili']) {
                header('Location: giris.php?kayit=basarili');
                exit;
            }
            $hata = $sonuc['mesaj'];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
.giris-wrapper { max-width: 460px; margin: 48px auto 72px; padding: 0 16px; }
.giris-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--glass-border, rgba(255,255,255,0.12));
    border-radius: var(--radius-lg, 20px);
    padding: 36px 32px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
}
.giris-baslik { font-size: 1.6rem; font-weight: 700; margin: 0 0 6px; color: var(--text-primary, #fff); }
.giris-alt-baslik { font-size: 0.92rem; color: var(--text-muted, rgba(255,255,255,0.65)); margin: 0 0 28px; }
.giris-alan { margin-bottom: 18px; }
.giris-alan label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: var(--text-primary, #fff); }
.giris-input-wrap { position: relative; }
.giris-input {
    width: 100% !important; padding: 12px 14px !important; border-radius: var(--radius-md, 12px) !important;
    border: 1px solid var(--border-color, rgba(255,255,255,0.18)) !important; background: rgba(0,0,0,0.2) !important;
    color: var(--text-primary, #fff) !important; font-size: 0.95rem !important; box-sizing: border-box !important;
    transition: border-color .15s ease;
}
.giris-input:focus { outline: none !important; border-color: var(--primary-color, #f2994a) !important; }
.giris-input-wrap .giris-input { padding-right: 44px !important; }
.giris-goz-btn {
    position: absolute; top: 50%; right: 6px; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; color: var(--text-muted, rgba(255,255,255,0.6)); padding: 8px;
    display: flex; align-items: center;
}
.giris-goz-btn:hover { color: var(--text-primary, #fff); }
.giris-btn {
    width: 100%; padding: 13px; border: none; border-radius: var(--radius-pill, 999px);
    background: var(--primary-color, #f2994a); color: #1a0f0a; font-weight: 700; font-size: 0.98rem;
    cursor: pointer; margin-top: 6px; transition: transform .12s ease, box-shadow .12s ease;
}
.giris-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(242, 153, 74, 0.35); }
.giris-alt-metin { text-align: center; margin-top: 22px; font-size: 0.88rem; color: var(--text-muted, rgba(255,255,255,0.65)); }
.giris-link { color: var(--primary-color, #f2994a); text-decoration: none; font-weight: 600; }
.giris-link:hover { text-decoration: underline; }
.giris-uyari {
    background: rgba(224, 7, 36, 0.12); border: 1px solid rgba(224, 7, 36, 0.35); color: #ffb4b4;
    padding: 11px 14px; border-radius: 10px; font-size: 0.88rem; margin-bottom: 20px;
}
.giris-ipucu { font-size: 0.78rem; color: var(--text-muted, rgba(255,255,255,0.55)); margin: 6px 0 0; }
</style>

<div class="giris-wrapper">
    <div class="giris-card">
        <h1 class="giris-baslik">Kayıt Ol</h1>
        <p class="giris-alt-baslik">Ücretsiz hesabınızı oluşturun.</p>

        <?php if ($hata): ?>
            <div class="giris-uyari"><?php echo htmlspecialchars($hata); ?></div>
        <?php endif; ?>

        <form method="post" action="kayit_ol.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(ki_csrf_token()); ?>">

            <div class="giris-alan">
                <label for="ad_soyad">Ad Soyad</label>
                <input class="giris-input" type="text" id="ad_soyad" name="ad_soyad" placeholder="Adınız Soyadınız"
                       value="<?php echo htmlspecialchars($_POST['ad_soyad'] ?? ''); ?>" required autofocus>
            </div>

            <div class="giris-alan">
                <label for="email">E-posta</label>
                <input class="giris-input" type="email" id="email" name="email" placeholder="ornek@eposta.com"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>

            <div class="giris-alan">
                <label for="sifre">Şifre</label>
                <div class="giris-input-wrap">
                    <input class="giris-input" type="password" id="sifre" name="sifre" placeholder="En az 6 karakter" required>
                    <button type="button" class="giris-goz-btn" onclick="kayitSifreGosterGizle('sifre','goz1')" aria-label="Şifreyi göster/gizle">
                        <svg id="goz1" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"></path><circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                <p class="giris-ipucu">En az 6 karakter kullanın.</p>
            </div>

            <div class="giris-alan">
                <label for="sifre_tekrar">Şifre (Tekrar)</label>
                <div class="giris-input-wrap">
                    <input class="giris-input" type="password" id="sifre_tekrar" name="sifre_tekrar" placeholder="••••••••" required>
                    <button type="button" class="giris-goz-btn" onclick="kayitSifreGosterGizle('sifre_tekrar','goz2')" aria-label="Şifreyi göster/gizle">
                        <svg id="goz2" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"></path><circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="giris-btn">Hesap Oluştur</button>
        </form>

        <p class="giris-alt-metin">
            Zaten hesabınız var mı? <a class="giris-link" href="giris.php">Giriş Yap</a>
        </p>
    </div>
</div>

<script>
function kayitSifreGosterGizle(inputId, ikonId) {
    const input = document.getElementById(inputId);
    const goster = input.type === 'password';
    input.type = goster ? 'text' : 'password';
    const ikon = document.getElementById(ikonId);
    ikon.innerHTML = goster
        ? '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a20.29 20.29 0 0 1 4.06-5.06M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 7 11 7a20.29 20.29 0 0 1-2.34 3.06M1 1l22 22"></path><path d="M14.12 14.12A3 3 0 1 1 9.88 9.88"></path>'
        : '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"></path><circle cx="12" cy="12" r="3"></circle>';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
