const puppeteer = require('puppeteer');

(async () => {
    console.log("Launching headless Chrome wrapper...");
    const browser = await puppeteer.launch({ 
        headless: true, // Updated from deprecated "new" to standard true
        args: [
            '--no-sandbox', 
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage', // CRITICAL: Fixes Docker/Container low-memory crashes
            '--disable-gpu'            // Prevents headless hardware acceleration bugs on Linux
        ]
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

        console.log("Bypassing potential security gates (Sleeping 6 seconds)...");
        // Sleep for 6 seconds to let the aes.js run, generate the cookie, and reload
        await new Promise(r => setTimeout(r, 6000));

        const finalUrl = page.url();
        console.log(`Landed successfully on: ${finalUrl}`);
        
        const content = await page.content();
        
        // Comprehensive check for anti-bot or JS-validation screening text
        if (content.includes("aes.js") || content.includes("Javascript to work") || content.includes("Checking your browser")) {
            console.log("Execution Status: Blocked by security gate.");
        } else {
            console.log("Execution Status: Success! Script parsed cleanly.");
            // Print out the output text of your PHP file (e.g., "Status: Active - Check Complete.")
            const bodyText = await page.evaluate(() => document.body.innerText);
            console.log(`PHP Output: ${bodyText.trim()}`);
        }

    } catch (error) {
        console.log(`Network error encountered: ${error.message}`);
    }

    await browser.close();
    console.log("Browser closed. Cycle finished.");
})();
