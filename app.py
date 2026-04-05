from flask import Flask, Response
import os
import requests

app = Flask(__name__)

# Şu anda en iyi çalışan TRT 1 linkleri (sırayla dener)
TRT1_LINKS = [
    "https://trt.daioncdn.net/trt-1/master.m3u8?app=web",     # En stabil (2025-2026)
    "https://trt.daioncdn.net/trt-1/master.m3u8?app=clean",
    "https://tv-trt1.medya.trt.com.tr/master.m3u8",
    "https://tv-trt1.live.trt.com.tr/master.m3u8",
]

def get_working_trt1_link():
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
                      "(KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36"
    }

    for link in TRT1_LINKS:
        try:
            # Sadece HEAD isteğiyle hızlı kontrol ediyoruz
            r = requests.head(link, headers=headers, timeout=10, allow_redirects=True)
            if r.status_code in (200, 302, 301):
                print(f"✅ Çalışan link: {link}")
                return link
        except Exception as e:
            print(f"❌ {link} çalışmadı → {e}")
            continue

    print("⚠️ Hiçbir TRT 1 linki çalışmadı!")
    return None


@app.route('/trt1.m3u8')
def trt1():
    real_link = get_working_trt1_link()

    if real_link:
        # Basit ve uyumlu m3u8 playlist
        content = f"""#EXTM3U
#EXT-X-VERSION:3
#EXT-X-STREAM-INF:BANDWIDTH=4000000,RESOLUTION=1920x1080,CODECS="avc1.640028,mp4a.40.2"
{real_link}
"""
        return Response(content, mimetype='application/vnd.apple.mpegurl')
    else:
        # Hiçbiri çalışmazsa net hata mesajı
        error_content = "#EXTM3U\n# TRT 1 şu anda çalışmıyor. Linkler değişmiş olabilir."
        return Response(error_content, mimetype='application/vnd.apple.mpegurl', status=503)


@app.route('/')
def home():
    return """
    <h1>✅ TRT 1 Proxy Çalışıyor</h1>
    <p><strong>Yayın Linki:</strong> <code>/trt1.m3u8</code></p>
    <p>VLC, Kodi, GSE Smart IPTV, IPTV Smarters veya herhangi bir m3u8 destekleyen oynatıcıyla açabilirsiniz.</p>
    """


if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host='0.0.0.0', port=port)
