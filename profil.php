<?php
/**
 * profil.php
 * Kullanıcıların kendi hesap bilgilerini ve şifrelerini güncelleyebileceği sayfa.
 */

require_once __DIR__ . '/includes/kullanici_islemleri.php';

// Giriş yapılmamışsa giriş sayfasına yönlendir
if (!ki_giris_yapilmis_mi()) {
    header('Location: giris.php?yonlendir=profil.php');
    exit;
}

 $kullanici_id = $_SESSION['kullanici_id'];
 $kullanicilar = ki_kullanicilari_oku();
 $idx = ki_id_index($kullanicilar, $kullanici_id);

// Kullanıcı veritabanında bulunamadıysa çıkış yaptır
if ($idx < 0) {
    ki_cikis_yap();
    exit;
}

 $mevcut_kullanici = $kullanicilar[$idx];
 $hata = null;
 $basari = null;

// Form gönderilmişse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ki_csrf_dogrula($_POST['csrf_token'] ?? null)) {
        $hata = 'Güvenlik doğrulaması başarısız. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $yeni_ad = trim($_POST['ad_soyad'] ?? '');
        $yeni_email = mb_strtolower(trim($_POST['email'] ?? ''));
        $mevcut_sifre = $_POST['mevcut_sifre'] ?? '';
        $yeni_sifre = $_POST['yeni_sifre'] ?? '';
        
        // 1. Temel Doğrulamalar
        if ($yeni_ad === '' || $yeni_email === '') {
            $hata = 'Ad Soyad ve E-posta alanları boş bırakılamaz.';
        } elseif (!filter_var($yeni_email, FILTER_VALIDATE_EMAIL)) {
            $hata = 'Lütfen geçerli bir e-posta adresi girin.';
        } else {
            // 2. E-posta kontrolü (Başkası tarafından kullanılıyor mu?)
            $email_degisti = (mb_strtolower($mevcut_kullanici['email']) !== $yeni_email);
            if ($email_degisti && ki_email_ile_bul($yeni_email, $kullanicilar) !== null) {
                $hata = 'Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor.';
            } else {
                // 3. Şifre değiştirme kontrolü (Eğer yeni şifre girilmişse)
                $sifre_guncellendi = false;
                if (!empty($yeni_sifre)) {
                    // Şifre değiştirmek için mevcut şifreyi doğru girmeli
                    if (!password_verify($mevcut_sifre, $mevcut_kullanici['sifre_hash'] ?? '')) {
                        $hata = 'Mevcut şifreniz yanlış. Şifrenizi değiştirmek için mevcut şifrenizi doğru girmelisiniz.';
                    } elseif (mb_strlen($yeni_sifre) < 6) {
                        $hata = 'Yeni şifre en az 6 karakter olmalıdır.';
                    } else {
                        $sifre_guncellendi = true;
                    }
                }

                // 4. Hata yoksa veritabanını güncelle
                if (!$hata) {
                    $kullanicilar[$idx]['ad_soyad'] = $yeni_ad;
                    $kullanicilar[$idx]['email'] = $yeni_email;
                    
                    if ($sifre_guncellendi) {
                        $kullanicilar[$idx]['sifre_hash'] = password_hash($yeni_sifre, PASSWORD_DEFAULT);
                    }

                    ki_kullanicilari_yaz($kullanicilar);
                    
                    // Session'daki adı da güncelle (Header'da yanlış isim görünmesin)
                    $_SESSION['kullanici_ad'] = $yeni_ad;
                    $_SESSION['kullanici_email'] = $yeni_email;

                    $basari = 'Profil bilgileriniz başarıyla güncellendi.';
                    $mevcut_kullanici = $kullanicilar[$idx]; // Formu yeni bilgilerle doldur
                }
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
.profil-wrapper {
    max-width: 600px;
    margin: 48px auto 72px;
    padding: 0 16px;
}

.profil-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--glass-border, rgba(255,255,255,0.12));
    border-radius: var(--radius-lg, 20px);
    padding: 36px 32px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
}

