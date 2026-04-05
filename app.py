from flask import Flask, Response
import os

app = Flask(__name__)

# En stabil TRT 1 linki
TRT1_LINK = "https://trt.daioncdn.net/trt-1/master.m3u8?app=web"

@app.route('/')
def home():
    return """
    <h1>TRT 1 Test</h1>
    <p>Bu sayfa açılıyorsa Flask çalışıyor demektir.</p>
    <p><a href="/trt1.m3u8">TRT 1 Yayınını Aç</a></p>
    """

@app.route('/trt1.m3u8')
def trt1():
    content = f"""#EXTM3U
#EXT-X-VERSION:3
#EXT-X-STREAM-INF:BANDWIDTH=4000000
{TRT1_LINK}
"""
    return Response(content, mimetype='application/vnd.apple.mpegurl')


if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host='0.0.0.0', port=port)
