from flask import Flask, Response, request
import requests
from flask_cors import CORS

app = Flask(__name__)
CORS(app)  # Tüm rotalara CORS izni (gerekli olabilir)

# TRT kanallarının güncel base linkleri (sık değişebilir)
TRT_STREAMS = {
    "trt1": "https://tv-trt1.mediaset.net/hls/trt1.m3u8",   # örnek, gerçek linki kendin kontrol et
    # diğer kanallar için buraya ekle
}

@app.route('/<channel>.m3u8')
def proxy_m3u8(channel):
    if channel not in TRT_STREAMS:
        return "Channel not found", 404

    real_url = TRT_STREAMS[channel]

    try:
        headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
            "Referer": "https://www.trtizle.com/",   # TRT bazen referer ister
            "Origin": "https://www.trtizle.com"
        }

        r = requests.get(real_url, headers=headers, stream=True, timeout=15)
        r.raise_for_status()

        def generate():
            for chunk in r.iter_content(chunk_size=8192):
                if chunk:
                    yield chunk

        return Response(
            generate(),
            content_type=r.headers.get('Content-Type', 'application/vnd.apple.mpegurl'),
            headers={
                "Access-Control-Allow-Origin": "*",
                "Access-Control-Allow-Methods": "GET",
                "Cache-Control": "no-cache"
            }
        )

    except requests.exceptions.RequestException as e:
        return f"Stream error: {str(e)}", 502


# Keep-alive için basit ping endpoint'i (çok önemli!)
@app.route('/ping')
def ping():
    return "OK", 200


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=10000)   # Render genellikle 10000 ister
