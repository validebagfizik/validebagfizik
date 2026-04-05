from flask import Flask, Response, jsonify
import os
import asyncio
from playwright.async_api import async_playwright

app = Flask(__name__)

async def get_trt_link():
    async with async_playwright() as p:
        # Tarayıcıyı başlat (Render için gerekli ayarlar)
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        
        target_link = None

        # Sayfadaki ağ isteklerini dinle
        def handle_request(request):
            nonlocal target_link
            # Eğer istek içinde .m3u8 geçiyorsa ve ana yayınsa yakala
            if ".m3u8" in request.url and "master" in request.url:
                target_link = request.url

        page.on("request", handle_request)

        # TRT Canlı Yayın sayfasına git
        try:
            await page.goto("https://www.trtizle.com/canli/tv/trt-1", wait_until="networkidle", timeout=60000)
            # Sayfanın yüklenmesi ve linkin oluşması için 5 saniye bekle
            await asyncio.sleep(5)
        except Exception as e:
            print(f"Hata oluştu: {e}")
        finally:
            await browser.close()
        
        return target_link

@app.route('/trt1.m3u8')
def trt1():
    # Botu çalıştır ve linki al
    loop = asyncio.new_event_loop()
    asyncio.set_event_loop(loop)
    real_url = loop.run_until_complete(get_trt_link())
    
    if real_url:
        # IPTV oynatıcılar için m3u8 formatında dön
        m3u8_content = f"#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-STREAM-INF:BANDWIDTH=1280000\n{real_url}"
        return Response(m3u8_content, mimetype='application/x-mpegURL')
    else:
        return "Link bulunamadı, lütfen sayfayı yenileyin.", 404

@app.route('/')
def home():
    return "Bot Sistemi Aktif! TRT 1 için /trt1.m3u8 adresini kullanın."

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host='0.0.0.0', port=port)
