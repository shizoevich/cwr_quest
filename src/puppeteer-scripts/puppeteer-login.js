const  chromium = require('puppeteer');
const fs = require('fs');
const path = require('path');

(async function() {
    const [login, password] = process.argv.slice(2);
    
    if (!login || !password) {
        return;
    }

    let browser = null;
    let page = null;
    try {
        const launchOptions = {
            headless: true,
            executablePath: '/usr/bin/chromium-browser', // comment executablePath during the local testing
            args: [ '--proxy-server=http://108.62.246.74:48549', '--disable-gpu', '--disable-setuid-sandbox', '--no-sandbox', '--no-zygote' ],
        };
        browser = await chromium.launch(launchOptions);

        page = await browser.newPage();

        await page.setViewport({ width: 1280, height: 720 });

        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36');
    
        await page.authenticate({ username:'cwr_5.04.2023', password: 'WqOcqcstfWCI1' });
 
        await page.goto('https://pm.officeally.com/pm/Login.aspx', {timeout: 0});

        await new Promise(r => setTimeout(r, 5000));

        await handleLogin(page);
        await browser.close();
    } catch(e) {
        if (page) {
            const screenshotsFolder = path.resolve(__dirname, 'images');
            if (!fs.existsSync(screenshotsFolder)) {
                fs.mkdirSync(screenshotsFolder);
            }
            const now = new Date();
            const dateTimeString = `${now.getFullYear()}-${now.getMonth() + 1}-${now.getDate()}_${now.getHours()}-${now.getMinutes()}`;
            const screenshotPath = `${screenshotsFolder}/error-screenshot_${dateTimeString}.png`;
            // Capture and save a screenshot
            await page.screenshot({ path: screenshotPath });
        }
        if (browser) {
            await browser.close();
        }
        console.log(e);
    }

    async function handleLogin(page) {
        await page.type('#username', login);
        await page.type('#password', password);
        await page.click('button[type="submit"][data-action-button-primary="true"]', {timeout: 0});

        try {
            await page.waitForSelector('#MainTabs');
            const cookies = await page.cookies();
            console.log(JSON.stringify(cookies));
        } catch(err) {
            await page.waitForSelector('#currentPassword', '#newPassword');
            await page.click('a[href="/account/"]');

            // wait until 'products' page will be opened
            await page.waitForSelector('.product');
            await page.click('.product:nth-child(2)');

            // wait until 'default' page will be opened
            await page.waitForSelector('#MainTabs');
            const cookies = await page.cookies();
            console.log(JSON.stringify(cookies));
        }
    }
}());
