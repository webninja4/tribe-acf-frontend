const { chromium } = require('playwright');

/**
 * Gemini CLI Frontend Tester for Tribe ACF Frontend Plugin
 * 
 * This script allows Gemini CLI to:
 * - View frontend webpages
 * - Test form functionality
 * - Capture screenshots
 * - Debug JavaScript issues
 * - Validate ACF field integration
 */

class GeminiFrontendTester {
    constructor() {
        this.browser = null;
        this.page = null;
        this.screenshots = [];
    }

    async initialize() {
        this.browser = await chromium.launch({ 
            headless: false, // Show browser for visual inspection
            slowMo: 500, // Slow down for better observation
            args: ['--disable-web-security', '--disable-features=VizDisplayCompositor']
        });
        
        this.page = await browser.newPage();
        
        // Set viewport for consistent testing
        await this.page.setViewportSize({ width: 1280, height: 720 });
        
        // Enable console logging
        this.page.on('console', msg => console.log('Browser Console:', msg.text()));
        
        // Enable error logging
        this.page.on('pageerror', error => console.error('Page Error:', error));
        
        console.log('✅ Gemini Frontend Tester initialized');
    }

    async navigateToPage(url) {
        try {
            console.log(`🌐 Navigating to: ${url}`);
            await this.page.goto(url, { waitUntil: 'networkidle' });
            console.log('✅ Page loaded successfully');
            return true;
        } catch (error) {
            console.error('❌ Failed to load page:', error.message);
            return false;
        }
    }

    async testTribeEventsForm() {
        console.log('🔍 Testing Tribe Events form...');
        
        try {
            // Wait for the form to load
            await this.page.waitForSelector('.tribe-community-events form', { timeout: 15000 });
            console.log('✅ Tribe Events form found');
            
            // Check form elements
            const formElements = await this.page.$$('.tribe-community-events form input, .tribe-community-events form select, .tribe-community-events form textarea');
            console.log(`📝 Found ${formElements.length} form elements`);
            
            return true;
        } catch (error) {
            console.error('❌ Tribe Events form test failed:', error.message);
            return false;
        }
    }

    async testACFIntegration() {
        console.log('🔍 Testing ACF integration...');
        
        try {
            // Check for ACF wrapper
            const acfWrapper = await this.page.$('#tribe-acf-fields-wrapper');
            if (!acfWrapper) {
                console.log('⚠️ ACF wrapper not found');
                return false;
            }
            
            console.log('✅ ACF wrapper found');
            
            // Check visibility (should be hidden initially)
            const isVisible = await acfWrapper.isVisible();
            console.log(`👁️ ACF wrapper visible: ${isVisible}`);
            
            // Check for ACF fields inside wrapper
            const acfFields = await this.page.$$('#tribe-acf-fields-wrapper .acf-field');
            console.log(`📋 Found ${acfFields.length} ACF fields`);
            
            // Check for ACF JavaScript
            const acfJSLoaded = await this.page.evaluate(() => {
                return typeof acf !== 'undefined';
            });
            console.log(`🔧 ACF JavaScript loaded: ${acfJSLoaded}`);
            
            return true;
        } catch (error) {
            console.error('❌ ACF integration test failed:', error.message);
            return false;
        }
    }

    async testJavaScriptFunctionality() {
        console.log('🔍 Testing JavaScript functionality...');
        
        try {
            // Check if our custom script is loaded
            const scriptLoaded = await this.page.evaluate(() => {
                return typeof tribe_acf_frontend_ajax !== 'undefined';
            });
            console.log(`📜 Custom script loaded: ${scriptLoaded}`);
            
            // Check for AJAX URL
            if (scriptLoaded) {
                const ajaxUrl = await this.page.evaluate(() => {
                    return tribe_acf_frontend_ajax.ajax_url;
                });
                console.log(`🔗 AJAX URL: ${ajaxUrl}`);
            }
            
            return true;
        } catch (error) {
            console.error('❌ JavaScript functionality test failed:', error.message);
            return false;
        }
    }

    async takeScreenshot(name = 'frontend-test') {
        const filename = `${name}-${Date.now()}.png`;
        await this.page.screenshot({ 
            path: filename,
            fullPage: true 
        });
        this.screenshots.push(filename);
        console.log(`📸 Screenshot saved: ${filename}`);
        return filename;
    }

    async fillFormFields() {
        console.log('✍️ Filling form fields...');
        
        try {
            // Fill basic event fields
            await this.page.fill('#post_title', 'Test Event via Gemini CLI');
            await this.page.fill('#post_content', 'This is a test event created by Gemini CLI for testing purposes.');
            
            // Try to fill ACF fields if they're visible
            const acfTextFields = await this.page.$$('#tribe-acf-fields-wrapper input[type="text"]');
            for (let i = 0; i < Math.min(acfTextFields.length, 3); i++) {
                await acfTextFields[i].fill(`Test ACF Field ${i + 1}`);
            }
            
            console.log('✅ Form fields filled');
            return true;
        } catch (error) {
            console.error('❌ Failed to fill form fields:', error.message);
            return false;
        }
    }

    async runFullTest() {
        console.log('🚀 Starting Gemini Frontend Test Suite...\n');
        
        await this.initialize();
        
        // Test different pages
        const testUrls = [
            'http://board-buddy.local/events/community/add/',
            'http://board-buddy.local/events/community/edit/',
            'http://board-buddy.local/events/'
        ];
        
        for (const url of testUrls) {
            console.log(`\n📋 Testing URL: ${url}`);
            
            const loaded = await this.navigateToPage(url);
            if (!loaded) continue;
            
            // Take initial screenshot
            await this.takeScreenshot(`initial-${url.split('/').pop()}`);
            
            // Run tests
            await this.testTribeEventsForm();
            await this.testACFIntegration();
            await this.testJavaScriptFunctionality();
            
            // Fill form if it's an add/edit page
            if (url.includes('add') || url.includes('edit')) {
                await this.fillFormFields();
                await this.takeScreenshot(`filled-${url.split('/').pop()}`);
            }
            
            // Wait a moment
            await this.page.waitForTimeout(2000);
        }
        
        console.log('\n📊 Test Summary:');
        console.log(`📸 Screenshots taken: ${this.screenshots.length}`);
        console.log('📁 Screenshots saved in current directory');
        
        await this.cleanup();
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
            console.log('🧹 Browser closed');
        }
    }
}

// Export for use with Gemini CLI
module.exports = { GeminiFrontendTester };

// Run if called directly
if (require.main === module) {
    const tester = new GeminiFrontendTester();
    tester.runFullTest().catch(console.error);
} 