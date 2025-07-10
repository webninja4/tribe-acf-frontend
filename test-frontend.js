const { chromium } = require('playwright');

async function testFrontend() {
    const browser = await chromium.launch({ 
        headless: false, // Set to true for headless mode
        slowMo: 1000 // Slow down actions for better visibility
    });
    
    const page = await browser.newPage();
    
    try {
        // Navigate to your local WordPress site
        // Update this URL to match your local development environment
        await page.goto('http://board-buddy.local/events/community/add/');
        
        console.log('Page loaded successfully');
        
        // Wait for the Tribe Events form to load
        await page.waitForSelector('.tribe-community-events form', { timeout: 10000 });
        console.log('Tribe Events form found');
        
        // Check if ACF fields are present
        const acfWrapper = await page.$('#tribe-acf-fields-wrapper');
        if (acfWrapper) {
            console.log('ACF fields wrapper found');
            
            // Check if ACF fields are visible
            const isVisible = await acfWrapper.isVisible();
            console.log('ACF fields visible:', isVisible);
            
            if (!isVisible) {
                console.log('ACF fields are hidden - this is expected behavior');
            }
        } else {
            console.log('ACF fields wrapper not found');
        }
        
        // Take a screenshot
        await page.screenshot({ 
            path: 'frontend-test-screenshot.png',
            fullPage: true 
        });
        console.log('Screenshot saved as frontend-test-screenshot.png');
        
        // Wait a bit to see the page
        await page.waitForTimeout(5000);
        
    } catch (error) {
        console.error('Error during testing:', error);
        await page.screenshot({ 
            path: 'error-screenshot.png',
            fullPage: true 
        });
    } finally {
        await browser.close();
    }
}

// Run the test
testFrontend().catch(console.error); 