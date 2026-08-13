<?php
/**
 * includes/kullanici_islemleri.php
 * -----------------------------------------------------------------------
 * data/giris.json dosyasını "kullanıcı veritabanı" gibi kullanan, dosya
 * tabanlı kimlik doğrulama kütüphanesi.
 *
 * Bu dosyayı ihtiyaç duyan her sayfanın en üstünde şu şekilde çağırın:
 *      require_once __DIR__ . '/includes/kullanici_islemleri.php';
 *
 * Sağladığı özellikler:
 *  - Kayıt / giriş / çıkış
 *  - "Beni Hatırla" (30 gün, cookie + hash)
 *  - Hatalı giriş denemesi sınırlama (5 deneme -> 15 dakika kilit)
 *  - CSRF token üretimi/doğrulaması
 *  - Şifremi unuttum / şifre sıfırlama token akışı
 * -----------------------------------------------------------------------
 */

// ---------------------------------------------------------------------------
// OTURUM BAŞLAT
// ---------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        // 'secure' => true, // Siteniz https'e geçtiğinde bu satırı açın
    ]);
    session_start();
}

// ---------------------------------------------------------------------------
// AYARLAR
// ---------------------------------------------------------------------------
define('KI_JSON_PATH', __DIR__ . '/../data/giris.json');
define('KI_MAX_DENEME', 5);           // kaç hatalı denemede kilitlensin
define('KI_KILIT_SURESI_DK', 15);     // kilit süresi (dakika)
define('KI_HATIRLA_GUN', 30);         // "beni hatırla" süresi (gün)
define('KI_RESET_SURESI_DK', 60);     // şifre sıfırlama linki geçerlilik süresi (dakika)

// ---------------------------------------------------------------------------
// JSON OKUMA / YAZMA (kilitli / güvenli)
// ---------------------------------------------------------------------------

/** data/giris.json içeriğini diziye çevirip döner. Dosya yoksa oluşturur. */
function ki_kullanicilari_oku(): array
{
    $dizin = dirname(KI_JSON_PATH);
    if (!is_dir($dizin)) {
        mkdir($dizin, 0755, true);
    }
    if (!file_exists(KI_JSON_PATH)) {
        file_put_contents(KI_JSON_PATH, json_encode([], JSON_PRETTY_PRINT));
    }

    $fp = fopen(KI_JSON_PATH, 'r');
    if (!$fp) return [];
    flock($fp, LOCK_SH);
    $icerik = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    $veri = json_decode($icerik, true);
    return is_array($veri) ? $veri : [];
}