.profil-baslik {
    font-size: 1.6rem;
    font-weight: 700;
    margin: 0 0 6px;
    color: var(--text-primary, #fff);
    display: flex;
    align-items: center;
    gap: 12px;
}

.profil-alt-baslik {
    font-size: 0.92rem;
    color: var(--text-muted, rgba(255,255,255,0.65));
    margin: 0 0 28px;
}

.profil-alan { margin-bottom: 20px; }
.profil-alan label {
    display: block; font-size: 0.85rem; font-weight: 600;
    margin-bottom: 6px; color: var(--text-primary, #fff);
}

.profil-input {
    width: 100%; padding: 12px 14px;
    border-radius: var(--radius-md, 12px);
    border: 1px solid var(--border-color, rgba(255,255,255,0.18));
    background: rgba(0, 0, 0, 0.2);
    color: var(--text-primary, #fff);
    font-size: 0.95rem; box-sizing: border-box;
    transition: border-color .15s ease;
}

.profil-input:focus {
    outline: none;
    border-color: var(--primary-color, #f2994a);
}

.profil-btn {
    width: 100%; padding: 13px;
    border: none; border-radius: var(--radius-pill, 999px);
    background: var(--primary-color, #f2994a);
    color: #1a0f0a; font-weight: 700; font-size: 0.98rem;
    cursor: pointer; transition: transform .12s ease, box-shadow .12s ease;
    margin-top: 10px;
}

.profil-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(242, 153, 74, 0.35);
}

.profil-uyari {
    background: rgba(224, 7, 36, 0.12); border: 1px solid rgba(224, 7, 36, 0.35);
    color: #ffb4b4; padding: 11px 14px; border-radius: 10px;
    font-size: 0.88rem; margin-bottom: 20px;
}

.profil-basari {
    background: rgba(94, 194, 120, 0.12); border: 1px solid rgba(94, 194, 120, 0.35);
    color: #a8f0bd; padding: 11px 14px; border-radius: 10px;
    font-size: 0.88rem; margin-bottom: 20px;
}

.sifre-degistir-alani {
    margin-top: 30px; padding-top: 20px;
    border-top: 1px solid var(--glass-border, rgba(255,255,255,0.1));
}

.sifre-degistir-alani h3 {
    font-size: 1.1rem; margin-bottom: 16px; color: var(--text-primary, #fff);
}
</style>

<div class="profil-wrapper">
    <div class="profil-card">
        <h1 class="profil-baslik"><i class="fa fa-user-circle"></i> Profilim</h1>
        <p class="profil-alt-baslik">Hesap bilgilerinizi ve şifrenizi buradan yönetin.</p>

        <?php if ($hata): ?>
            <div class="profil-uyari"><?php echo htmlspecialchars($hata); ?></div>
        <?php endif; ?>

        <?php if ($basari): ?>
            <div class="profil-basari"><?php echo htmlspecialchars($basari); ?></div>
        <?php endif; ?>

        <form method="post" action="profil.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(ki_csrf_token()); ?>">

            <div class="profil-alan">
                <label for="ad_soyad">Ad Soyad</label>
                <input class="profil-input" type="text" id="ad_soyad" name="ad_soyad" 
                       value="<?php echo htmlspecialchars($mevcut_kullanici['ad_soyad'] ?? ''); ?>" required>
            </div>

            <div class="profil-alan">
                <label for="email">E-posta</label>
                <input class="profil-input" type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($mevcut_kullanici['email'] ?? ''); ?>" required>
            </div>

            <div class="sifre-degistir-alani">
                <h3><i class="fa fa-key"></i> Şifre Değiştir (İsteğe Bağlı)</h3>
                
                <div class="profil-alan">
                    <label for="mevcut_sifre">Mevcut Şifre</label>
                    <input class="profil-input" type="password" id="mevcut_sifre" name="mevcut_sifre" placeholder="••••••••">
                    <small style="color: var(--text-soft, #888); font-size: 0.8rem; margin-top: 4px; display: block;">Şifrenizi değiştirmek istemiyorsanız boş bırakın.</small>
                </div>

                <div class="profil-alan">
                    <label for="yeni_sifre">Yeni Şifre</label>
                    <input class="profil-input" type="password" id="yeni_sifre" name="yeni_sifre" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="profil-btn"><i class="fa fa-save"></i> Değişiklikleri Kaydet</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>