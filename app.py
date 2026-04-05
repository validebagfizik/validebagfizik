from flask import Flask, Response, request
import requests
import re

app = Flask(__name__)

# Gerçek bir tarayıcı ve Türkiye lokasyonu süsü veren başlıklar
HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36",
    "Accept-Language": "tr-TR,tr;q=0.9,en-US;q=0.8,en;q=0.7",
    "Referer": "https://www.dmax.com.tr/",
    "Origin": "https://www.dmax.com.tr",
    "X-Forwarded-For": "88.230.12.34" # Örnek bir Türkiye IP'si (Süsleme için)
}

def get_live_link():
    # Burayı DMAX veya başka bir kanal için güncelleyebilirsin
    url = "https://www.dmax.com.tr/canli-izle"
    try:
        r = requests.get(url, headers=HEADERS, timeout=10)
        match = re.search(r'https?://[^\s"\'<>]*\.m3u8[^\s"\'<>]*', r.text)
        return match.group(0).replace("\\/", "/") if match else None
    except:
        return None

@app.route('/yayin.m3u8')
def proxy_stream():
    stream_url = get_live_link()
    if not stream_url:
        return "Yayin bulunamadi", 404

    # Yayını DMAX'ten alıp senin üzerinden kullanıcıya 'akıtıyoruz'
    req = requests.get(stream_url, headers=HEADERS, stream=True)
    
    def generate():
        for chunk in req.iter_content(chunk_size=1024*1024):
            yield chunk

    return Response(generate(), content_type=req.headers.get('content-type'), headers={
        'Access-Control-Allow-Origin': '*',
        'Cache-Control': 'no-cache'
    })

@app.route('/')
def home():
    return "<h1>Validebag Yerel Proxy Sunucusu Aktif</h1>"

if __name__ == '__main__':
    app.run()
