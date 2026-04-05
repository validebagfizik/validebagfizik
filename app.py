from flask import Flask, Response, request, redirect
import requests
import re

app = Flask(__name__)

# Yayıncıyı "Türkiye'den bir kullanıcıyız" diye kandırmak için kullanılan başlıklar
HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36",
    "Accept-Language": "tr-TR,tr;q=0.9,en-US;q=0.8,en;q=0.7",
    "Referer": "https://www.dmax.com.tr/",
    "Origin": "https://www.dmax.com.tr",
    "X-Forwarded-For": "88.230.12.34" # Örnek Türkiye IP'si
}

def get_dmax_url():
    """DMAX sitesine gidip güncel .m3u8 linkini bulur"""
    url = "https://www.dmax.com.tr/canli-izle"
    try:
        r = requests.get(url, headers=HEADERS, timeout=10)
        # Sayfa kaynağında .m3u8 ile biten linki arıyoruz
        match = re.search(r'https?://[^\s"\'<>]*\.m3u8[^\s"\'<>]*', r.text)
        if match:
            return match.group(0).replace("\\/", "/")
        return None
    except Exception as e:
        print(f"Hata oluştu: {e}")
        return None

@app.route('/')
def home():
    return """
    <h1>Validebag Yerel Proxy Sunucusu Aktif</h1>
    <p>Test etmek için aşağıdaki linkleri kullanın:</p>
    <ul>
        <li><a href="/yayin.m3u8">DMAX Canlı Yayın (Proxy Modu)</a></li>
        <li><a href="/trt1.m3u8">TRT 1 (Doğrudan Yönlendirme)</a></li>
    </ul>
    """

@app.route('/yayin.m3u8')
def proxy_dmax():
    """DMAX yayınını senin sunucun üzerinden akıtır (Proxy)"""
    target_url = get_dmax_url()
    if not target_url:
        return "DMAX yayın linki şu an alınamadı.", 404

    try:
        # Yayını DMAX'ten alıyoruz
        req = requests.get(target_url, headers=HEADERS, stream=True, timeout=10)
        
        # Alınan yayını paketler halinde kullanıcıya iletiyoruz
        def generate():
            for chunk in req.iter_content(chunk_size=1024*1024):
                yield chunk

        return Response(generate(), content_type=req.headers.get('content-type'), headers={
            'Access-Control-Allow-Origin': '*',
            'Cache-Control': 'no-cache'
        })
    except:
        return "Yayın akışı sırasında hata oluştu.", 500

@app.route('/trt1.m3u8')
def play_trt():
    """TRT 1 için hızlı yönlendirme testi"""
    return redirect("https://tv-trt1.medya.trt.com.tr/master.m3u8")

if __name__ == '__main__':
    # Render'ın beklediği port ayarı
    app.run(host='0.0.0.0', port=10000)
