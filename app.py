from flask import Flask, redirect, Response
import requests
import re

app = Flask(__name__)

def get_dmax_link():
    url = "https://www.dmax.com.tr/canli-izle"
    # Sunucuyu gerçek bir kullanıcı gibi gösteriyoruz
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
        "Referer": "https://www.dmax.com.tr/"
    }
    try:
        response = requests.get(url, headers=headers, timeout=10)
        # M3U8 linkini daha geniş bir taramayla bulalım
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
        # 302 yönlendirmesi yerine doğrudan linke gitmesini söylüyoruz
        return redirect(link)
    return "Yayın şu an alınamadı, lütfen sayfayı yenileyin.", 404

@app.route('/')
def index():
    return "Validebag TV Sunucusu Aktif ve Stabil!"

if __name__ == '__main__':
    app.run()
