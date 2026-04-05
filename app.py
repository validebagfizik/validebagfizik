from flask import Flask, Response, request
import requests

app = Flask(__name__)

HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36",
    "Referer": "https://www.trtizle.com/"
}

# TRT'nin ana yayın merkezi
BASE_URL = "https://tv-trt1.medya.trt.com.tr/"

@app.route('/')
def home():
    return "<h1>Validebag Sunucu Aktif</h1><p>VLC veya Player icin link: /trt1.m3u8</p>"

@app.route('/trt1.m3u8')
@app.route('/segment/<path:path>')
def proxy(path=None):
    # Eğer path yoksa ana dosyayı (master.m3u8) istiyoruz demektir
    target_url = BASE_URL + (path if path else "master.m3u8")
    
    try:
        r = requests.get(target_url, headers=HEADERS, stream=True, timeout=15)
        
        # Eğer gelen dosya bir liste (m3u8) ise içindeki linkleri bozmadan kendimize çekiyoruz
        if ".m3u8" in target_url:
            lines = r.text.split('\n')
            new_lines = []
            for line in lines:
                # Satır boş değilse ve yorum satırı (#) değilse, linktir
                if line and not line.startswith('#'):
                    # Göreli (relative) linkleri tam linke çevirip kendi sunucumuza yönlendiriyoruz
                    if not line.startswith('http'):
                        line = request.host_url + "segment/" + line
                    else:
                        line = line.replace(BASE_URL, request.host_url + "segment/")
                new_lines.append(line)
            
            return Response('\n'.join(new_lines), content_type="application/vnd.apple.mpegurl")

        # Eğer gelen dosya bir video parçası (ts) ise doğrudan akıtıyoruz
        return Response(r.content, content_type=r.headers.get('content-type'))
        
    except Exception as e:
        return f"Hata: {str(e)}", 404

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=10000)
