from flask import Flask, Response, request, redirect
import requests

app = Flask(__name__)

# Yayıncıyı ikna etmek için kullanılan standart başlıklar
HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36",
    "Referer": "https://www.trtizle.com/"
}

# TRT'nin yayın yaptığı ana sunucu adresi
BASE_URL = "https://tv-trt1.medya.trt.com.tr/"

@app.route('/')
def home():
    return """
    <h1>Validebag Relay Aktif</h1>
    <p>Google Chrome'da denediğin ve çalışan sistem bu.</p>
    <ul>
        <li><a href="/trt1.m3u8">TRT 1 Yayın Linki</a></li>
    </ul>
    """

# 1. ADIM: M3U8 Dosyasını düzenleyen ana fonksiyon
@app.route('/trt1.m3u8')
def play_trt():
    url = BASE_URL + "master.m3u8"
    try:
        r = requests.get(url, headers=HEADERS, timeout=10)
        content = r.text

        # Senin bulduğun dahi fikir: TRT adreslerini kendi Render adresine çeviriyoruz
        # Böylece oynatıcı her parçayı (segment) senin sunucundan ister
        proxy_url = request.host_url + "segment/"
        content = content.replace(BASE_URL, proxy_url)

        # Oynatıcı uyumluluğu için gerekli Content-Type ve CORS ayarları
        response = Response(content, content_type="application/vnd.apple.mpegurl")
        response.headers['Access-Control-Allow-Origin'] = '*'
        response.headers['Cache-Control'] = 'no-cache'
        return response
    except Exception as e:
        return f"M3U8 Hatasi: {str(e)}", 500

# 2. ADIM: Senin yazdığın, video parçalarını (TS) aktaran fonksiyon
@app.route('/segment/<path:path>')
def proxy_segment(path):
    # Oynatıcı senden parça istediğinde biz onu TRT'den alıp ona veriyoruz
    actual_url = BASE_URL + path
    
    try:
        # stream=True ile veriyi sunucuya kaydetmeden doğrudan 'akıtıyoruz'
        r = requests.get(actual_url, headers=HEADERS, stream=True, timeout=15)
        
        def generate():
            # 1MB'lık parçalar halinde okuyup gönderiyoruz (akıcılık için)
            for chunk in r.iter_content(chunk_size=1024*1024):
                yield chunk

        return Response(generate(), content_type=r.headers.get('content-type'), headers={
            'Access-Control-Allow-Origin': '*',
            'Cache-Control': 'public, max-age=3600'
        })
    except Exception as e:
        return f"Segment Hatasi: {str(e)}", 404

if __name__ == '__main__':
    # Render'ın port ayarı (10000 varsayılan porttur)
    app.run(host='0.0.0.0', port=10000)
