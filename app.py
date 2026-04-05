from flask import Flask, Response
import os

app = Flask(__name__)

# Şu anda en stabil çalışan TRT 1 linki (Nisan 2026)
TRT1_LINK = "https://trt.daioncdn.net/trt-1/master.m3u8?app=web"

@app.route('/trt1.m3u8')
def trt1():
    content = f"""#EXTM3U
#EXT-X-VERSION:3
#EXT-X-STREAM-INF:BANDWIDTH=6000000,RESOLUTION=1920x1080,CODECS="avc1.640028,mp4a.40.2"
{TRT1_LINK}
"""
    return Response(content, mimetype='application/vnd.apple.mpegurl')


@app.route('/')
def home():
    return """
    <h1>✅ TRT 1 Proxy (Render)</h1>
    <p><strong>Doğrudan açılacak link:</strong><br>
    <code>https://validebagfizik.onrender.com/trt1.m3u8</code></p>
    
    <h2>Nasıl izlenir?</h2>
    <ul>
        <li><strong>VLC Media Player</strong> → Media → Open Network Stream → linki yapıştır</li>
        <li>Chrome'da direkt açılmaz (CORS yüzünden)</li>
        <li>Windows Media Player desteklemez</li>
    </ul>
    <p>Denemek için <a href="/trt1.m3u8" target="_blank">/trt1.m3u8</a> tıklayın (VLC'de açın).</p>
    """

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host='0.0.0.0', port=port)
