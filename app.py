from flask import Flask, Response, request
import requests

app = Flask(__name__)

# TRT'nin kabul ettiği standart başlıklar
HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36",
    "Referer": "https://www.trtizle.com/"
}

BASE_URL = "https://tv-trt1.medya.trt.com.tr/"

@app.route('/')
def home():
    return "<h1>Validebag Relay Sunucusu Aktif</h1><p>Yayin Adresi: /trt1.m3u8</p>"

# 1. ADIM: M3U8 Dosyasını alıp içindeki linkleri kendi sunucuna çeviren kısım
@app.route('/trt1.m3u8')
def play_trt():
    url = BASE_URL + "master.m3u8"
    try:
        r = requests.get(url, headers=HEADERS, timeout=10)
        content = r.text

        # TRT linklerini kendi sunucuna (/segment/) yönlendiriyoruz
        # request.host_url otomatik olarak 'https://validebagfizik.onrender.com/' olur
        content = content.replace(BASE_URL, request.host_url + "segment/")

        return Response(content, content_type="application/vnd.apple.mpegurl")
    except Exception as e:
        return f"M3U8 Hatasi: {str(e)}", 500

# 2. ADIM: Senin yazdığın, video parçalarını (TS) TRT'den çekip ileten kısım
@app.route('/segment/<path:path>')
def proxy_segment(path):
    # Oynatıcı /segment/video123.ts istediğinde bunu gerçek TRT linkine çeviriyoruz
    url = BASE_URL + path
    
    try:
        # stream=True ve iter_content ile veriyi 'boru hattı' gibi aktarıyoruz
        r = requests.get(url, headers=HEADERS, stream=True, timeout=15)
        
        def generate():
            for chunk in r.iter_content(chunk_size=1024*1024): # 1MB'lık parçalarla akış
                yield chunk

        return Response(generate(), content_type="video/mp2t", headers={
            'Access-Control-Allow-Origin': '*' # Tarayıcı engellerini aşmak için şart
        })
    except Exception as e:
        return f"Segment Hatasi: {str(e)}", 404

if __name__ == '__main__':
    # Render'ın port ayarı
    app.run(host='0.0.0.0', port=10000)