/** Diziyi data/giris.json'a güvenli (kilitli) şekilde yazar. */
function ki_kullanicilari_yaz(array $kullanicilar): bool
{
    $fp = fopen(KI_JSON_PATH, 'c+');
    if (!$fp) return false;
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($kullanicilar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

/** E-postaya göre kullanıcıyı bulur; bulunamazsa null döner. */
function ki_email_ile_bul(string $email, array $kullanicilar): ?array
{
    $email = mb_strtolower(trim($email));
    foreach ($kullanicilar as $k) {
        if (mb_strtolower($k['email'] ?? '') === $email) {
            return $k;
        }
    }
    return null;
}

/** ID'ye göre dizideki index'i bulur; bulunamazsa -1 döner. */
function ki_id_index(array $kullanicilar, string $id): int
{
    foreach ($kullanicilar as $i => $k) {
        if (($k['id'] ?? '') === $id) return $i;
    }
    return -1;
}

// ---------------------------------------------------------------------------
// CSRF KORUMASI
// ---------------------------------------------------------------------------
function ki_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function ki_csrf_dogrula(?string $gelenToken): bool
{
    return !empty($_SESSION['csrf_token']) && !empty($gelenToken)
        && hash_equals($_SESSION['csrf_token'], $gelenToken);
}

// ---------------------------------------------------------------------------
// KAYIT
// ---------------------------------------------------------------------------
/**
 * Yeni kullanıcı oluşturur.
 * Dönüş: ['basarili' => bool, 'mesaj' => string]
 */
function ki_kayit_ol(string $adSoyad, string $email, string $sifre): array
{
    $email = mb_strtolower(trim($email));
    $adSoyad = trim($adSoyad);

    if ($adSoyad === '' || $email === '' || $sifre === '') {
        return ['basarili' => false, 'mesaj' => 'Lütfen tüm alanları doldurun.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['basarili' => false, 'mesaj' => 'Geçerli bir e-posta adresi girin.'];
    }
    if (mb_strlen($sifre) < 6) {
        return ['basarili' => false, 'mesaj' => 'Şifre en az 6 karakter olmalı.'];
    }

    $kullanicilar = ki_kullanicilari_oku();
    if (ki_email_ile_bul($email, $kullanicilar) !== null) {
        return ['basarili' => false, 'mesaj' => 'Bu e-posta adresi ile zaten bir hesap var.'];
    }

    $yeniKullanici = [
        'id'               => 'kul_' . bin2hex(random_bytes(8)),
        'ad_soyad'         => $adSoyad,
        'email'            => $email,
        'sifre_hash'       => password_hash($sifre, PASSWORD_DEFAULT),
        'rol'              => 'kullanici',
        'olusturulma'      => date('d.m.Y H:i:s'),
        'son_giris'        => null,
        'basarisiz_deneme' => 0,
        'kilit_bitis'      => null,
        'hatirla_hash'     => null,
        'hatirla_bitis'    => null,
        'reset_hash'       => null,
        'reset_bitis'      => null,
    ];

    $kullanicilar[] = $yeniKullanici;
    ki_kullanicilari_yaz($kullanicilar);

    return ['basarili' => true, 'mesaj' => 'Hesabınız oluşturuldu, giriş yapabilirsiniz.'];
}

// ---------------------------------------------------------------------------
// DENEME SINIRLAMA (BRUTE-FORCE KORUMASI)
// ---------------------------------------------------------------------------
/** Kullanıcı şu an kilitli mi? Kilitliyse kalan dakikayı da döner. */
function ki_kilitli_mi(array $kullanici): array
{
    if (empty($kullanici['kilit_bitis'])) {
        return ['kilitli' => false, 'kalan_dk' => 0];
    }
    $bitis = strtotime($kullanici['kilit_bitis']);
    $simdi = time();
    if ($simdi >= $bitis) {
        return ['kilitli' => false, 'kalan_dk' => 0];
    }
    return ['kilitli' => true, 'kalan_dk' => (int) ceil(($bitis - $simdi) / 60)];
}

/** Hatalı giriş sonrası deneme sayacını artırır, gerekirse hesabı kilitler. */
function ki_basarisiz_deneme_kaydet(string $email): void
{
    $kullanicilar = ki_kullanicilari_oku();
    $email = mb_strtolower(trim($email));
    foreach ($kullanicilar as &$k) {
        if (mb_strtolower($k['email'] ?? '') === $email) {
            $k['basarisiz_deneme'] = ($k['basarisiz_deneme'] ?? 0) + 1;
            if ($k['basarisiz_deneme'] >= KI_MAX_DENEME) {
                $k['kilit_bitis'] = date('d.m.Y H:i:s', time() + KI_KILIT_SURESI_DK * 60);
            }
            break;
        }
    }
    unset($k);
    ki_kullanicilari_yaz($kullanicilar);
}

/** Başarılı girişte deneme sayacını sıfırlar ve son giriş zamanını günceller. */
function ki_denemeleri_sifirla_ve_giris_kaydet(string $id): void
{
    $kullanicilar = ki_kullanicilari_oku();
    $idx = ki_id_index($kullanicilar, $id);
    if ($idx >= 0) {
        $kullanicilar[$idx]['basarisiz_deneme'] = 0;
        $kullanicilar[$idx]['kilit_bitis'] = null;
        $kullanicilar[$idx]['son_giris'] = date('d.m.Y H:i:s');
        ki_kullanicilari_yaz($kullanicilar);
    }
}

// ---------------------------------------------------------------------------
// GİRİŞ / ÇIKIŞ
// ---------------------------------------------------------------------------
/**
 * Giriş denemesi yapar.
 * Dönüş: ['basarili' => bool, 'mesaj' => string]
 */
function ki_giris_yap(string $email, string $sifre, bool $beniHatirla = false): array
{
    $kullanicilar = ki_kullanicilari_oku();
    $kullanici = ki_email_ile_bul($email, $kullanicilar);

    // Kullanıcı bulunamadıysa: aynı jenerik mesajı ver (hesap keşfini engellemek için)
    // ve enumeration/timing saldırılarını yavaşlatmak için küçük bir gecikme uygula.
    if ($kullanici === null) {
        usleep(400000); // 0.4 sn
        return ['basarili' => false, 'mesaj' => 'E-posta veya şifre hatalı.'];
    }

    $kilitDurumu = ki_kilitli_mi($kullanici);
    if ($kilitDurumu['kilitli']) {
        return [
            'basarili' => false,
            'mesaj' => "Çok fazla hatalı deneme yapıldı. Lütfen {$kilitDurumu['kalan_dk']} dakika sonra tekrar deneyin.",
        ];
    }
    // ENGELLİ KULLANICI KONTROLÜ
    if (isset($kullanici['durum']) && $kullanici['durum'] === 'engelli') {
        return [
            'basarili' => false,
            'mesaj' => "Hesabınız yönetici tarafından engellenmiştir. Lütfen iletişime geçin.",
        ];
    }
    if (!password_verify($sifre, $kullanici['sifre_hash'] ?? '')) {
        ki_basarisiz_deneme_kaydet($email);
        return ['basarili' => false, 'mesaj' => 'E-posta veya şifre hatalı.'];
    }

    // Başarılı giriş
    ki_denemeleri_sifirla_ve_giris_kaydet($kullanici['id']);
    ki_oturum_baslat($kullanici);

    if ($beniHatirla) {
        ki_hatirla_cookie_olustur($kullanici['id']);
    }

    return ['basarili' => true, 'mesaj' => 'Giriş başarılı.'];
}

/** Oturumu başlatır (session'a kullanıcı bilgilerini yazar). */
function ki_oturum_baslat(array $kullanici): void
{
    session_regenerate_id(true);
    $_SESSION['kullanici_id']   = $kullanici['id'];
    $_SESSION['kullanici_ad']   = $kullanici['ad_soyad'];
    $_SESSION['kullanici_email'] = $kullanici['email'];
    $_SESSION['kullanici_rol']  = $kullanici['rol'] ?? 'kullanici';
}

/** Şu an giriş yapılmış mı? */
function ki_giris_yapilmis_mi(): bool
{
    return !empty($_SESSION['kullanici_id']);
}

/** "Beni Hatırla" cookie'si oluşturur ve kullanıcı kaydına hash'ini yazar. */
function ki_hatirla_cookie_olustur(string $id): void
{
    $token = bin2hex(random_bytes(32));
    $hash  = hash('sha256', $token);
    $bitisZaman = time() + KI_HATIRLA_GUN * 86400;

    $kullanicilar = ki_kullanicilari_oku();
    $idx = ki_id_index($kullanicilar, $id);
    if ($idx >= 0) {
        $kullanicilar[$idx]['hatirla_hash']  = $hash;
        $kullanicilar[$idx]['hatirla_bitis'] = date('d.m.Y H:i:s', $bitisZaman);
        ki_kullanicilari_yaz($kullanicilar);
    }

    setcookie('hatirla_beni', $id . ':' . $token, [
        'expires'  => $bitisZaman,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * Sayfa yüklendiğinde, oturum yoksa "beni hatırla" cookie'sine bakıp
 * otomatik giriş yapmayı dener. giris.php dışındaki korumalı sayfaların
 * en üstünde çağırabilirsiniz.
 */
function ki_hatirla_ile_otomatik_giris(): void
{
    if (ki_giris_yapilmis_mi() || empty($_COOKIE['hatirla_beni'])) {
        return;
    }

    [$id, $token] = array_pad(explode(':', $_COOKIE['hatirla_beni'], 2), 2, '');
    if ($id === '' || $token === '') return;

    $kullanicilar = ki_kullanicilari_oku();
    $idx = ki_id_index($kullanicilar, $id);
    if ($idx < 0) return;

    $k = $kullanicilar[$idx];
    if (empty($k['hatirla_hash']) || empty($k['hatirla_bitis'])) return;
    if (strtotime($k['hatirla_bitis']) < time()) return;

    if (hash_equals($k['hatirla_hash'], hash('sha256', $token))) {
        ki_oturum_baslat($k);
        ki_kullanicilari_yaz($kullanicilar); // (ileride token rotasyonu istenirse burada yapılır)
    }
}

/** Çıkış yapar: session'ı ve "beni hatırla" cookie/hash'ini temizler. */
function ki_cikis_yap(): void
{
    $id = $_SESSION['kullanici_id'] ?? null;

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path']);
    }
    session_destroy();

    if (!empty($_COOKIE['hatirla_beni'])) {
        setcookie('hatirla_beni', '', time() - 42000, '/');
    }

    if ($id) {
        $kullanicilar = ki_kullanicilari_oku();
        $idx = ki_id_index($kullanicilar, $id);
        if ($idx >= 0) {
            $kullanicilar[$idx]['hatirla_hash']  = null;
            $kullanicilar[$idx]['hatirla_bitis'] = null;
            ki_kullanicilari_yaz($kullanicilar);
        }
    }
}

// ---------------------------------------------------------------------------
// ŞİFREMİ UNUTTUM / ŞİFRE SIFIRLAMA
// ---------------------------------------------------------------------------
/**
 * Sıfırlama token'ı üretir, kullanıcı kaydına (hash'lenmiş) yazar.
 * Dönüş: token (ham hali, link için) veya null (kullanıcı yoksa)
 */
function ki_sifirlama_tokeni_olustur(string $email): ?string
{
    $kullanicilar = ki_kullanicilari_oku();
    $email = mb_strtolower(trim($email));
    $idx = -1;
    foreach ($kullanicilar as $i => $k) {
        if (mb_strtolower($k['email'] ?? '') === $email) {
            $idx = $i;
            break;
        }
    }
    if ($idx < 0) return null;

    $token = bin2hex(random_bytes(32));
    $kullanicilar[$idx]['reset_hash']  = hash('sha256', $token);
    $kullanicilar[$idx]['reset_bitis'] = date('d.m.Y H:i:s', time() + KI_RESET_SURESI_DK * 60);
    ki_kullanicilari_yaz($kullanicilar);

    return $kullanicilar[$idx]['id'] . ':' . $token;
}

/** Token geçerli mi? Geçerliyse kullanıcı dizisini döner, değilse null. */
function ki_sifirlama_tokeni_dogrula(string $tokenParam): ?array
{
    [$id, $token] = array_pad(explode(':', $tokenParam, 2), 2, '');
    if ($id === '' || $token === '') return null;

    $kullanicilar = ki_kullanicilari_oku();
    $idx = ki_id_index($kullanicilar, $id);
    if ($idx < 0) return null;

    $k = $kullanicilar[$idx];
    if (empty($k['reset_hash']) || empty($k['reset_bitis'])) return null;
    if (strtotime($k['reset_bitis']) < time()) return null;
    if (!hash_equals($k['reset_hash'], hash('sha256', $token))) return null;

    return $k;
}

/** Token geçerliyse şifreyi günceller ve token'ı geçersizleştirir. */
function ki_sifre_sifirla(string $tokenParam, string $yeniSifre): array
{
    if (mb_strlen($yeniSifre) < 6) {
        return ['basarili' => false, 'mesaj' => 'Şifre en az 6 karakter olmalı.'];
    }

    [$id, $token] = array_pad(explode(':', $tokenParam, 2), 2, '');
    $kullanicilar = ki_kullanicilari_oku();
    $idx = ki_id_index($kullanicilar, $id);
    if ($idx < 0) {
        return ['basarili' => false, 'mesaj' => 'Geçersiz veya süresi dolmuş bağlantı.'];
    }

    $k = $kullanicilar[$idx];
    if (empty($k['reset_hash']) || empty($k['reset_bitis'])
        || strtotime($k['reset_bitis']) < time()
        || !hash_equals($k['reset_hash'], hash('sha256', $token))
    ) {
        return ['basarili' => false, 'mesaj' => 'Geçersiz veya süresi dolmuş bağlantı.'];
    }

    $kullanicilar[$idx]['sifre_hash']       = password_hash($yeniSifre, PASSWORD_DEFAULT);
    $kullanicilar[$idx]['reset_hash']       = null;
    $kullanicilar[$idx]['reset_bitis']      = null;
    $kullanicilar[$idx]['basarisiz_deneme'] = 0;
    $kullanicilar[$idx]['kilit_bitis']      = null;
    ki_kullanicilari_yaz($kullanicilar);

    return ['basarili' => true, 'mesaj' => 'Şifreniz güncellendi. Şimdi giriş yapabilirsiniz.'];
}

/**
 * Sıfırlama e-postası göndermeyi dener. Sunucuda SMTP/mail() yapılandırılı
 * değilse (yerel XAMPP gibi) sessizce başarısız olur — bu normaldir,
 * çağıran kod yine de test linkini ekranda gösterebilir.
 */
function ki_sifirlama_maili_gonder(string $email, string $link): bool
{
    $konu = 'Şifre Sıfırlama Talebi';
    $mesaj = "Merhaba,\n\nŞifrenizi sıfırlamak için aşağıdaki bağlantıya tıklayın:\n$link\n\nBu bağlantı " . KI_RESET_SURESI_DK . " dakika içinde geçerliliğini kaybedecektir.\n\nBu talebi siz yapmadıysanız bu e-postayı görmezden gelebilirsiniz.";
    $basliklar = 'Content-Type: text/plain; charset=UTF-8';

    return @mail($email, $konu, $mesaj, $basliklar);
}
