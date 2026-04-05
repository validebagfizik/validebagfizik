from flask import Flask, Response
import os
import asyncio
from playwright.async_api import async_playwright

app = Flask(__name__)

async def get_trt_link():
    # Render'da tarayıcıyı daha hızlı açmak için ayarlar
    async with async_playwright() as p:
        browser = await p.chromium.launch(
            headless=True,
            args=["--no-sandbox", "--disable-setuid-sandbox", "--disable-dev-shm-usage"]
        )
        context = await browser.new_context(user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36")
        page = await context.new_page()
        
        target_link = None

        def handle_request(request):
            nonlocal target_link
            if ".m3u8" in request.url and ("master" in request.url or "index" in request.url):
                target_link = request.url

        page.on("request", handle_request)

        try:
            # TRT sayfasını aç ve biraz bekle
            await page.goto("https://www.trtizle.com/canli/tv/trt-1", timeout=60000)
            # Linkin yakalanması için kısa bir süre bekleyelim
            for _ in range(10):
                if target_link: break
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
            # IPTV uyumlu format
            m3u8_content = f"#EXTM3U\n#EXT-X-VERSION:3\n{real_url}"
            return Response(m3u8_content, mimetype='application/x-mpegURL')
        else:
            return "Link yakalanamadı, tekrar deneyin.", 503
    except Exception as e:
        return f"Sunucu Hatası: {str(e)}", 500

@app.route('/')
def home():
    return "Bot Yayında! TRT 1 için /trt1.m3u8 adresine gidin."

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host='0.0.0.0', port=port)
