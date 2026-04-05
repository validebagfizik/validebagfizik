from flask import Flask, Response
import os

app = Flask(__name__)

# ANA SAYFA: Sitenin çalıştığını teyit eder
@app.route('/')
def home():
    return "<h1>Bot Sistemi Aktif!</h1><p>Yayın linki için: <b>/trt1.m3u8</b> adresine gidin.</p>"

# YAYIN ADRESİ: IPTV oynatıcıların okuyacağı yer
@app.route('/trt1.m3u8')
def trt1():
    # Şimdilik elle sabit bir link veriyoruz ki sistemin çalıştığını görelim
    # İleride buraya 'botun bulduğu link' gelecek
    ornek_link = "http://canli.trt.tv/index.m3u8"
    
    m3u8_icerigi = f"#EXTM3U\n#EXT-X-VERSION:3\n{ornek_link}"
    
    return Response(m3u8_icerigi, mimetype='application/x-mpegURL')

if __name__ == "__main__":
    # Render'ın istediği port ayarı
    port = int(os.environ.get("PORT", 5000))
    app.run(host='0.0.0.0', port=port)
