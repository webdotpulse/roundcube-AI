import subprocess
import json
import time

html = """<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="file:///home/koen/Git/roundcube-AI/skins/gmail_plus/style.css">
<style>
body { font-family: sans-serif; background: #f0f0f0; margin: 0; padding: 20px; }
</style>
</head>
<body class="skin-gmail_plus">
<div id="lpai-overlay" style="display:block"></div>
<div id="lpai-panel" style="display:flex">
    <div id="lpai-header">
        <div class="lpai-title-wrapper">
            <span class="lpai-gemini-sparkle"></span>
            <span id="lpai-title">Gemini Assistant</span>
            <span class="lpai-model-tag">gemini-3.8-flash</span>
        </div>
        <button type="button" id="lpai-close">&times;</button>
    </div>
    <div id="lpai-body">
        <div id="lpai-controls">
            <div class="lpai-control-group">
                <label for="lpai-model-select">Model</label>
                <select id="lpai-model-select" class="lpai-select">
                    <option value="gemini-3.8-flash" selected>gemini 3.8 flash</option>
                    <option value="gemini-3.8-flash-cyber">gemini 3.8 flash cyber</option>
                    <option value="gemini-3.7-flash">gemini 3.7 flash</option>
                </select>
            </div>
            <div class="lpai-control-group">
                <label for="lpai-tone-select">Tone</label>
                <select id="lpai-tone-select" class="lpai-select">
                    <option value="professional" selected>Professional</option>
                    <option value="concise">Concise</option>
                    <option value="friendly">Friendly</option>
                </select>
            </div>
            <div class="lpai-control-group">
                <label for="lpai-lang-select">Language</label>
                <select id="lpai-lang-select" class="lpai-select">
                    <option value="English">English</option>
                    <option value="Dutch" selected>Dutch</option>
                    <option value="Spanish">Spanish</option>
                </select>
            </div>
        </div>
        <div id="lpai-input-wrapper">
            <textarea id="lpai-input"></textarea>
        </div>
    </div>
</div>
<script>
console.log("SELECT_COUNT:" + document.querySelectorAll('select').length);
const tone = document.getElementById('lpai-tone-select');
tone.addEventListener('mousedown', (e) => console.log('MOUSEDOWN, defaultPrevented=' + e.defaultPrevented));
tone.addEventListener('click', (e) => console.log('CLICK, defaultPrevented=' + e.defaultPrevented));
tone.addEventListener('focus', () => console.log('FOCUS'));
</script>
</body>
</html>
"""

with open("/tmp/select_test.html", "w") as f:
    f.write(html)

cmd = [
    "google-chrome-stable",
    "--headless",
    "--disable-gpu",
    "--no-sandbox",
    "--window-size=1280,800",
    "--enable-logging=stderr",
    "/tmp/select_test.html"
]

res = subprocess.run(cmd, capture_output=True, text=True, timeout=5)
print("STDOUT:", res.stdout)
print("STDERR:", res.stderr)
