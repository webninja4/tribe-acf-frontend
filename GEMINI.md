# Project: Tribe ACF Frontend

This document outlines the development plan and key decisions for the Tribe ACF Frontend WordPress plugin.

## Development Plan

### Phase 1: Foundation and Scaffolding (Complete)

- [X] Create plugin directory structure.
- [X] Create empty plugin files.
- [X] Add standard plugin header to `plugin.php`.
- [X] Create `GEMINI.md` project context file.
- [X] Initialize Git repository and push to GitHub.

### Phase 2: Admin Settings UI (Complete)

- [X] Implement `roles-loader.php` to fetch roles and post types.
- [X] Register the `Settings > Editor UI Cleaner` admin page.
- [X] Build the settings form in `templates/settings-form.php`.
- [X] Implement Settings API for saving data.

### Phase 3: Core Hiding Logic (Complete)

- [X] Define the master list of controllable UI elements.
- [X] Implement Classic Editor hiding logic in `classic-hooks.php`.
- [X] Implement Block Editor hiding logic in `block-hooks.php`.
- [X] Develop the main controller to apply rules based on user/screen.

### Phase 4: Finalization and Repository Push

- [ ] Implement security best practices (nonces, sanitization, escaping).
- [ ] Create `uninstall.php` script.
- [X] Populate `readme.txt`.
- [ ] Push final, stable code to GitHub.

## Key Decisions

*   **Framework:** Standard WordPress functions and APIs. No external frameworks to keep it lightweight.
*   **Settings Storage:** A single option in the `wp_options` table, storing a multidimensional array.
*   **UI Hiding:** A combination of `remove_meta_box()` for classic metaboxes and injected CSS (`display: none !important;`) for other elements, especially in the Block Editor. Dynamic detection of editor type (Classic vs. Block) based on rendered HTML for accurate rule application.

## Current Plan (ACF Integration)

The current strategy focuses on a robust AJAX-based solution to integrate ACF fields into the Community Events form.

*   **PHP (`tribe-acf-frontend.php`):**
    *   Hook `init_acf_form_head` to `wp_head` to ensure ACF scripts and styles are loaded correctly.
    *   Hook `output_acf_fields` to `tribe_events_community_form_before_template` to ensure the fields are available before the form is fully rendered.
    *   In `output_acf_fields`, wrap the `acf_form()` output in a hidden div for JS targeting.
    *   Use `wp_localize_script` in the `enqueue_scripts` function to safely pass the `admin-ajax.php` URL to the frontend script, preventing 403 errors.
    *   Implement a clean AJAX handler (`ajax_save_acf_community_event`) that verifies the ACF nonce and uses `acf_save_post()`.

*   **JavaScript (`assets/js/tribe-acf-frontend.js`):**
    *   Use a `MutationObserver` to reliably wait for the Tribe form (identified by `.tribe-community-events form`) to be added to the DOM.
    *   Once the form is found, move the ACF wrapper into it, make it visible, and re-initialize ACF's JavaScript using `acf.do_action('append', ...)`.
    *   Intercept the form's `submit` event.
    *   Use the localized AJAX URL for the request.
    *   Find the nonce using a robust attribute selector (`input[name="_wpnonce"]`) within the ACF wrapper.
    *   Use jQuery's `.serialize()` method to collect all field data reliably.
    *   On successful AJAX save, programmatically re-submit the main Tribe Events form.

## Failed Approaches (and why they failed)

1.  **Incorrect AJAX URL (`ajaxurl`):**
    *   **Problem:** Form submission resulted in a `403 Forbidden` error.
    *   **Reason for failure:** The global `ajaxurl` JavaScript variable is not always available on the frontend in WordPress. The AJAX request was being sent to an incorrect URL, causing the server to reject it. The fix was to use `wp_localize_script` in PHP to provide the correct URL.

2.  **Incorrect Form Selector in JS:**
    *   **Problem:** ACF fields were rendered in the DOM but were not visible on the form. Console logs showed that the JavaScript could not find the Tribe Events form.
    *   **Reason for failure:** The selectors used to find the form (`form[name="tribe-community-event"]` and later `form#tribe-community-events-form`) were too specific and incorrect. The form's ID and name attributes were not stable or predictable. The fix was to use a more general selector (`.tribe-community-events form`) which successfully identified the form within its container.

3.  **Polling with `setInterval` / `setTimeout`:**
    *   **Problem:** The initial JavaScript implementation used polling to check for the existence of the form, which was unreliable and led to timeout errors.
    *   **Reason for failure:** The Tribe Events form is rendered dynamically, and its appearance in the DOM was not predictable. Polling is inefficient and prone to race conditions. The fix was to use a `MutationObserver` to react instantly when the form is added to the DOM.

4.  **Manual AJAX Data Collection:**
    *   **Problem:** An early version of the script attempted to manually iterate over ACF fields to build a data object for AJAX submission.
    *   **Reason for failure:** Manual data collection is error-prone and often fails to correctly format data for complex fields (like Relationship or Repeater fields). The fix was to use jQuery's `.serialize()` method, which is much more reliable.

5.  **Incorrect `acf_form_head()` Hooking:**
    *   **Problem:** ACF fields like Select2 and Date Pickers were not initializing correctly.
    *   **Reason for failure:** `acf_form_head()` was being called too late in the page load. The fix was to move it to the `wp_head` action to ensure all necessary scripts and styles are loaded in the HTML `<head>`.