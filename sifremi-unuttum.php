<?php
/**
 * sifremi-unuttum.php
 * Kullanıcı e-postasını girer, geçerliyse sıfırlama linki üretilir.
 *
 * NOT: Sunucuda SMTP/mail() yapılandırılı değilse (örn. yerel XAMPP),
 * gerçek e-posta gitmeyebilir. Bu yüzden test/geliştirme amacıyla,
 * link ayrıca ekranda gösterilir. Canlıya alırken bu ekran gösterimini
 * kaldırıp gerçek bir SMTP servisi (PHPMailer vb.) bağlamanız önerilir.
 */

require_once __DIR__ . '/includes/kullanici_islemleri.php';

$hata = null;
$gonderildi = false;
$testLinki = null; // sadece dev/test amaçlı ekranda gösterim için

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ki_csrf_dogrula($_POST['csrf_token'] ?? null)) {
        $hata = 'Oturum süresi doldu, lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $hata = 'Geçerli bir e-posta adresi girin.';
        } else {
            $tokenParam = ki_sifirlama_tokeni_olustur($email);

            // Güvenlik: hesap olsun ya da olmasın kullanıcıya aynı mesajı göster
            // (bu, hangi e-postaların sistemde kayıtlı olduğunun anlaşılmasını önler).
            $gonderildi = true;

            if ($tokenParam !== null) {
                $siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
                $link = $siteUrl . dirname($_SERVER['SCRIPT_NAME']) . '/sifre-sifirla.php?token=' . urlencode($tokenParam);
                ki_sifirlama_maili_gonder($email, $link);
                $testLinki = $link; // dev/test modu: linki ekranda da göster
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
.giris-wrapper { max-width: 440px; margin: 48px auto 72px; padding: 0 16px; }
.giris-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--glass-border, rgba(255,255,255,0.12));
    border-radius: var(--radius-lg, 20px);
    padding: 36px 32px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
}
.giris-baslik { font-size: 1.5rem; font-weight: 700; margin: 0 0 6px; color: var(--text-primary, #fff); }
.giris-alt-baslik { font-size: 0.92rem; color: var(--text-muted, rgba(255,255,255,0.65)); margin: 0 0 26px; line-height: 1.5; }
.giris-alan { margin-bottom: 18px; }
.giris-alan label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: var(--text-primary, #fff); }
.giris-input {
    width: 100% !important; padding: 12px 14px !important; border-radius: var(--radius-md, 12px) !important;
    border: 1px solid var(--border-color, rgba(255,255,255,0.18)) !important; background: rgba(0,0,0,0.2) !important;
    color: var(--text-primary, #fff) !important; font-size: 0.95rem !important; box-sizing: border-box !important;
}
.giris-input:focus { outline: none !important; border-color: var(--primary-color, #f2994a) !important; }
.giris-btn {
    width: 100%; padding: 13px; border: none; border-radius: var(--radius-pill, 999px);
    background: var(--primary-color, #f2994a); color: #1a0f0a; font-weight: 700; font-size: 0.98rem; cursor: pointer;
}
.giris-btn:hover { box-shadow: 0 8px 20px rgba(242, 153, 74, 0.35); }
.giris-alt-metin { text-align: center; margin-top: 22px; font-size: 0.88rem; color: var(--text-muted, rgba(255,255,255,0.65)); }
.giris-link { color: var(--primary-color, #f2994a); text-decoration: none; font-weight: 600; }
.giris-link:hover { text-decoration: underline; }
.giris-uyari {
    background: rgba(224, 7, 36, 0.12); border: 1px solid rgba(224, 7, 36, 0.35); color: #ffb4b4;
    padding: 11px 14px; border-radius: 10px; font-size: 0.88rem; margin-bottom: 20px;
}
.giris-basari {
    background: rgba(94, 194, 120, 0.12); border: 1px solid rgba(94, 194, 120, 0.35); color: #a8f0bd;
    padding: 11px 14px; border-radius: 10px; font-size: 0.88rem; margin-bottom: 20px; line-height: 1.6;
}
.giris-dev-kutu {
    background: rgba(224, 114, 74, 0.1); border: 1px dashed rgba(224, 114, 74, 0.4);
    padding: 12px 14px; border-radius: 10px; font-size: 0.82rem; margin-bottom: 20px; word-break: break-all;
    color: var(--text-muted, rgba(255,255,255,0.8));
}
.giris-dev-kutu strong { display: block; margin-bottom: 6px; color: var(--text-primary, #fff); }
.giris-dev-kutu a { color: var(--primary-color, #f2994a); }
</style>

<div class="giris-wrapper">
    <div class="giris-card">
        <h1 class="giris-baslik">Şifremi Unuttum</h1>

        <?php if ($hata): ?>
            <div class="giris-uyari"><?php echo htmlspecialchars($hata); ?></div>
        <?php endif; ?>

        <?php if ($gonderildi): ?>
            <div class="giris-basari">
                Eğer bu e-posta adresine kayıtlı bir hesap varsa, şifre sıfırlama bağlantısı gönderildi.
                Lütfen gelen kutunuzu (ve spam klasörünü) kontrol edin.
            </div>
            <?php if ($testLinki): ?>
                <div class="giris-dev-kutu">
                    <strong>Geliştirme modu — test linki:</strong>
                    Sunucunuzda e-posta gönderimi (SMTP) henüz yapılandırılmadığı için bağlantı burada da gösteriliyor:
                    <br><br>
                    <a href="<?php echo htmlspecialchars($testLinki); ?>"><?php echo htmlspecialchars($testLinki); ?></a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="giris-alt-baslik">
                Hesabınıza kayıtlı e-posta adresini girin, size şifre sıfırlama bağlantısı gönderelim.
            </p>
            <form method="post" action="sifremi-unuttum.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(ki_csrf_token()); ?>">
                <div class="giris-alan">
                    <label for="email">E-posta</label>
                    <input class="giris-input" type="email" id="email" name="email" placeholder="ornek@eposta.com" required autofocus>
                </div>
                <button type="submit" class="giris-btn">Sıfırlama Bağlantısı Gönder</button>
            </form>
        <?php endif; ?>

        <p class="giris-alt-metin">
            <a class="giris-link" href="giris.php">← Giriş sayfasına dön</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
