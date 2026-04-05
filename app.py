@app.route('/trt1.m3u8')
def play_trt():
    url = "https://tv-trt1.medya.trt.com.tr/master.m3u8"

    req = requests.get(url, headers=HEADERS, stream=True)

    def generate():
        for chunk in req.iter_content(chunk_size=1024):
            yield chunk

    return Response(generate(), content_type=req.headers.get('content-type'), headers={
        'Access-Control-Allow-Origin': '*'
    })
