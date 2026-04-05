from flask import Flask, redirect
import requests
import re

app = Flask(__name__)

def get_dmax_link():
    url = "https://www.dmax.com.tr/canli-izle"
    headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0"}
    try:
        response = requests.get(url, headers=headers)
        match = re.search(r'https?://[\?=&%_\.\-\w/]*\.m3u8[\?=&%_\.\-\w]*', response.text)
        return match.group(0) if match else None
    except:
        return None

@app.route('/dmax.m3u8')
def play_dmax():
    link = get_dmax_link()
    if link:
        return redirect(link, code=302)
    return "Link bulunamadı", 404

@app.route('/')
def index():
    return "Validebag TV Sunucusu Calisiyor!"

if __name__ == '__main__':
    app.run()
