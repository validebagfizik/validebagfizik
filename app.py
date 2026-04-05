from flask import Flask, Response
import os

app = Flask(__name__)

# 2026 itibarıyla en stabil çalışan TRT 1 linki
TRT1_MAIN_LINK = "https://trt.daioncdn.net/trt-1/master.m3u8?app=web"

@app.route('/trt1.m3u8')
def trt1():
    # Direkt en iyi linki veriyoruz (Render'da test etmek sorun çıkarıyorsa bypass)
    content = f"""#EXTM3U
#EXT-X-VERSION:3
#EXT-X-STREAM-INF:BANDWIDTH=5000000,RESOLUTION=1920x1080,CODECS="avc1.640028,mp4a.40.2"
{TRT1_MAIN_LINK}
"""
    return Response(content, mimetype='application/vnd.apple.mpegurl')


@app.route('/')
def home():
    return """
    <h1>TRT 1 Proxy - Render Üzerinde</h1>
    <p><strong>Yayın Linki:</strong> <code>https://validebagfizik.onrender.com/trt1.m3u8</code></p>
    <p>VLC, Kodi, GSE IPTV veya herhangi bir m3u8 oynatıcı ile açmayı dene.</p>
    <p><small>Not: Render ücretsiz planda bazen yavaş açılabilir.</small></p>
    """


if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host='0.0.0.0', port=port)
