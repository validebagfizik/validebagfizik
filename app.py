@app.route('/trt1.m3u8')
def play_trt():
    url = "https://tv-trt1.medya.trt.com.tr/master.m3u8"

    r = requests.get(url, headers=HEADERS)
    content = r.text

    # TS linklerini kendi sunucuna yönlendir
    content = content.replace("https://tv-trt1.medya.trt.com.tr/", request.host_url + "segment/")

    return Response(content, content_type="application/vnd.apple.mpegurl")
