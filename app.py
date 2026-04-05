from flask import Flask, Response, request
import requests
import re

app = Flask(__name__)

def get_dmax_link():
    url = "https://www.dmax.com.tr/canli-izle"
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
        "Referer": "https://www.dmax.com.tr/"
    }
    try:
        response = requests.get(url, headers=headers, timeout=10)
        match = re.search(r'https?://[^\s"\'<>]*\.m3u8[^\s"\'<>]*', response.text)
        if match:
            return match.group(0).replace("\\/", "/")
        return None
    except:
        return None

@app.route('/dmax.m3u8')
def proxy_dmax():
    target_url = get_dmax_link()
    if not target_url:
        return "Yayın linki alınamadı", 404

    # DMAX'i kandırmak için gerekli başlıklar
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
        "Referer": "https://www.dmax.com.tr/",
        "Origin": "https://www.dmax.com.tr"
    }
    
    # Yayını DMAX'ten alıp senin sunucun üzerinden (Proxy) kullanıcıya iletiyoruz
    req = requests.get(target_url, headers=headers, stream=True)
    
    # Tarayıcıya/Oynatıcıya "Bu bir videodur ve herkese açıktır" diyoruz
    exclude_headers = ['content-encoding', 'content-length', 'transfer-encoding', 'connection']
    headers_out = [(name, value) for (name, value) in req.headers.items()
                   if name.lower() not in exclude_headers]
    headers_out.append(('Access-Control-Allow-Origin', '*'))

    return Response(req.content, req.status_code, headers_out)

@app.route('/')
def index():
    return "Validebag TV Sunucusu - Proxy Modu Aktif!"

if __name__ == '__main__':
    app.run()
