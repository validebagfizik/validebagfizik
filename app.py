from flask import Flask, redirect, request
import requests

app = Flask(__name__)

# TRT'nin ana yayın merkezi
BASE_URL = "https://tv-trt1.medya.trt.com.tr/"

@app.route('/')
def home():
    return "<h1>Sistem Aktif</h1><p>Adres: /trt1.m3u8</p>"

@app.route('/trt1.m3u8')
def trt_yonlendir():
    # Sadece ana linke gitmeni sağlar, aracı olmaz (En hızlı yöntem)
    return redirect(BASE_URL + "master.m3u8")

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=10000)
