from flask import Flask, Response, request
import requests

app = Flask(__name__)

HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36",
    "Referer": "https://www.trtizle.com/"
}

# TRT ana sunucusu
BASE_URL = "https://tv-trt1.medya.trt.com.tr/"

@app.route('/')
def home():
    return "<h1>Sistem Yenilendi</h1><p>VLC Linki: /trt1.m3u8</p>"

@app.route('/trt1.m3u8')
def master():
    # Ana listeyi alıp içindeki her şeyi bizim sunucuya yönlendiriyoruz
    try:
        r = requests.get(BASE_URL + "master.m3u8", headers=HEADERS, timeout=10)
        content = r.text.replace(BASE_URL, request.host_url + "proxy/")
        return Response(content, content_type="application/vnd.apple.mpegurl")
    except:
        return "Hata", 500

@app.route('/proxy/<path:path>')
def proxy(path):
    # Hem alt listeleri hem de video parçalarını çeken ortak kapı
    url = BASE_URL + path
    r = requests.get(url, headers=HEADERS, stream=True, timeout=15)
    
    if ".m3u8" in path:
        # Eğer bu bir alt listeyse içindeki linkleri de temizle
        content = r.text.replace(BASE_URL, request.host_url + "proxy/")
        return Response(content, content_type="application/vnd.apple.mpegurl")
    
    # Video parçasıysa doğrudan akıt
    return Response(r.content, content_type=r.headers.get('content-type'))

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=10000)
