<?php
/*
    includes/blok_render.php
    ===================================================================
    admin panelde kaydedilen blok dizisini (uid/type/content) salt-okunur
    HTML'e çeviren PAYLAŞILAN fonksiyon. deney.php, soru.php, okuma.php,
    yonetim.php gibi TÜM public çoklu sayfalar bunu kullanır — mantık
    main.js'in tarayıcıda yaptığının PHP (sunucu tarafı) karşılığıdır.

    Yeni bir blok tipi eklenirse SADECE bu dosyaya dokunulur.
    ===================================================================
*/

function blokToHtml($block) {
    $tip = $block['type'] ?? '';
    $icerik = $block['content'] ?? [];

    if ($tip === 'baslik') {
        $tag = in_array($icerik['tag'] ?? '', ['h1', 'h2', 'h3'], true) ? $icerik['tag'] : 'h2';
        return "<{$tag}>" . htmlspecialchars($icerik['text'] ?? '') . "</{$tag}>";
    }

    if ($tip === 'paragraf' || $tip === 'tablo') {
        // Bu HTML zaten kayıt anında (kayit-tekil.js/kayit-coklu.js'deki
        // getBlockData) temizlenmiş olarak geldi — güvenilir kabul edilir.
        return $icerik['html'] ?? '';
    }

    if ($tip === 'sutunlu_yazi') {
        $uid = $block['uid'] ?? '';
        $layout = (int) ($icerik['layout_type'] ?? 0);
        $html = '<div class="editor-row" contenteditable="false">';
        for ($i = 1; $i <= $layout; $i++) {
            $hucreTipi = $icerik["cell_{$i}_type"] ?? '';
            if ($hucreTipi === 'image') {
                $imgYolu = $icerik["image_path_block_{$uid}_{$i}"] ?? '';
                $html .= '<div class="editor-col active-content"><div class="col-img-container">'
                       . '<img src="' . htmlspecialchars($imgYolu) . '" alt="Görsel">'
                       . '</div></div>';
            } else {
                $metin = $icerik["text_block_{$uid}_{$i}"] ?? '';
                $html .= '<div class="editor-col active-content"><div class="col-text-content">' . $metin . '</div></div>';
            }
        }
        $html .= '</div>';
        return $html;
    }

    return '';
}

function renderBloklar($bloklar) {
    if (!is_array($bloklar)) return '';
    $html = '';
    foreach ($bloklar as $block) {
        $html .= blokToHtml($block);
    }
    return $html;
}
