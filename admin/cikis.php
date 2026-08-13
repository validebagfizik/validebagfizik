<?php
/**
 * admin/cikis.php
 * Admin oturumunu kapatıp giriş sayfasına yönlendirir.
 */
require_once __DIR__ . '/includes/auth.php';
adminCikisYap();
header('Location: index.php');
exit;
