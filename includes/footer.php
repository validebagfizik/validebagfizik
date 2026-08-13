<?php
// includes/footer.php
?>
<?php if (!$isHome): ?>
</div><!-- #site-content kapat -->
<?php endif; ?>

<!-- Footer -->
<footer class="site-footer">
    <p class="footer-tagline">evrenin kurallarını keşfet</p>
    <div class="footer-bottom">
        <span>© <?= date('Y') ?> Fizik Platformu</span>
        <span>Tüm hakları saklıdır.</span>
    </div>
</footer>
</div><!-- .page-shell kapat -->

<?php if ($currentPage !== 'yonetim'): ?>
<script>const CURRENT_PAGE = '<?= basename($_SERVER['SCRIPT_NAME'], '.php') ?>';</script>

<?php endif; ?>

<!-- Bootstrap JS (Dropdown menüler için şart) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!-- RESİM BÜYÜTME (LIGHTBOX) ÖZELLİĞİ -->
<div id="img-lightbox" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.9); z-index:9999; justify-content:center; align-items:center; cursor:zoom-out;">
    <img src="" id="lightbox-img" style="max-width:90%; max-height:90%; border-radius:8px; box-shadow:0 0 20px rgba(0,0,0,0.5);">
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lightbox = document.getElementById('img-lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    
    // Sadece içerik alanlarındaki resimleri yakala (Yorumlar veya formlar hariç)
    const contentAreas = document.querySelectorAll('.reading-content, .soru-rehber, .site-block');
    
    contentAreas.forEach(area => {
        area.addEventListener('click', function(e) {
            if (e.target.tagName === 'IMG' && !e.target.closest('.img-resize-overlay')) {
                lightboxImg.src = e.target.src;
                lightbox.style.display = 'flex';
            }
        });
    });

    // Büyüyen resme veya arka plana tıklanınca kapat
    lightbox.addEventListener('click', function() {
        lightbox.style.display = 'none';
    });

    // ESC tuşu ile kapat
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') lightbox.style.display = 'none';
    });
});
</script>