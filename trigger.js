const puppeteer = require('puppeteer');

(async () => {
    console.log("Launching headless Chrome wrapper...");
    const browser = await puppeteer.launch({ 
        headless: "new",
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    const page = await browser.newPage();

    // Set a normal browser signature so it looks like a real computer visitor
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

    try {
        console.log("Loading target endpoint...");
        // Direct link to your script
        await page.goto('https://ekjholokekolkata.page.gd/cron.php', {
            waitUntil: 'networkidle2', 
            timeout: 45000
        });

        // Sleep for 6 seconds to let the aes.js run, generate the cookie, and reload
        await new Promise(r => setTimeout(r, 6000));

        const finalUrl = page.url();
        console.log(`Landed successfully on: ${finalUrl}`);
        
        const content = await page.content();
        if (content.includes("aes.js") || content.includes("Javascript to work")) {
            console.log("Execution Status: Blocked by security gate.");
        } else {
            console.log("Execution Status: Success! Script parsed cleanly.");
        }

    } catch (error) {
        console.log(`Network error encountered: ${error.message}`);
    }

    await browser.close();
})();
