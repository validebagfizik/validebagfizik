from flask import Flask, redirect
import requests
import re

app = Flask(__name__)

def get_live_token_link():
    url = "https://www.dmax.com.tr/canli-izle"
    headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0"}
    
    try:
        response = requests.get(url, headers=headers)
        # Sayfadaki güncel m3u8 linkini (token dahil) bulur
        match = re.search(r'https?://[\?=&%_\.\-\w/]*\.m3u8[\?=&%_\.\-\w]*', response.text)
        if match:
            return match.group(0)
    except:
        return None
    return None

@app.route('/dmax.m3u8')
def play_dmax():
    target_link = get_live_token_link()
    if target_link:
        # Seni otomatik olarak o anki geçerli/tokenlı linke yönlendirir
        return redirect(target_link, code=302)
    return "Link bulunamadı", 404

if __name__ == '__main__':
    app.run(port=5000)
