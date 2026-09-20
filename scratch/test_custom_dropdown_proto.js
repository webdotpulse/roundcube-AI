const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');

const testHtml = `<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f0f0f0; margin: 0; padding: 40px; }
#lpai-controls { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; width: 750px; background: #fff; padding: 20px; border-radius: 12px; }
.lpai-control-group { display: flex; flex-direction: column; gap: 5px; }
.lpai-control-group label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; }

.lpai-custom-select {
    position: relative !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

.lpai-custom-select-trigger {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100% !important;
    height: 38px !important;
    padding: 6px 12px !important;
    border: 1.5px solid #e5e7eb !important;
    border-radius: 9px !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    color: #1f2937 !important;
    background-color: #ffffff !important;
    cursor: pointer !important;
    transition: border-color 0.15s, box-shadow 0.15s !important;
    box-sizing: border-box !important;
    outline: none !important;
    user-select: none !important;
    -webkit-user-select: none !important;
    z-index: 12 !important;
    position: relative !important;
    text-align: left !important;
}

.lpai-custom-select-trigger:hover {
    border-color: #d1d5db !important;
}

.lpai-custom-select.open .lpai-custom-select-trigger,
.lpai-custom-select-trigger:focus {
    border-color: #9333ea !important;
    box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.15) !important;
}

.lpai-custom-select-label {
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    flex: 1 1 auto !important;
}

.lpai-custom-select-arrow {
    width: 16px !important;
    height: 16px !important;
    stroke: #6b7280 !important;
    flex-shrink: 0 !important;
    transition: transform 0.2s ease !important;
}

.lpai-custom-select.open .lpai-custom-select-arrow {
    transform: rotate(180deg) !important;
    stroke: #9333ea !important;
}

.lpai-custom-dropdown {
    position: absolute !important;
    top: calc(100% + 4px) !important;
    left: 0 !important;
    right: 0 !important;
    background: #ffffff !important;
    border: 1.5px solid #e5e7eb !important;
    border-radius: 10px !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
    max-height: 220px !important;
    overflow-y: auto !important;
    z-index: 100 !important;
    padding: 4px !important;
    margin: 0 !important;
    box-sizing: border-box !important;
    animation: lpai-dropdown-pop 0.15s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

@keyframes lpai-dropdown-pop {
    0% { opacity: 0; transform: translateY(-4px); }
    100% { opacity: 1; transform: translateY(0); }
}

.lpai-dropdown-option {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 8px 10px !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    color: #374151 !important;
    border-radius: 6px !important;
    cursor: pointer !important;
    transition: background-color 0.12s, color 0.12s !important;
    user-select: none !important;
    -webkit-user-select: none !important;
}

.lpai-dropdown-option:hover,
.lpai-dropdown-option.focused {
    background-color: #f3e8ff !important;
    color: #7e22ce !important;
}

.lpai-dropdown-option.selected {
    background-color: #ede9fe !important;
    color: #6b21a8 !important;
    font-weight: 600 !important;
}

.lpai-dropdown-option .lpai-option-check {
    display: none !important;
    width: 14px !important;
    height: 14px !important;
    stroke: #7e22ce !important;
}

.lpai-dropdown-option.selected .lpai-option-check {
    display: inline-block !important;
}

select.lpai-select,
#lpai-model-select,
#lpai-tone-select,
#lpai-lang-select {
    position: relative !important;
    z-index: 10 !important;
    pointer-events: auto !important;
    width: 100% !important;
    height: 38px !important;
    padding: 6px 30px 6px 12px !important;
    border: 1.5px solid #e5e7eb !important;
    border-radius: 9px !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    color: #1f2937 !important;
    background-color: #ffffff !important;
    box-sizing: border-box !important;
    outline: none !important;
}

.lpai-custom-select select.lpai-select {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    opacity: 0 !important;
    pointer-events: auto !important;
    z-index: 10 !important;
}
</style>
</head>
<body>
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
            <option value="formal">Formal</option>
            <option value="direct">Direct</option>
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

<script>
function lpai_init_custom_selects() {
    var selects = document.querySelectorAll('.lpai-select');
    selects.forEach(function(sel) {
        if (!sel.id) return;
        var parent = sel.closest('.lpai-custom-select');
        if (!parent) {
            var wrapper = document.createElement('div');
            wrapper.className = 'lpai-custom-select';
            wrapper.dataset.selectId = sel.id;
            sel.parentNode.insertBefore(wrapper, sel);
            wrapper.appendChild(sel);
            parent = wrapper;
        }

        var trigger = parent.querySelector('.lpai-custom-select-trigger');
        var dropdown = parent.querySelector('.lpai-custom-dropdown');

        if (!trigger) {
            trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'lpai-custom-select-trigger';
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');

            var labelSpan = document.createElement('span');
            labelSpan.className = 'lpai-custom-select-label';
            var selectedOpt = sel.options[sel.selectedIndex] || sel.options[0];
            labelSpan.textContent = selectedOpt ? selectedOpt.text : '';

            var arrowSvg = '<svg class="lpai-custom-select-arrow" viewBox="0 0 20 20" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8l4 4 4-4"/></svg>';
            trigger.appendChild(labelSpan);
            trigger.insertAdjacentHTML('beforeend', arrowSvg);
            parent.insertBefore(trigger, sel);
        }

        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.className = 'lpai-custom-dropdown';
            dropdown.setAttribute('role', 'listbox');
            dropdown.style.display = 'none';
            parent.insertBefore(dropdown, sel);
        }

        dropdown.innerHTML = '';
        Array.from(sel.options).forEach(function(opt) {
            var item = document.createElement('div');
            item.className = 'lpai-dropdown-option' + (opt.value === sel.value ? ' selected' : '');
            item.dataset.value = opt.value;
            item.setAttribute('role', 'option');

            var textSpan = document.createElement('span');
            textSpan.textContent = opt.text;
            item.appendChild(textSpan);

            var checkSvg = '<svg class="lpai-option-check" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
            item.insertAdjacentHTML('beforeend', checkSvg);

            item.addEventListener('click', function(e) {
                e.stopPropagation();
                sel.value = opt.value;
                lpai_sync_custom_selects();
                sel.dispatchEvent(new Event('change', { bubbles: true }));
                lpai_close_all_custom_dropdowns();
                trigger.focus();
            });

            dropdown.appendChild(item);
        });

        if (!trigger._lpai_bound) {
            trigger._lpai_bound = true;
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var isOpen = parent.classList.contains('open');
                lpai_close_all_custom_dropdowns();
                if (!isOpen) {
                    parent.classList.add('open');
                    dropdown.style.display = 'block';
                    trigger.setAttribute('aria-expanded', 'true');
                }
            });
        }
    });
}

function lpai_close_all_custom_dropdowns() {
    document.querySelectorAll('.lpai-custom-select.open').forEach(function(cs) {
        cs.classList.remove('open');
        var dd = cs.querySelector('.lpai-custom-dropdown');
        if (dd) dd.style.display = 'none';
        var tr = cs.querySelector('.lpai-custom-select-trigger');
        if (tr) tr.setAttribute('aria-expanded', 'false');
    });
}

function lpai_sync_custom_selects() {
    document.querySelectorAll('.lpai-select').forEach(function(sel) {
        var parent = sel.closest('.lpai-custom-select');
        if (!parent) return;
        var labelEl = parent.querySelector('.lpai-custom-select-label');
        var opt = sel.options[sel.selectedIndex];
        if (labelEl && opt) {
            labelEl.textContent = opt.text;
        }
        var items = parent.querySelectorAll('.lpai-dropdown-option');
        items.forEach(function(it) {
            var isSel = (it.dataset.value === sel.value);
            it.classList.toggle('selected', isSel);
        });
    });
}

document.addEventListener('click', function(e) {
    if (!e.target.closest || !e.target.closest('.lpai-custom-select')) {
        lpai_close_all_custom_dropdowns();
    }
});

lpai_init_custom_selects();
</script>
</body>
</html>
`;

