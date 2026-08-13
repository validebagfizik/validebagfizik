<?php
// Dosya yolu
 $jsonYolu = __DIR__ . '/data/giris.json';
 $yeniSifre = '1234'; // Sisteme giriş yaparken bu şifreyi kullanacaksınız

if (file_exists($jsonYolu)) {
    $kullanicilar = json_decode(file_get_contents($jsonYolu), true);
    $bulundu = false;

    foreach ($kullanicilar as &$kullanici) {
        // E-posta adresini kontrol et
        if ($kullanici['email'] === 'demo@fizikplatformu.com') {
            // Yeni şifreyi güvenli hale getir (hash'le)
            $kullanici['sifre_hash'] = password_hash($yeniSifre, PASSWORD_DEFAULT);
            // Hesap kilitliyse aç
            $kullanici['basarisiz_deneme'] = 0;
            $kullanici['kilit_bitis'] = null;
            $bulundu = true;
            break;
        }
    }
    unset($kullanici);

    if ($bulundu) {
        // Dosyayı yeniden kaydet
        file_put_contents($jsonYolu, json_encode($kullanicilar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        echo "<h2>İşlem Başarılı! 🎉</h2>";
        echo "<p><strong>demo@fizikplatformu.com</strong> mail adresinin şifresi başarıyla <strong>1234</strong> olarak güncellendi.</p>";
        echo "<p>Hesabın kilidi açıldı. Şimdi giriş sayfasına gidip bu bilgilerle sisteme girebilirsiniz.</p>";
        echo "<p><strong>Uyarı:</strong> Giriş yaptıktan sonra bu dosyayı (sifre-sifirla.php) sunucudan silin!</p>";
    } else {
        echo "<h2>Hata!</h2> <p>Sistemde 'demo@fizikplatformu.com' e-postasına sahip kullanıcı bulunamadı.</p>";
    }
} else {
    echo "<h2>Hata!</h2> <p>data/giris.json dosyası bulunamadı. Klasör yolunu kontrol edin.</p>";
}
?>