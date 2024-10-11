const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    let browser = null;
    let page = null;

    try {
        browser = await puppeteer.launch({
            headless: "new", // Opt-in to the new Headless mode
        });
        page = await browser.newPage();

        await page.setViewport({ width: 1280, height: 720 });

        await page.goto('https://www.google.com.ua');
        await page.click('nonexistent-selector');
    } catch (error) {
        console.error('Error occurred:', error.message);
    } finally {
        try {
            if (page) {
                const screenshotsFolder = './images';
                if (!fs.existsSync(screenshotsFolder)) {
                    fs.mkdirSync(screenshotsFolder);
                }

                const currentDate = new Date().toISOString().split('T')[0];
                const screenshotPath = `${screenshotsFolder}/error-screenshot-${currentDate}.png`;

                // Capture and save a screenshot
                await page.screenshot({ path: screenshotPath });

                console.log(`Screenshot saved: ${screenshotPath}`);
            }
        } finally {
            if (browser) {
                // Close the browser and handle cleanup
                await browser.close();
            }
        }
    }
})();
