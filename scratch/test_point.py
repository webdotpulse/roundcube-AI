import subprocess

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
window.onload = function() {
    var tone = document.getElementById('lpai-tone-select');
    var rect = tone.getBoundingClientRect();
    var cx = rect.left + rect.width / 2;
    var cy = rect.top + rect.height / 2;
    var elAtPoint = document.elementFromPoint(cx, cy);
    console.log("ELEMENT_AT_POINT:" + (elAtPoint ? (elAtPoint.tagName + '#' + elAtPoint.id + '.' + elAtPoint.className) : "null"));
    console.log("RECT:" + JSON.stringify(rect));
    console.log("TONE_VALUE:" + tone.value);
    console.log("SHOW_PICKER_AVAILABLE:" + (typeof tone.showPicker === 'function'));
    
    // Try calling showPicker
    try {
        tone.showPicker();
        console.log("SHOW_PICKER_CALL_SUCCESS");
    } catch(e) {
        console.log("SHOW_PICKER_ERROR:" + e.message);
    }
};
</script>
</body>
</html>
"""

with open("/tmp/select_point_test.html", "w") as f:
    f.write(html)

cmd = 'google-chrome-stable --headless --disable-gpu --no-sandbox --window-size=1280,800 --run-all-compositor-stages-before-draw --virtual-time-budget=2000 --enable-logging=stderr /tmp/select_point_test.html 2>&1'
output = subprocess.check_output(cmd, shell=True, text=True)
for line in output.split("\n"):
    if any(k in line for k in ["ELEMENT_AT_POINT", "RECT", "SHOW_PICKER", "TONE_VALUE"]):
        print(line)
