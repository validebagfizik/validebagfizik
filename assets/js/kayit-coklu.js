/*
    assets/js/kayit-coklu.js
    ===================================================================
    TÜM çoklu kayıt sayfalarında (meb, soru, deney, okuma...) ortak
    kullanılır. Sayfaya özel hiçbir şey yazılmaz — CURRENT_PAGE her
    admin dosyasında zaten otomatik tanımlı geliyor (dosya adından).
    ===================================================================
*/

// popup açma/kapama — Bootstrap yok, kendi CSS/JS'imiz (.active class'ı).
function yeniBaglamAc() {
    const modalEl = document.getElementById('yeniBaglamModal');
    if (!modalEl) return;
    document.getElementById('baglam-baslik-formu')?.reset();
    modalEl.classList.add('active');
}

function baglamKapat() {
    document.getElementById('yeniBaglamModal')?.classList.remove('active');
}

// Arka plana (overlay) tıklanınca da kapansın
document.addEventListener('click', (e) => {
    if (e.target && e.target.id === 'yeniBaglamModal') {
        baglamKapat();
    }
});

// Esc tuşuyla kapatma
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') baglamKapat();
});

// modal.php'deki "Tamam" butonu bunu çağırır: onclick="popupTamam('<?=pageType?>')"
function popupTamam(pageType) {
    const konu   = document.getElementById('popup-konu')?.value.trim();
    const baslik = document.getElementById('popup-baslik')?.value.trim();

    if (!konu || !baslik) {
        alert('Lütfen zorunlu alanları doldurun.');
        return;
    }

    const formData = new FormData();
    formData.append('page', CURRENT_PAGE);
    formData.append('edit_id', ''); // boş = yeni kayıt oluştur
    formData.append('meta_konu', konu);
    formData.append('meta_baslik', baslik);

    // Bu sayfaya özel TÜM alanları OTOMATİK topla — modal.php bunları
    // OZEL_ALAN_ANAHTARLARI ile bildiriyor, elle sinif/dogru/yazar diye
    // tek tek kontrol etmeye gerek yok. Yeni bir alan eklendiğinde bu
    // satıra dokunulmaz, alan_tanimlari.php'den otomatik gelir.
    (window.OZEL_ALAN_ANAHTARLARI || []).forEach(anahtar => {
        const el = document.getElementById('popup-' + anahtar);
        if (el) formData.append('meta_' + anahtar, el.value);
    });

    // Yeni kayıt boş bir blok dizisiyle oluşturulur, sonra düzenleme
    // ekranında kullanıcı yazacak.
    formData.append('page_content', JSON.stringify({ page: [] }));

    fetch('kaydet.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.new_id) {
                // Yeni kaydın düzenleme ekranına geç — sayfa tazelenir,
                // toolbar + boş kanvas bu sefer gerçekten yüklenir.
                window.location.href = CURRENT_PAGE + '.php?duzenle=' + encodeURIComponent(data.new_id);
            } else {
                alert('Hata: ' + (data.message || 'Kayıt oluşturulamadı.'));
            }
        })
        .catch(err => {
            console.error('Kayıt hatası:', err);
            alert('Sunucuyla bağlantı kurulamadı.');
        });
}

