# Gemini CLI + Playwright Frontend Testing

This setup allows Gemini CLI to view and test frontend webpages using Playwright.

## Quick Start

### 1. Run Basic Test
```bash
npm test
```

### 2. Run Full Gemini Test Suite
```bash
npm run test:gemini
```

### 3. Quick Screenshot
```bash
npm run screenshot
```

### 4. Headless Testing (for CI/CD)
```bash
npm run test:headless
```

## What Gemini CLI Can Do

With this Playwright setup, Gemini CLI can:

### 🌐 **View Frontend Webpages**
- Navigate to any URL in your WordPress site
- Take screenshots of pages
- View the rendered HTML and CSS

### 🔍 **Test Form Functionality**
- Check if Tribe Events forms load correctly
- Verify ACF field integration
- Test form submission workflows

### 🐛 **Debug JavaScript Issues**
- Monitor browser console logs
- Catch JavaScript errors
- Validate AJAX functionality

### 📸 **Capture Visual Evidence**
- Take full-page screenshots
- Document UI states
- Create visual regression tests

### ⚡ **Automated Testing**
- Run tests in headless mode
- Generate test reports
- Validate plugin functionality

## Usage Examples

### Basic Page Viewing
```javascript
const { GeminiFrontendTester } = require('./gemini-frontend-tester.js');
const tester = new GeminiFrontendTester();

await tester.initialize();
await tester.navigateToPage('http://board-buddy.local/events/community/add/');
await tester.takeScreenshot('community-add-page');
await tester.cleanup();
```

### Testing ACF Integration
```javascript
await tester.testACFIntegration();
// Checks for:
// - ACF wrapper presence
// - Field visibility
// - JavaScript loading
// - AJAX functionality
```

### Form Testing
```javascript
await tester.fillFormFields();
await tester.takeScreenshot('form-filled');
// Fills basic event fields and ACF fields
```

## Configuration

### Update URLs
Edit `gemini-frontend-tester.js` to match your local development URLs:

```javascript
const testUrls = [
    'http://your-site.local/events/community/add/',
    'http://your-site.local/events/community/edit/',
    'http://your-site.local/events/'
];
```

### Browser Options
Modify browser launch options in `initialize()`:

```javascript
this.browser = await chromium.launch({ 
    headless: false, // Set to true for headless mode
    slowMo: 500, // Adjust speed for debugging
    args: ['--disable-web-security']
});
```

## Integration with Gemini CLI

When working with Gemini CLI, you can:

1. **Ask Gemini to run tests**: "Run the frontend test suite"
2. **Request screenshots**: "Take a screenshot of the community events form"
3. **Debug issues**: "Test the ACF integration and show me any errors"
4. **Validate changes**: "Check if my recent changes broke the frontend"

## Troubleshooting

### Common Issues

1. **URL not accessible**: Update URLs in the test script
2. **Browser not launching**: Ensure Playwright is installed (`npx playwright install`)
3. **Screenshots not saving**: Check file permissions in the project directory
4. **Tests timing out**: Increase timeout values in the script

### Debug Mode
Run with verbose logging:
```bash
DEBUG=pw:api npm run test:gemini
```

## Files Created

- `test-frontend.js` - Basic Playwright test
- `gemini-frontend-tester.js` - Comprehensive test suite
- `package.json` - NPM scripts for easy execution
- `GEMINI-CLI-README.md` - This documentation

## Next Steps

1. Customize URLs for your environment
2. Add specific test cases for your ACF fields
3. Integrate with your development workflow
4. Set up automated testing in CI/CD 