<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

// Tema Ayarı
 $ayar_dosyasi = __DIR__ . '/../aktif_tema.txt';
if (!file_exists($ayar_dosyasi)) file_put_contents($ayar_dosyasi, 'warm-amber');
 $aktif_tema = trim(file_get_contents($ayar_dosyasi));
 $gecerli_temalar = ['warm-amber', 'deep-space', 'cyber-ocean', 'forest-matrix', 'midnight-cherry', 'arctic-frost'];
if (!in_array($aktif_tema, $gecerli_temalar)) $aktif_tema = 'warm-amber';

 $commentsPath = __DIR__ . '/../data/comments.json';
 $comments = [];
if (file_exists($commentsPath)) {
    $comments = json_decode(file_get_contents($commentsPath), true) ?: [];
}

// Onaylama veya Silme İşlemi
if (isset($_GET['islem']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $islem = $_GET['islem'];
    
    foreach ($comments as &$c) {
        if ($c['id'] === $id) {
            if ($islem === 'onayla') $c['status'] = 'approved';
            elseif ($islem === 'sil') $c = null; 
            break;
        }
    }
    unset($c);
    
    $comments = array_filter($comments);
    file_put_contents($commentsPath, json_encode(array_values($comments), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: yorumlar.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yorum Yönetimi — Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <link rel="stylesheet" href="../assets/css/editor.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .admin-table th, .admin-table td { padding: 16px 20px; vertical-align: middle; }
    </style>
</head>
<body class="theme-<?php echo htmlspecialchars($aktif_tema); ?>">
    <div class="admin-layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main" style="flex-grow: 1;">
            <header class="admin-topbar">
                <h1><i class="fa fa-comments"></i> Yorum Yönetimi</h1>
                <div>Admin: <strong><?php echo htmlspecialchars($_SESSION['kullanici_ad'] ?? 'Yönetici'); ?></strong></div>
            </header>

            <div class="admin-content">
                <div class="admin-card">
                    <div class="card-header">
                        <h2><i class="fa fa-list"></i> Tüm Yorumlar</h2>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($comments)): ?>
                            <div class="admin-empty" style="padding: 40px; text-align: center;">
                                <i class="fa fa-comment-slash" style="font-size: 2rem; opacity: 0.3; margin-bottom: 15px;"></i>
                                <p>Henüz hiç yorum yok.</p>
                            </div>
                        <?php else: ?>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Yorum Sahibi</th>
                                        <th>Yorum</th>
                                        <th>Sayfa</th>
                                        <th>Tarih</th>
                                        <th>Durum</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_reverse($comments) as $c): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($c['name'] ?? 'Ziyaretçi'); ?></strong></td>
                                            <td style="max-width: 400px;"><?php echo htmlspecialchars(mb_substr($c['text'] ?? '', 0, 150)); ?></td>
                                            <td><?php echo htmlspecialchars($c['page'] ?? 'Bilinmiyor'); ?></td>
                                            <td><?php echo htmlspecialchars($c['date'] ?? '-'); ?></td>
                                            <td>
                                                <?php if (($c['status'] ?? '') === 'approved'): ?>
                                                    <span class="status-badge status-approved">Onaylı</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-pending">Beklemede</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons" style="display: flex; gap: 8px;">
                                                    <?php if (($c['status'] ?? '') !== 'approved'): ?>
                                                        <a href="yorumlar.php?islem=onayla&id=<?php echo htmlspecialchars($c['id']); ?>" class="btn-action btn-approve" title="Onayla" style="background: rgba(34, 197, 94, 0.15); color: #22c55e; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none;"><i class="fa fa-check"></i></a>
                                                    <?php endif; ?>
                                                    <a href="yorumlar.php?islem=sil&id=<?php echo htmlspecialchars($c['id']); ?>" class="btn-action btn-delete" title="Sil" onclick="return confirm('Bu yorumu silmek istediğinize emin misiniz?')" style="background: rgba(239, 68, 68, 0.15); color: #ef4444; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none;"><i class="fa fa-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>