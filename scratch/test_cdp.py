import subprocess
import json
import urllib.request
import time

html = """<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="file:///home/koen/Git/roundcube-AI/skins/gmail_plus/style.css">
</head>
<body class="skin-gmail_plus">
<div id="lpai-overlay" style="display:block"></div>
<div id="lpai-panel" style="display:flex">
    <div id="lpai-body">
        <div id="lpai-controls">
            <div class="lpai-control-group">
                <label for="lpai-tone-select">Tone</label>
                <select id="lpai-tone-select" class="lpai-select">
                    <option value="professional" selected>Professional</option>
                    <option value="concise">Concise</option>
                    <option value="friendly">Friendly</option>
                </select>
            </div>
        </div>
    </div>
</div>
<script>
var tone = document.getElementById('lpai-tone-select');
tone.addEventListener('mousedown', function(e) {
    console.log('EVENT_MOUSEDOWN, defaultPrevented=' + e.defaultPrevented);
});
tone.addEventListener('mouseup', function(e) {
    console.log('EVENT_MOUSEUP, defaultPrevented=' + e.defaultPrevented);
});
tone.addEventListener('click', function(e) {
    console.log('EVENT_CLICK, defaultPrevented=' + e.defaultPrevented);
});
</script>
</body>
</html>
"""

with open("/tmp/cdp_test.html", "w") as f:
    f.write(html)

proc = subprocess.Popen([
    "google-chrome-stable",
    "--headless",
    "--disable-gpu",
    "--no-sandbox",
    "--remote-debugging-port=9222",
    "/tmp/cdp_test.html"
], stdout=subprocess.PIPE, stderr=subprocess.PIPE)

time.sleep(1.5)

try:
    with urllib.request.urlopen("http://127.0.0.1:9222/json") as response:
        pages = json.loads(response.read().decode())
    print("PAGES:", pages)
finally:
    proc.terminate()
