from flask import Flask, Response
import os
import asyncio
from playwright.async_api import async_playwright

app = Flask(__name__)

async def get_trt_link():
    async with async_playwright() as p:
        # Render için optimize edilmiş tarayıcı ayarları
        browser = await p.chromium.launch(headless=True, args=["--no-sandbox"])
        context = await browser.new_context(user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36")
        page = await context.new_page()
        
        target_link = None

        # Gelen her ağ isteğini kontrol et
        def handle_request(request):
            nonlocal target_link
            url = request.url
            # TRT'nin ana yayın linkleri genelde 'master.m3u8' veya 'index.m3u8' içerir
            if ".m3u8" in url and ("master" in url or "index" in url) and "http" in url:
                target_link = url

        page.on("request", handle_request)

        try:
            # TRT Canlı Yayın sayfasına git
            await page.goto("https://www.trtizle.com/canli/tv/trt-1", wait_until="commit", timeout=60000)
            
            # Linkin yakalanması için max 15 saniye bekle
            for _ in range(15):
                if target_link:
                    break
                await asyncio.sleep(1)
                
        except Exception as e:
            print(f"Bot Hatası: {e}")
        finally:
            await browser.close()
        
        return target_link

@app.route('/trt1.m3u8')
def trt1():
    try:
        loop = asyncio.new_event_loop()
        asyncio.set_event_loop(loop)
        real_url = loop.run_until_complete(get_trt_link())
        
        if real_url:
            # IPTV oynatıcılar için doğru format
            m3u8_content = f"#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-STREAM-INF:BANDWIDTH=1280000\n{real_url}"
            return Response(m3u8_content, mimetype='application/x-mpegURL')
        else:
            return "Link yakalanamadı. Lütfen 10 saniye sonra tekrar deneyin.", 404
    except Exception as e:
        return f"Sistem Hatası: {str(e)}", 500

@app.route('/')
def home():
    return "<h1>Bot Aktif!</h1><p>Yayın için: <b>/trt1.m3u8</b></p>"

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host='0.0.0.0', port=port)
