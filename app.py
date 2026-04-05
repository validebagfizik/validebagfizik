from playwright.sync_api import sync_playwright

def get_stream_info():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(locale="tr-TR")
        page = context.new_page()

        stream_url = None
        headers = {}

        def handle_request(request):
            nonlocal stream_url, headers
            url = request.url

            if ".m3u8" in url:
                stream_url = url
                headers = request.headers

        page.on("request", handle_request)

        page.goto("https://www.trtizle.com/canli/tv/trt1", timeout=60000)
        page.wait_for_timeout(8000)

        browser.close()

        return stream_url, headers
