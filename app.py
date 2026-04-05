from flask import Flask, Response, request
import requests

app = Flask(__name__)

HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36",
    "Referer": "https://www.trtizle.com/"
}

BASE_URL = "https://tv-trt1.medya.trt.com.tr/"

@app.route('/')
def index():
    return "<h1>Validebag Relay Aktif</h1><p>Link: /trt1.m3u8</p>"

@app.route('/trt1.m3u8')
def play_trt():
    url = f"{BASE_URL}master.m3u8"
    try:
        r = requests.get(url, headers=HEADERS, timeout=10)
        content = r.text

        # Senin bulduğun dahi fikir: Linkleri kendi sunucuna yönlendiriyoruz
        # TRT'nin adreslerini silip yerine Render adresini + /segment/ yolunu koyuyoruz
        proxy_url = request.host_url + "segment/"
        content = content.replace(BASE_URL, proxy_url)

        return Response(content, content_type="application/vnd.apple.mpegurl")
    except Exception as e:
        return f"Hata: {str(e)}", 500

@app.route('/segment/<path:segment_path>')
def proxy_segment(segment_path):
    # Oynatıcı (VLC/HLSPlayer) bir parça istediğinde buraya gelir
    # Biz de o parçayı gidip TRT'den alıp kullanıcıya paketleyip gönderiyoruz
    actual_url = f"{BASE_URL}{segment_path}"
    
    try:
        # stream=True yaparak veriyi parça parça çekip aktarıyoruz (belleği yormaz)
        req = requests.get(actual_url, headers=HEADERS, stream=True, timeout=15)
        return Response(req.content, content_type=req.headers.get('content-type'), headers={
            'Access-Control-Allow-Origin': '*'
        })
    except:
        return "Segment hatasi", 404

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=10000)
