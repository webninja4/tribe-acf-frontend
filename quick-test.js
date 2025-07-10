const { chromium } = require('playwright');

async function quickTest() {
    const browser = await chromium.launch({ 
        headless: false,
        slowMo: 1000
    });
    
    const page = await browser.newPage();
    
    try {
        console.log('🌐 Navigating to test page...');
        await page.goto('http://board-buddy.local/events/community/add/');
        
        console.log('📸 Taking screenshot...');
        await page.screenshot({ 
            path: 'quick-test-screenshot.png',
            fullPage: true 
        });
        
        console.log('✅ Screenshot saved as quick-test-screenshot.png');
        
        // Wait a moment to see the page
        await page.waitForTimeout(3000);
        
    } catch (error) {
        console.error('❌ Error:', error.message);
        await page.screenshot({ 
            path: 'error-screenshot.png',
            fullPage: true 
        });
    } finally {
        await browser.close();
        console.log('🧹 Browser closed');
    }
}

quickTest().catch(console.error); 