<?php
/**
 * admin/includes/auth.php
 * Tek Giriş Sistemi (giris.php ve giris.json) ile uyumlu admin kontrolcüsü.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Giren kişinin rolü admin mi?
 */
function isAdmin(): bool {
    return !empty($_SESSION['kullanici_rol']) && $_SESSION['kullanici_rol'] === 'admin';
}

/**
 * Admin sayfalarının en üstünde çağırılır. 
 * Giriş yapılmamışsa veya admin değilse ana giriş sayfasına atar.
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        $mevcutSayfa = basename($_SERVER['PHP_SELF']);
        header('Location: ../giris.php?yonlendir=admin/' . $mevcutSayfa);
        exit;
    }
}

/**
 * Admin oturumunu tamamen kapatır.
 */
function adminCikisYap(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path']);
    }
    session_destroy();
    header('Location: ../index.php');
    exit;
}
?>