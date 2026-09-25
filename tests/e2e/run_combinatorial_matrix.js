/**
 * Master Combinatorial E2E Matrix Runner for Roundcube AI Suite
 * Matrix: 3 Skins (gmail_plus, elastic, larry) x 2 Viewports (Desktop 1920x1080, Mobile 375x812)
 */

const { chromium } = require('./node_modules/playwright');
const { BASE_URL, setUserSkin, setupErrorAuditor, setupAiMocks, login } = require('./test_helpers');
const { testMailViewAndToolbar } = require('./test_mail_toolbar');
const { testComposeAndAi } = require('./test_compose_ai');
const { testCalendar } = require('./test_calendar');
const { testSettingsAndPlugins } = require('./test_settings_plugins');

const SKINS = ['gmail_plus', 'elastic', 'larry'];
const VIEWPORTS = [
    { name: 'Desktop (1920x1080)', width: 1920, height: 1080, isMobile: false },
    { name: 'Mobile (375x812)', width: 375, height: 812, isMobile: true }
];

async function runCombinatorialMatrix() {
    console.log('===============================================================');
    console.log('STARTING COMBINATORIAL E2E VERIFICATION MATRIX');
    console.log(`Target URL: ${BASE_URL}`);
    console.log(`Skins: ${SKINS.join(', ')}`);
    console.log(`Viewports: ${VIEWPORTS.map(v => v.name).join(', ')}`);
    console.log('===============================================================\n');

    const totalMetrics = {
        matrixCells: 0,
        matrixCellsPassed: 0,
        interactions: 0,
        buttonsClicked: 0,
        formsSubmitted: 0,
        modalsExercised: 0,
        totalErrors: 0
    };

    const cellResults = [];

    const browser = await chromium.launch({ headless: true });

    for (const skin of SKINS) {
        // Set user skin preference in database
        setUserSkin(skin);

        for (const vp of VIEWPORTS) {
            totalMetrics.matrixCells++;
            const cellTitle = `Skin: [${skin}] | Viewport: [${vp.name}]`;
            console.log(`\n===============================================================`);
            console.log(`RUNNING COMBINATION #${totalMetrics.matrixCells}: ${cellTitle}`);
            console.log(`===============================================================`);

            const cellMetric = {
                skin,
                viewport: vp.name,
                interactions: 0,
                buttonsClicked: 0,
                formsSubmitted: 0,
                modalsExercised: 0,
                errors: [],
                passed: false
            };

            const context = await browser.newContext({
                viewport: { width: vp.width, height: vp.height },
                isMobile: vp.isMobile,
                hasTouch: vp.isMobile
            });

            const page = await context.newPage();
            const errors = setupErrorAuditor(page);
            await setupAiMocks(page);

            try {
                // 1. Session Login
                await login(page);
                cellMetric.interactions++;

                // 2. Mail View & Toolbar
                await testMailViewAndToolbar(page, cellMetric);

                // 3. Compose & AI Assistant
                await testComposeAndAi(page, cellMetric);

                // 4. Calendar Task
                await testCalendar(page, cellMetric);

                // 5. Settings & Plugin Panels
                await testSettingsAndPlugins(page, cellMetric);

                cellMetric.errors = errors;
                cellMetric.passed = (errors.length === 0);
                if (cellMetric.passed) {
                    totalMetrics.matrixCellsPassed++;
                    console.log(`>>> CELL RESULT: PASS (0 errors, ${cellMetric.interactions} interactions)`);
                } else {
                    console.error(`>>> CELL RESULT: FAIL (${errors.length} error(s) detected)`);
                    console.error(errors);
                }
            } catch (err) {
                console.error(`>>> CELL EXCEPTION in ${cellTitle}:`, err);
                cellMetric.errors.push({ type: 'test.exception', text: err.message, stack: err.stack });
                cellMetric.passed = false;
            } finally {
                await context.close();
            }

            // Aggregate metrics
            totalMetrics.interactions += cellMetric.interactions;
            totalMetrics.buttonsClicked += cellMetric.buttonsClicked;
            totalMetrics.formsSubmitted += cellMetric.formsSubmitted;
            totalMetrics.modalsExercised += cellMetric.modalsExercised;
            totalMetrics.totalErrors += cellMetric.errors.length;

            cellResults.push(cellMetric);
        }
    }

    await browser.close();

    // Final Report
    console.log('\n===============================================================');
    console.log('COMBINATORIAL E2E VERIFICATION REPORT');
    console.log('===============================================================');
    console.log(`Combinations Tested: ${totalMetrics.matrixCellsPassed} / ${totalMetrics.matrixCells} PASSED`);
    console.log(`Total Interactive Actions: ${totalMetrics.interactions}`);
    console.log(`Buttons & Links Clicked:  ${totalMetrics.buttonsClicked}`);
    console.log(`Forms & Fields Submitted:  ${totalMetrics.formsSubmitted}`);
    console.log(`Modals & Dialogs Exercised: ${totalMetrics.modalsExercised}`);
    console.log(`Total Uncaught Console/HTTP Errors: ${totalMetrics.totalErrors}`);
    console.log('---------------------------------------------------------------');
    console.log('MATRIX BREAKDOWN:');
    for (const r of cellResults) {
        const mark = r.passed ? '✓ PASS' : '✗ FAIL';
        console.log(`  ${mark} | Skin: ${r.skin.padEnd(11)} | Viewport: ${r.viewport.padEnd(20)} | Actions: ${r.interactions} | Errors: ${r.errors.length}`);
    }
    console.log('===============================================================\n');

    if (totalMetrics.matrixCellsPassed !== totalMetrics.matrixCells || totalMetrics.totalErrors > 0) {
        process.exit(1);
    }
}

runCombinatorialMatrix().catch(e => {
    console.error('FATAL RUNNER FAILURE:', e);
    process.exit(1);
});