// KAPAK GÖRSELİ — 'kapak' tipi alanı olan HERHANGİ bir çoklu sayfada
// çalışır (alan_tanimlari.php'de 'type' => 'kapak' yazan her alan için).
// Sayfa artık kendi script'ini yazmıyor, bu tek yer yeterli.
function kapakSec() {
    window.open('galeri.php?hedef=' + CURRENT_PAGE + '&tip=kapak', 'galeriPenceresi', 'width=900,height=700');
}
window.kapakResmiAl = function (dosyaYolu) {
    const gizli = document.getElementById('meta_kapak');
    const onizleme = document.getElementById('kapakOnizleme');
    const metin = document.getElementById('kapakSecMetni');
    if (gizli) gizli.value = dosyaYolu.replace(/^\.\.\//, '');
    if (onizleme) { onizleme.src = dosyaYolu; onizleme.style.display = 'block'; }
    if (metin) metin.style.display = 'none';
};

function popupKapakSec() {
    window.open('galeri.php?hedef=' + CURRENT_PAGE + '&tip=kapak_popup', 'galeriPenceresi', 'width=900,height=700');
}
window.popupKapakResmiAl = function (dosyaYolu) {
    const gizli = document.getElementById('popup-kapak');
    const onizleme = document.getElementById('popupKapakOnizleme');
    const metin = document.getElementById('popupKapakSecMetni');
    if (gizli) gizli.value = dosyaYolu.replace(/^\.\.\//, '');
    if (onizleme) { onizleme.src = dosyaYolu; onizleme.style.display = 'block'; }
    if (metin) metin.style.display = 'none';
};

// --- HTML'İ BLOK JSON YAPISINA ÇEVİREN FONKSİYON ---
// kayit-tekil.js'deki ile birebir aynı mantık — tutarlılık için.
function getBlockData(rootElement) {
    const blocks = [];
    const nodes = Array.from(rootElement.childNodes);

    nodes.forEach(node => {
        if (node.nodeType === Node.TEXT_NODE && node.textContent.trim() === '') return;

        const block = { uid: 'block_' + Date.now() + '_' + Math.floor(Math.random() * 10000) };

        if (node.nodeType === Node.ELEMENT_NODE) {
            if (['H1', 'H2', 'H3'].includes(node.tagName)) {
                block.type = 'baslik';
                block.content = { tag: node.tagName.toLowerCase(), text: node.innerText };
            }
            else if (node.classList.contains('editor-row')) {
                block.type = 'sutunlu_yazi';
                const cols = node.querySelectorAll(':scope > .editor-col');
                block.content = { layout_type: String(cols.length) };

                cols.forEach((col, index) => {
                    const i = index + 1;
                    const img = col.querySelector('img');
                    const text = col.querySelector('.col-text-content');

                    if (img) {
                        block.content[`cell_${i}_type`] = 'image';
                        block.content[`image_path_block_${block.uid}_${i}`] = img.getAttribute('src');
                        block.content[`text_block_${block.uid}_${i}`] = "";
                    } else if (text) {
                        block.content[`cell_${i}_type`] = 'text';
                        block.content[`text_block_${block.uid}_${i}`] = text.innerHTML;
                        block.content[`image_path_block_${block.uid}_${i}`] = "";
                    }
                });
            }
            else if (node.tagName === 'TABLE') {
                block.type = 'tablo';
                node.querySelectorAll('*').forEach(el => el.removeAttribute('style'));
                block.content = { html: node.outerHTML };
            }
            else {
                block.type = 'paragraf';
                node.querySelectorAll('*').forEach(el => {
                    if (el.tagName !== 'IMG') el.removeAttribute('style');
                });
                block.content = { html: node.outerHTML };
            }
        } else if (node.nodeType === Node.TEXT_NODE) {
            block.type = 'paragraf';
            block.content = { html: `<p>${node.textContent}</p>` };
        }

        if (block.type) blocks.push(block);
    });

    return { page: blocks };
}

document.addEventListener('DOMContentLoaded', () => {
    const canvas     = document.getElementById('word-canvas');
    const saveBtn    = document.getElementById('btn-save-page');
    const previewBtn = document.getElementById('btn-preview');
    const editIdEl   = document.getElementById('edit_id');

    if (!canvas) return;

    // KAYDET / GÜNCELLE
    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            saveBtn.disabled = true;
            const eskiYazi = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Kaydediliyor...';

            const formData = new FormData();
            formData.append('page', CURRENT_PAGE);
            formData.append('edit_id', editIdEl ? editIdEl.value : '');

            // Sayfadaki TÜM meta_* alanlarını otomatik topla (meta_konu,
            // meta_baslik, meta_yazar, meta_kapak, ne varsa) — yeni bir
            // özel alan eklediğinde bu satıra dokunmana gerek kalmaz.
            document.querySelectorAll('[name^="meta_"]').forEach(el => {
                formData.append(el.name, el.value.trim ? el.value.trim() : el.value);
            });

            const temizIcerik = getBlockData(canvas);
            formData.append('page_content', JSON.stringify(temizIcerik));

            fetch('kaydet.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        saveBtn.innerHTML = '<i class="fa-solid fa-check me-2"></i> Kaydedildi!';
                        // Yeni oluşturulan kayıtta edit_id boşsa doldur (URL'yi de güncelle)
                        if (editIdEl && !editIdEl.value && data.new_id) {
                            editIdEl.value = data.new_id;
                            history.replaceState(null, '', CURRENT_PAGE + '.php?duzenle=' + encodeURIComponent(data.new_id));
                        }
                        setTimeout(() => {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = eskiYazi;
                        }, 1800);
                    } else {
                        alert('Hata: ' + (data.message || 'Kaydedilemedi.'));
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = eskiYazi;
                    }
                })
                .catch(err => {
                    console.error('Kayıt hatası:', err);
                    alert('Sunucuyla bağlantı kurulamadı.');
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = eskiYazi;
                });
        });
    }

    // ÖNGÖRÜNÜM
    if (previewBtn) {
        previewBtn.addEventListener('click', () => {
            const pencere = window.open('', '_blank');
            pencere.document.write(`
                <!DOCTYPE html>
                <html lang="tr">
                <head>
                    <meta charset="UTF-8">
                    <title>Öngörünüm</title>
                    <style>
                        body { background:#f3f4f6; margin:0; padding:40px 20px; font-family:'Segoe UI',Tahoma,Verdana,sans-serif; }
                        .paper { max-width:900px; margin:0 auto; background:#fff; padding:40px; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.08); color:#202124; line-height:1.6; }
                    </style>
                </head>
                <body><div class="paper">${canvas.innerHTML}</div></body>
                </html>
            `);
            pencere.document.close();
        });
    }
});
