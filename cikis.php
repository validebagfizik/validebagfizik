<?php
/**
 * cikis.php
 * Oturumu ve "beni hatırla" cookie'sini temizleyip giriş sayfasına yönlendirir.
 */
require_once __DIR__ . '/includes/kullanici_islemleri.php';
ki_cikis_yap();
header('Location: giris.php');
exit;
