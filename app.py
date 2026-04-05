from flask import Flask, redirect, make_response
import requests
import re

app = Flask(__name__)

def get_dmax_link():
    url = "https://www.dmax.com.tr/canli-izle"
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
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
def play_dmax():
    link = get_dmax_link()
    if link:
        # Yönlendirme yaparken tarayıcıya "izin ver" (CORS) diyoruz
        response = make_response(redirect(link))
        response.headers['Access-Control-Allow-Origin'] = '*'
        return response
    return "Yayın bulunamadı", 404

@app.route('/')
def index():
    return "Validebag TV Sunucusu Aktif!"

if __name__ == '__main__':
    app.run()
