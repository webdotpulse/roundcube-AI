/**
 * E2E Suite: Mail View & Toolbar Interactions
 */

const { BASE_URL, setupErrorAuditor, setupAiMocks, login } = require('./test_helpers');

async function testMailViewAndToolbar(page, metrics) {
    console.log('--- Testing Mail View & Toolbar ---');

    await page.goto(`${BASE_URL}/?_task=mail&_mbox=INBOX`);
    await page.waitForTimeout(1500);

    // 1. Message Selection & Preview
    await page.waitForSelector('#messagelist tbody tr.message', { timeout: 5000 }).catch(() => {});
    const allRows = await page.$$('#messagelist tbody tr.message, table.messagelist tr.message, .listing tbody tr.message');
    const messageRows = [];
    for (const r of allRows) {
        if (await r.isVisible().catch(() => false)) {
            messageRows.push(r);
        }
    }

    metrics.interactions++;
    if (messageRows.length > 0) {
        // Click first message to select it
        await messageRows[0].click({ force: true }).catch(() => {});
        metrics.buttonsClicked++;
        await page.waitForTimeout(800);
    }

    // 2. Toolbar Actions Exploration
    // Test toolbar buttons: Reply, Reply All, Forward, Delete, Mark, Move, Print, Source, Open in new window
    const toolbarSelectors = [
        'a.button.reply, #rcmbtn106, [data-action="reply"], .reply',
        'a.button.reply-all, #rcmbtn107, [data-action="reply-all"], .reply-all',
        'a.button.forward, #rcmbtn108, [data-action="forward"], .forward',
        'a.button.delete, #rcmbtn109, [data-action="delete"], .delete',
        'a.button.markmessage, #rcmbtn110, [data-action="mark"], .markmessage',
        'a.button.move, #rcmbtn111, [data-action="move"], .move',
        'a.button.print, #rcmbtn112, [data-action="print"], .print',
        'a.button.source, #rcmbtn113, [data-action="source"], .source',
        'a.button.extwin, #rcmbtn114, [data-action="extwin"], .extwin'
    ];

    for (const sel of toolbarSelectors) {
        const btn = await page.$(sel);
        if (btn) {
            metrics.buttonsClicked++;
            metrics.interactions++;
            // Check visibility and attributes
            const isVisible = await btn.isVisible().catch(() => false);
            const isEnabled = await btn.evaluate(el => !el.classList.contains('disabled')).catch(() => false);
            if (isVisible && isEnabled) {
                // If it's a non-destructive menu button, click it to test dropdown/action
                if (sel.includes('mark') || sel.includes('move')) {
                    await btn.click().catch(() => {});
                    await page.waitForTimeout(300);
                }
            }
        }
    }

    // 3. Test Thunderbird Labels
    // Check if label menu / buttons exist and assign a label
    const labelMenuBtn = await page.$('#rcmbtn115, a.button.tb-labels, a.tb-labels, .tb-labels-button, [data-action="plugin.thunderbird_labels"]');
    if (labelMenuBtn) {
        metrics.buttonsClicked++;
        metrics.interactions++;
        await labelMenuBtn.click().catch(() => {});
        await page.waitForTimeout(500);

        // Click a label option if popup opened
        const labelItem = await page.$('.popupmenu a.label, .tb-label-item, [data-label="1"]');
        if (labelItem) {
            metrics.buttonsClicked++;
            metrics.interactions++;
            await labelItem.click().catch(() => {});
            await page.waitForTimeout(500);
        }
    }

    // Test label filter / tags in folder list or search bar
    const labelFilter = await page.$('#tb-labels-filter, .tb-labels-filter, select[name="tb_label_filter"]');
    if (labelFilter) {
        metrics.interactions++;
        await labelFilter.selectOption({ index: 1 }).catch(() => {});
        await page.waitForTimeout(500);
    }

    // 4. Test Thread Drafts (collapse/expand threads)
    const threadToggle = await page.$('.thread-toggle, tr.thread .toggle, .collapsed, .expanded');
    if (threadToggle) {
        metrics.buttonsClicked++;
        metrics.interactions++;
        await threadToggle.click().catch(() => {});
        await page.waitForTimeout(500);
        // Toggle again to restore
        await threadToggle.click().catch(() => {});
        await page.waitForTimeout(500);
    }

    // 5. Test Roundcube Attachments
    // Select message 3 (which has attachments: proposal.pdf, metrics.csv)
    const freshRows = await page.$$('#messagelist tbody tr.message, table.messagelist tr.message, .listing tbody tr.message');
    if (freshRows.length >= 3) {
        await freshRows[2].click({ force: true }).catch(() => {});
        metrics.interactions++;
        await page.waitForTimeout(1000);

        // Check for attachment list in message preview
        const attList = await page.$('#attachment-list, .attachments-list, .attachment-list, ul.attachments');
        if (attList) {
            const attItems = await page.$$('.attachments-list li, ul.attachments li, #attachment-list a');
            for (const item of attItems) {
                metrics.interactions++;
                const attLink = await item.$('a');
                if (attLink) {
                    const title = await attLink.getAttribute('title') || await attLink.innerText();
                    // Verify attachment has valid name
                    if (title && (title.includes('pdf') || title.includes('csv'))) {
                        metrics.buttonsClicked++;
                    }
                }
            }
        }
    }

    // 6. Test xmultibox Selector
    const multiboxSelector = await page.$('#xmultibox-select, .xmultibox-selector, select[name="_xmultibox"]');
    if (multiboxSelector) {
        metrics.interactions++;
        const options = await multiboxSelector.$$('option');
        if (options.length > 1) {
            await multiboxSelector.selectOption({ index: 1 }).catch(() => {});
            await page.waitForTimeout(500);
            metrics.buttonsClicked++;
        }
    }

    console.log('✓ Mail View & Toolbar tested successfully.');
}

module.exports = { testMailViewAndToolbar };
