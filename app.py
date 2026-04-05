from flask import Flask, Response
import os
import requests
import re

app = Flask(__name__)

# Güncel TRT 1 master linkleri (2026 itibarıyla çalışanlardan en stabil olanlar)
TRT1_LINKS = [
    "https://trt.daioncdn.net/trt-1/master.m3u8?app=web",
    "https://tv-trt1.live.trt.com.tr/master.m3u8",
    "https://tv-trt1.medya.trt.com.tr/master.m3u8",
]

def get_trt1_link():
    """Öncelikle sabit çalışan linkleri dene, hiçbiri olmazsa scraping dene"""
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
                      "(KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36"
    }

    # 1. Adım: Sabit linkleri test et (en hızlı ve güvenilir yöntem)
    for link in TRT1_LINKS:
        try:
            # HEAD isteği ile linkin canlı olup olmadığını hızlıca kontrol et
            r = requests.head(link, headers=headers, timeout=8, allow_redirects=True)
            if r.status_code in (200, 302, 301):
                print(f"✅ Çalışan link bulundu: {link}")
                return link
        except:
            continue

    # 2. Adım: trtizle.com üzerinden scraping (yedek - eskisi gibi çalışmayabilir)
    try:
        url = "https://www.trtizle.com/canli/tv/trt-1"
        response = requests.get(url, headers=headers, timeout=12)
        response.raise_for_status()

        # Daha geniş ve güncel regex (birçok varyasyonu yakalar)
        patterns = [
            r'(https?://[^\s"\'<>`]+\.m3u8[^\s"\'<>`]*)',
            r'["\']([^"\']+\.m3u8[^"\']*)["\']',
            r'(https?://[^\s"\'\\]+trt-?1[^\s"\'\\]*\.m3u8)'
        ]

        for pattern in patterns:
            match = re.search(pattern, response.text, re.IGNORECASE)
            if match:
                found = match.group(1)
                # Temizle
                found = found.replace("\\u002F", "/").replace("\\/", "/")
                if found.startswith("http"):
                    print(f"✅ Scraping ile bulundu: {found}")
                    return found
    except Exception as e:
        print(f"Scraping hatası: {e}")

    # Hiçbiri çalışmazsa None dön (fallback yok, hata görelim)
    return None


@app.route('/trt1.m3u8')
def trt1():
    real_link = get_trt1_link()

    if real_link:
        # Basit ama çalışan playlist
        content = f"""#EXTM3U
#EXT-X-VERSION:3
#EXT-X-STREAM-INF:BANDWIDTH=3000000,RESOLUTION=1280x720
{real_link}
"""
        return Response(content, mimetype='application/vnd.apple.mpegurl')
    else:
        # Hiçbir link çalışmıyorsa hata mesajı ver (debug için faydalı)
        error_msg = "#EXTM3U\n# TRT 1 linki şu anda bulunamadı. Lütfen kodu güncelleyin."
        return Response(error_msg, mimetype='application/vnd.apple.mpegurl', status=503)


@app.route('/')
def home():
    return """
    <h1>TRT 1 Proxy</h1>
    <p>Yayın adresi: <strong>/trt1.m3u8</strong></p>
    <p>VLC, Kodi, IPTV Smarters vs. ile doğrudan açabilirsiniz.</p>
    """


if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host='0.0.0.0', port=port, debug=False)