fs.writeFileSync('/tmp/test_custom_dropdown.html', testHtml);

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
        '/tmp/test_custom_dropdown.html'
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

        // Check computed style of #lpai-model-select
        const evalStyles = await send('Runtime.evaluate', {
            expression: `
                const sel = document.getElementById('lpai-model-select');
                const cs = window.getComputedStyle(sel);
                ({ pointerEvents: cs.pointerEvents, zIndex: cs.zIndex })
            `,
            returnByValue: true
        });
        console.log('Model select computed styles:', evalStyles.result.result.value);

        // Click on Tone trigger
        const evalTrigger = await send('Runtime.evaluate', {
            expression: `
                const parent = document.querySelector('[data-select-id="lpai-tone-select"]');
                const trigger = parent.querySelector('.lpai-custom-select-trigger');
                const r = trigger.getBoundingClientRect();
                ({ x: r.left + r.width / 2, y: r.top + r.height / 2 })
            `,
            returnByValue: true
        });
        const coords = evalTrigger.result.result.value;
        console.log('Trigger coords:', coords);

        await send('Input.dispatchMouseEvent', { type: 'mousePressed', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });
        await send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });

        await new Promise(r => setTimeout(r, 300));

        // Take screenshot with dropdown open
        const shot1 = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync('/home/koen/Git/roundcube-AI/scratch/custom_dropdown_open.png', Buffer.from(shot1.result.data, 'base64'));
        console.log('Screenshot saved to scratch/custom_dropdown_open.png');

        // Click on "Friendly" option in Tone dropdown
        const evalFriendly = await send('Runtime.evaluate', {
            expression: `
                (() => {
                    const opt = document.querySelector('[data-select-id="lpai-tone-select"] .lpai-dropdown-option[data-value="friendly"]');
                    if (!opt) return null;
                    const r = opt.getBoundingClientRect();
                    return { x: r.left + r.width / 2, y: r.top + r.height / 2 };
                })()
            `,
            returnByValue: true
        });
        const friendlyCoords = evalFriendly.result.result.value;
        console.log('Friendly option coords:', friendlyCoords);

        await send('Input.dispatchMouseEvent', { type: 'mousePressed', x: friendlyCoords.x, y: friendlyCoords.y, button: 'left', clickCount: 1 });
        await send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: friendlyCoords.x, y: friendlyCoords.y, button: 'left', clickCount: 1 });

        await new Promise(r => setTimeout(r, 300));

        // Check value and trigger text
        const checkRes = await send('Runtime.evaluate', {
            expression: `
                const sel = document.getElementById('lpai-tone-select');
                const trLabel = document.querySelector('[data-select-id="lpai-tone-select"] .lpai-custom-select-label').textContent;
                const ddDisplay = document.querySelector('[data-select-id="lpai-tone-select"] .lpai-custom-dropdown').style.display;
                ({ selValue: sel.value, trLabel, ddDisplay })
            `,
            returnByValue: true
        });
        console.log('After selecting Friendly:', checkRes.result.result.value);

        // Take screenshot after selection
        const shot2 = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync('/home/koen/Git/roundcube-AI/scratch/custom_dropdown_selected.png', Buffer.from(shot2.result.data, 'base64'));
        console.log('Screenshot saved to scratch/custom_dropdown_selected.png');

        ws.close();
    } finally {
        chrome.kill();
    }
}

run().catch(console.error);
