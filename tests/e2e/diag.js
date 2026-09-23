const { chromium } = require('./node_modules/playwright');
const { BASE_URL, login } = require('./test_helpers');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });
    await login(page);

    await page.goto(`${BASE_URL}/?_task=mail&_mbox=INBOX`);
    await page.waitForTimeout(2000);

    const info = await page.evaluate(() => {
        const rows = document.querySelectorAll('#messagelist tbody tr, table.messagelist tr, .listing tbody tr');
        return {
            rowCount: rows.length,
            rowInfo: Array.from(rows).map(r => ({
                id: r.id,
                className: r.className,
                visible: r.offsetWidth > 0 && r.offsetHeight > 0,
                text: r.innerText.trim().replace(/\n/g, ' ')
            }))
        };
    });

    console.log('Diagnostic Info:', JSON.stringify(info, null, 2));
    await browser.close();
})();
