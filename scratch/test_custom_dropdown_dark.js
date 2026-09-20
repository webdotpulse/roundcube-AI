const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');

// We test dark mode
let darkHtml = fs.readFileSync('/tmp/test_custom_dropdown.html', 'utf8');
darkHtml = darkHtml.replace('<body>', '<body class="dark-mode" style="background:#0f172a;"><div style="background:#1e293b; padding:20px; border-radius:12px; border:1px solid #334155;">')
                   .replace('</body>', '</div></body>');

// Add dark mode CSS
const darkCss = `
<style>
body.dark-mode .lpai-control-group label { color: #94a3b8 !important; }
body.dark-mode .lpai-custom-select-trigger {
    background-color: #1e293b !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}
body.dark-mode .lpai-custom-select.open .lpai-custom-select-trigger,
body.dark-mode .lpai-custom-select-trigger:focus {
    border-color: #c084fc !important;
    box-shadow: 0 0 0 3px rgba(192, 132, 252, 0.25) !important;
}
body.dark-mode .lpai-custom-select-arrow { stroke: #94a3b8 !important; }
body.dark-mode .lpai-custom-select.open .lpai-custom-select-arrow { stroke: #c084fc !important; }

body.dark-mode .lpai-custom-dropdown {
    background-color: #1e293b !important;
    border-color: #334155 !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.4) !important;
}
body.dark-mode .lpai-dropdown-option {
    color: #cbd5e1 !important;
}
body.dark-mode .lpai-dropdown-option:hover,
body.dark-mode .lpai-dropdown-option.focused {
    background-color: rgba(168, 85, 247, 0.2) !important;
    color: #e9d5ff !important;
}
body.dark-mode .lpai-dropdown-option.selected {
    background-color: rgba(168, 85, 247, 0.28) !important;
    color: #f3e8ff !important;
}
body.dark-mode .lpai-dropdown-option .lpai-option-check {
    stroke: #c084fc !important;
}
</style>
`;
darkHtml = darkHtml.replace('</head>', darkCss + '</head>');
darkHtml = darkHtml.replace('background: #fff;', 'background: #1e293b;');
fs.writeFileSync('/tmp/test_custom_dropdown_dark.html', darkHtml);

async function getWsUrl() {
    return new Promise((resolve, reject) => {
        http.get('http://127.0.0.1:9222/json', res => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                const list = JSON.parse(data);
                const page = list.find(p => p.type === 'page');
                resolve(page ? page.webSocketDebuggerUrl : null);
            });
        }).on('error', reject);
    });
}

async function run() {
    const chrome = spawn('google-chrome-stable', [
        '--headless',
        '--disable-gpu',
        '--no-sandbox',
        '--window-size=1280,800',
        '--remote-debugging-port=9222',
        '/tmp/test_custom_dropdown_dark.html'
    ]);

    await new Promise(r => setTimeout(r, 1200));

    try {
        const wsUrl = await getWsUrl();
        const ws = new WebSocket(wsUrl);

        let id = 1;
        const pending = new Map();
        ws.onmessage = (event) => {
            const msg = JSON.parse(event.data);
            if (msg.id && pending.has(msg.id)) {
                pending.get(msg.id)(msg);
                pending.delete(msg.id);
            }
        };

        function send(method, params = {}) {
            return new Promise((resolve) => {
                const reqId = id++;
                pending.set(reqId, resolve);
                ws.send(JSON.stringify({ id: reqId, method, params }));
            });
        }

        await new Promise(r => ws.onopen = r);
        await send('Page.enable');
        await send('Runtime.enable');

        // Click on Model trigger
        const evalTrigger = await send('Runtime.evaluate', {
            expression: `
                const parent = document.querySelector('[data-select-id="lpai-model-select"]');
                const trigger = parent.querySelector('.lpai-custom-select-trigger');
                const r = trigger.getBoundingClientRect();
                ({ x: r.left + r.width / 2, y: r.top + r.height / 2 })
            `,
            returnByValue: true
        });
        const coords = evalTrigger.result.result.value;

        await send('Input.dispatchMouseEvent', { type: 'mousePressed', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });
        await send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });

        await new Promise(r => setTimeout(r, 300));

        // Take screenshot with dropdown open in dark mode
        const shot1 = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync('/home/koen/Git/roundcube-AI/scratch/custom_dropdown_dark.png', Buffer.from(shot1.result.data, 'base64'));
        console.log('Screenshot saved to scratch/custom_dropdown_dark.png');

        ws.close();
    } finally {
        chrome.kill();
    }
}

run().catch(console.error);
