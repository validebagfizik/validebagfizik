from flask import Flask, Response
import os
import asyncio
from playwright.async_api import async_playwright

app = Flask(__name__)

async def get_trt_live_url():
    async with async_playwright() as p:
        # Render'ın kısıtlı kaynakları için en hafif tarayıcı ayarları
        browser = await p.chromium.launch(headless=True, args=["--no-sandbox", "--disable-gpu"])
        page = await browser.new_page()
        
        found_url = None

        # Ağ trafiğini dinle ve gerçek m3u8 linkini yakala
        def log_request(request):
            nonlocal found_url
            # TRT'nin gerçek yayın linkleri genellikle 'master.m3u8' içerir
            if ".m3u8" in request.url and "master" in request.url:
                found_url = request.url

        page.on("request", log_request)

        try:
            # TRT 1 Canlı Yayın sayfasına git
            await page.goto("https://www.trtizle.com/canli/tv/trt-1", wait_until="networkidle", timeout=60000)
            # Linkin düşmesi için 5 saniye bekle
            await asyncio.sleep(5)
        except:
            pass
        finally:
            await browser.close()
        
        return found_url

@app.route('/trt1.m3u8')
def trt1():
    loop = asyncio.new_event_loop()
    asyncio.set_event_loop(loop)
    # Bot gidip gerçek linki buluyor
    real_link = loop.run_until_complete(get_trt_live_url())

    if real_link:
        # Gerçek link bulunduysa IPTV formatında dön
        m3u8_content = f"#EXTM3U\n#EXT-X-VERSION:3\n{real_link}"
        return Response(m3u8_content, mimetype='application/x-mpegURL')
    else:
        # Eğer bot linki bulamazsa hata ver
        return "Yayın linki şu an yakalanamadı, lütfen sayfayı yenileyin.", 503

@app.route('/')
def home():
    return "<h1>Bot Sistemi Aktif!</h1><p>Yayın için: <b>/trt1.m3u8</b> adresini kullanın.</p>"

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host='0.0.0.0', port=port)
