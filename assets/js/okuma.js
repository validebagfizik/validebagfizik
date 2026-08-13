document.addEventListener('DOMContentLoaded', () => {
    const commentMaddeIdEl = document.getElementById('comment-madde-id');
    const commentForm      = document.getElementById('comment-form');
    const commentsSection  = document.getElementById('comments-section');

    if (!commentsSection) return; // Yorum bölümü olmayan sayfa (liste görünümü)

    const maddeId = commentMaddeIdEl ? commentMaddeIdEl.value : commentsSection.dataset.maddeId;
    if (!maddeId) return;

    // yonetim.js ile birebir aynı liste (site genelinde tek bir kelime
    // listesi olması daha doğru olurdu, ama şimdilik yonetim.js'le
    // aynı tutuyorum ki davranış tutarlı olsun).
    const badWords = ["amk","aq","orospu","sik","piç","kahpe","ibne","yarrak","fuck","shit","bitch","mal","salak","aptal"];

    function escapeHtml(text) {
        return String(text).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[m]);
    }

    async function loadComments(id) {
        try {
            const response = await fetch('data/comments.json', { cache: 'no-store' });
            const comments = await response.json();
            const targetPage = 'okuma_' + id;
            const approved = comments
                .filter(c => c.page === targetPage && c.status === 'approved')
                .sort((a, b) => new Date(b.date) - new Date(a.date));

            let html = `<h3 class="yorumlar-title"><i class="fa fa-comments"></i> Yorumlar (${approved.length})</h3>`;
            if (approved.length === 0) {
                html += `<p class="yorumlar-empty" style="color:#777;">Bu okuma parçasına henüz onaylanmış yorum bulunmuyor.</p>`;
            } else {
                approved.forEach(comment => {
                    html += `
                        <div class="yorum-item" style="background:#f9f9f9; padding:12px; border-radius:6px; margin-bottom:10px; border-left:3px solid #c8f542;">
                            <div class="yorum-item-header" style="display:flex; justify-content:space-between; font-weight:bold; font-size:13px;">
                                <strong>${escapeHtml(comment.name || 'Anonim')}</strong>
                                <small style="color:#888;">${comment.date || ''}</small>
                            </div>
                            <p class="yorum-item-text" style="margin:5px 0 0 0; font-size:14px; color:#333;">${escapeHtml(comment.text)}</p>
                        </div>`;
                });
            }
            commentsSection.innerHTML = html;
        } catch (e) {
            commentsSection.innerHTML = `<p class="yorumlar-empty">Yorumlar yüklenemedi.</p>`;
        }
    }

    if (commentForm) {
        commentForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const text = document.getElementById('comment-text').value.trim();
            const name = document.getElementById('comment-name')?.value.trim() || 'Anonim';

            if (!text) return;
            if (badWords.some(w => text.toLowerCase().includes(w) || name.toLowerCase().includes(w))) {
                alert("Uygunsuz kelimeler tespit edildi. Lütfen kelimelerinizi düzeltin.");
                return;
            }
            try {
                const response = await fetch('add_comment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name: name,
                        text: text,
                        page: 'okuma_' + maddeId
                    })
                });
                const result = await response.json();
                if (result.success) {
                    alert('Yorumunuz onaya gönderildi. Admin onayladıktan sonra görünecektir.');
                    document.getElementById('comment-text').value = '';
                } else {
                    alert(result.message || 'Hata oluştu.');
                }
            } catch (err) {
                alert('Yorum gönderilemedi.');
            }
        });
    }

    loadComments(maddeId);
});
