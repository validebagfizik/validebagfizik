/*
    assets/js/kayit-tekil.js
    ===================================================================
*/
document.addEventListener('DOMContentLoaded', () => {
    const canvas     = document.getElementById('word-canvas');
    const saveBtn    = document.getElementById('btn-save-page');
    const previewBtn = document.getElementById('btn-preview');

    if (!canvas) return;

    // --- HTML'İ BLOK JSON YAPISINA ÇEVİREN FONKSİYON ---
    function getBlockData(rootElement) {
        const blocks = [];
        const nodes = Array.from(rootElement.childNodes);
        
        nodes.forEach(node => {
            // Boş metin düğümlerini atla
            if (node.nodeType === Node.TEXT_NODE && node.textContent.trim() === '') return;
            
            const block = { uid: 'block_' + Date.now() + '_' + Math.floor(Math.random() * 10000) };
            
            if (node.nodeType === Node.ELEMENT_NODE) {
                // 1. BAŞLIKLAR (H1, H2, H3)
                if (['H1', 'H2', 'H3'].includes(node.tagName)) {
                    block.type = 'baslik';
                    block.content = { tag: node.tagName.toLowerCase(), text: node.innerText };
                } 
                // 2. SÜTUNLU YAPI (editor-row)
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
                // 3. TABLO
                else if (node.tagName === 'TABLE') {
                    block.type = 'tablo';
                    // SADECE text-align ve vertical-align'i koruyarak temizle
                    node.querySelectorAll('*').forEach(el => {
                        let tAlign = el.style.textAlign;
                        let vAlign = el.style.verticalAlign;
                        el.removeAttribute('style');
                        if (tAlign) el.style.textAlign = tAlign;
                        if (vAlign) el.style.verticalAlign = vAlign;
                    });
                    block.content = { html: node.outerHTML };
                }
                             // 4. DİĞER TÜM İÇERİKLER (Paragraflar, yazılar vb.)
                else {
                    block.type = 'paragraf';
                    // İçindeki stilleri temizle (AMA text-align'i koru)
                    node.querySelectorAll('*').forEach(el => {
                        if (el.tagName !== 'IMG') {
                            let tAlign = el.style.textAlign;
                            el.removeAttribute('style');
                            if (tAlign) el.style.textAlign = tAlign;
                        }
                    });
                    block.content = { html: node.outerHTML };
                }
            } else if (node.nodeType === Node.TEXT_NODE) {
                // Editöre dışarıdan kopyalanan düz yazılar
                block.type = 'paragraf';
                block.content = { html: `<p>${node.textContent}</p>` };
            }
            
            if (block.type) blocks.push(block);
        });
        
        return { page: blocks };
    }

    // KAYDET
    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            saveBtn.disabled = true;
            const eskiYazi = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Kaydediliyor...';

            // İÇERİĞİ BLOK JSON'A ÇEVİR
            const blockData = getBlockData(canvas);

            const formData = new FormData();
            formData.append('page', CURRENT_PAGE);
            // Artık "icerik" yerine "page" dizisi olarak gönderiyoruz
            formData.append('page_content', JSON.stringify(blockData));

            fetch('kaydet.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    saveBtn.innerHTML = '<i class="fa-solid fa-check me-2"></i> Kaydedildi!';
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
                        .paper {
                            max-width: 900px; margin: 0 auto; background:#fff;
                            padding:40px; border-radius:8px;
                            box-shadow:0 10px 25px rgba(0,0,0,0.08);
                            color:#202124; line-height:1.6;
                        }
                        /* Editör içindeki tablo ve sütun stillerinin önizlemede de görünmesi için */
                        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        .editor-row { display: flex; gap: 16px; margin: 16px 0; }
                        .editor-col { flex: 1; min-height: 120px; padding: 10px; border: 1px dashed #ccc; border-radius: 6px; background-color: #fafafa; }
                        .col-img-container img, .single-img-container img { max-width: 100%; height: auto; border-radius: 4px; }
                        .img-caption { display: block; font-size: 12px; color: #666; margin-top: 6px; text-align: center; font-style: italic; }
                    </style>
                </head>
                <body>
                    <div class="paper">${canvas.innerHTML}</div>
                </body>
                </html>
            `);
            pencere.document.close();
        });
    }
});