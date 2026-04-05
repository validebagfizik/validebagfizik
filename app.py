from flask import Flask, redirect

app = Flask(__name__)

TRT1_LINK = "https://trt.daioncdn.net/trt-1/master.m3u8?app=web"

@app.route('/trt1.m3u8')
def trt1():
    return redirect(TRT1_LINK)
