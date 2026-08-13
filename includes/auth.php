<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sisteme giriş yapılmış mı? (kullanici_islemleri.php'deki 'kullanici_id' yi arar)
function isLoggedIn() {
    return !empty($_SESSION['kullanici_id']);
}

// Giren kişi admin mi? (kullanici_islemleri.php'deki 'kullanici_rol' yi arar)
function isAdmin() {
    return !empty($_SESSION['kullanici_rol']) && $_SESSION['kullanici_rol'] === 'admin';
}
?>