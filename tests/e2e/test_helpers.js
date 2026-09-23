/**
 * Playwright E2E Helper Utilities for Roundcube AI Suite
 */

const { execSync } = require('child_process');

const BASE_URL = process.env.ROUNDCUBE_URL || 'http://127.0.0.1:8888';

/**
 * Configure active user skin preference directly in database/Roundcube environment.
 */
function setUserSkin(skin) {
    try {
        execSync(`PHPRC=/home/koen/.config/php php tests/e2e/set_skin.php "${skin}"`, {
            cwd: '/home/koen/Git/roundcube-AI',
            stdio: 'pipe'
        });
    } catch (e) {
        console.error('Failed to set user skin:', e.message);
    }
}

/**
 * Audit console, page errors, and HTTP 4xx/5xx errors on page.
 */
function setupErrorAuditor(page) {
    const errorLog = [];

    page.on('console', msg => {
        if (msg.type() === 'error') {
            const text = msg.text();
            // Ignore intentional test mocks or browser extension noise
            if (!text.includes('favicon.ico')) {
                errorLog.push({ type: 'console.error', text });
            }
        }
    });

    page.on('pageerror', err => {
        errorLog.push({ type: 'pageerror', text: err.message, stack: err.stack });
    });

    page.on('response', res => {
        const status = res.status();
        const url = res.url();
        // Ignore 404 for optional favicon
        if (status >= 400 && !url.includes('favicon.ico')) {
            errorLog.push({ type: 'http.error', status, url });
        }
    });

    page.on('dialog', async dialog => {
        try {
            await dialog.accept();
        } catch (e) {}
    });

    return errorLog;
}

/**
 * Mock Gemini AI API responses for lifeprisma_ai / roundcube_ai.
 */
async function setupAiMocks(page) {
    // SSE Stream Mock
    await page.route('**/*plugin.lifeprisma_ai_stream*', async route => {
        const sseBody = 
            'data: {"type": "delta", "text": "Dear Sarah,\\n\\nThank you for reaching out. Here is the requested summary and confirmation of our sync scheduled for tomorrow afternoon.\\n\\nBest regards,\\nKoen"}\n\n' +
            'data: {"type": "done", "model": "gemini-3.8-flash", "usage": {"total_tokens": 85}}\n\n';

        await route.fulfill({
            status: 200,
            contentType: 'text/event-stream',
            headers: {
                'Cache-Control': 'no-cache',
                'Connection': 'keep-alive'
            },
            body: sseBody
        });
    });

    // Synchronous Request Mock
    await page.route('**/*plugin.lifeprisma_ai_request*', async route => {
        const jsonBody = JSON.stringify({
            status: 'success',
            result: 'Dear Sarah,\n\nThank you for reaching out. Here is the requested summary and confirmation of our sync scheduled for tomorrow afternoon.\n\nBest regards,\nKoen',
            model: 'gemini-3.8-flash',
            usage: { prompt_tokens: 35, completion_tokens: 50, total_tokens: 85 }
        });

        await route.fulfill({
            status: 200,
            contentType: 'application/json; charset=utf-8',
            body: jsonBody
        });
    });
}

/**
 * Authenticate session via UI login.
 */
async function login(page, user = 'test@example.com', pass = 'password123') {
    await page.goto(BASE_URL + '/');

    // Check if already logged in
    const isLogin = await page.$('input[name="_user"]');
    if (isLogin) {
        await page.fill('input[name="_user"]', user);
        await page.fill('input[name="_pass"]', pass);
        await Promise.all([
            page.waitForURL(url => url.toString().includes('_task=mail'), { timeout: 15000 }),
            page.click('button[type="submit"], input[type="submit"]')
        ]);
        await page.waitForLoadState('domcontentloaded');
        await page.waitForTimeout(500);
    }
}

module.exports = {
    BASE_URL,
    setUserSkin,
    setupErrorAuditor,
    setupAiMocks,
    login
};
