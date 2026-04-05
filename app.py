from flask import Flask, redirect

app = Flask(__name__)

# Bu ana sayfa (https://validebagfizik.onrender.com/)
@app.route('/')
def index():
    return "<h1>Sistem Calisiyor!</h1><p>TRT 1 test linki hazir: /trt1.m3u8</p>"

# Bu TRT 1 yolu (https://validebagfizik.onrender.com/trt1.m3u8)
@app.route('/trt1.m3u8')
def play_trt():
    # TRT'nin doğrudan yayın linki
    trt_link = "https://tv-trt1.medya.trt.com.tr/master.m3u8"
    return redirect(trt_link)

if __name__ == '__main__':
    app.run()
