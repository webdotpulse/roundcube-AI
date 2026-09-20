const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');

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
        '/tmp/select_point_test.html'
    ]);

    await new Promise(r => setTimeout(r, 1200));

    try {
        const wsUrl = await getWsUrl();
        console.log('WS URL:', wsUrl);
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
        console.log('Connected to CDP');

        await send('Page.enable');
        await send('Runtime.enable');

        // Evaluate bounding rect of #lpai-tone-select
        const evalRes = await send('Runtime.evaluate', {
            expression: `
                const el = document.getElementById('lpai-tone-select');
                const r = el.getBoundingClientRect();
                ({ x: r.left + r.width / 2, y: r.top + r.height / 2 })
            `,
            returnByValue: true
        });
        const coords = evalRes.result.result.value;
        console.log('Coords:', coords);

        // Click on it
        console.log('Dispatching mouse click...');
        await send('Input.dispatchMouseEvent', {
            type: 'mousePressed',
            x: coords.x,
            y: coords.y,
            button: 'left',
            clickCount: 1
        });
        await send('Input.dispatchMouseEvent', {
            type: 'mouseReleased',
            x: coords.x,
            y: coords.y,
            button: 'left',
            clickCount: 1
        });

        await new Promise(r => setTimeout(r, 500));

        // Take screenshot
        const shot = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync('/home/koen/Git/roundcube-AI/scratch/chrome_click_shot.png', Buffer.from(shot.result.data, 'base64'));
        console.log('Screenshot saved to scratch/chrome_click_shot.png');

        // Check active element
        const active = await send('Runtime.evaluate', {
            expression: 'document.activeElement ? (document.activeElement.tagName + "#" + document.activeElement.id) : "none"',
            returnByValue: true
        });
        console.log('Active element:', active.result.result.value);

        ws.close();
    } finally {
        chrome.kill();
    }
}

run().catch(console.error);
