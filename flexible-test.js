const { chromium } = require('playwright');

// Common local development URLs - update these for your environment
const TEST_URLS = [
    'http://board-buddy.local/events/community/add/',
    'http://localhost/board-buddy/events/community/add/',
    'http://127.0.0.1/board-buddy/events/community/add/',
    'http://localhost:8080/events/community/add/',
    'http://localhost:3000/events/community/add/'
];

async function flexibleTest(url = null) {
    const browser = await chromium.launch({ 
        headless: false,
        slowMo: 1000
    });
    
    const page = await browser.newPage();
    
    try {
        // Try the provided URL first, then fall back to common URLs
        const urlsToTry = url ? [url] : TEST_URLS;
        
        let success = false;
        for (const testUrl of urlsToTry) {
            try {
                console.log(`🌐 Trying URL: ${testUrl}`);
                await page.goto(testUrl, { waitUntil: 'networkidle', timeout: 10000 });
                console.log(`✅ Successfully loaded: ${testUrl}`);
                success = true;
                break;
            } catch (error) {
                console.log(`❌ Failed to load: ${testUrl} - ${error.message}`);
                continue;
            }
        }
        
        if (!success) {
            console.log('❌ Could not load any of the test URLs');
            console.log('💡 Please update the TEST_URLS array in flexible-test.js with your local development URL');
            return;
        }
        
        // Take a screenshot
        console.log('📸 Taking screenshot...');
        await page.screenshot({ 
            path: 'flexible-test-screenshot.png',
            fullPage: true 
        });
        console.log('✅ Screenshot saved as flexible-test-screenshot.png');
        
        // Test for common elements
        console.log('🔍 Testing for common elements...');
        
        // Check for WordPress
        const wpContent = await page.$('#wp-content');
        if (wpContent) console.log('✅ WordPress content area found');
        
        // Check for Tribe Events
        const tribeEvents = await page.$('.tribe-community-events');
        if (tribeEvents) console.log('✅ Tribe Community Events found');
        
        // Check for ACF wrapper
        const acfWrapper = await page.$('#tribe-acf-fields-wrapper');
        if (acfWrapper) {
            console.log('✅ ACF fields wrapper found');
            const isVisible = await acfWrapper.isVisible();
            console.log(`👁️ ACF wrapper visible: ${isVisible}`);
        } else {
            console.log('⚠️ ACF fields wrapper not found');
        }
        
        // Check for forms
        const forms = await page.$$('form');
        console.log(`📝 Found ${forms.length} forms on the page`);
        
        // Wait a moment to see the page
        console.log('⏳ Waiting 5 seconds to view the page...');
        await page.waitForTimeout(5000);
        
    } catch (error) {
        console.error('❌ Error during testing:', error.message);
        await page.screenshot({ 
            path: 'error-screenshot.png',
            fullPage: true 
        });
    } finally {
        await browser.close();
        console.log('🧹 Browser closed');
    }
}

// Get URL from command line argument
const url = process.argv[2] || null;

console.log('🚀 Starting Flexible Frontend Test...');
console.log('💡 Usage: node flexible-test.js [optional-url]');
console.log('💡 Example: node flexible-test.js http://localhost:8080/events/community/add/');
console.log('');

flexibleTest(url).catch(console.error); 