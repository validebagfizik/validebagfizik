from flask import Flask, Response, redirect
import os
import requests
import re

app = Flask(__name__)

def get_real_trt_link():
    try:
        # TRT'nin ham yayın sayfasını tarayıcı açmadan indiriyoruz
        url = "https://www.trtizle.com/canli/tv/trt-1"
        headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
        }
        response = requests.get(url, headers=headers, timeout=10)
        
        # Sayfa içindeki gizli .m3u8 linkini cımbızla çekiyoruz (Regex)
        match = re.search(r'(https://[^\s^"]+\.m3u8[^\s^"]*)', response.text)
        
        if match:
            return match.group(1).replace("\\u002F", "/")
        return None
    except:
        return None

@app.route('/trt1.m3u8')
def trt1():
    real_link = get_real_trt_link()
    
    if real_link:
        # Eğer linki bulduysak, IPTV oynatıcının anlayacağı dosyayı anında oluştur
        content = f"#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-STREAM-INF:BANDWIDTH=1280000\n{real_link}"
        return Response(content, mimetype='application/x-mpegURL')
    else:
        # Link bulunamazsa eski sabit linki ver (en azından hata vermesin)
        return "#EXTM3U\nhttps://trtcanlitv-lh.akamaihd.net/i/TRT1_1@12345/master.m3u8"

@app.route('/')
def home():
    return "<h1>Sistem Hafifletildi!</h1><p>Yayın adresi: <b>/trt1.m3u8</b></p>"

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host='0.0.0.0', port=port)
