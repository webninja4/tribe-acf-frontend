# Project: Tribe ACF Frontend

This document outlines the development plan and key decisions for the Tribe ACF Frontend WordPress plugin.

## Development Plan

### Phase 1: Foundation and Scaffolding (Complete)

- [X] Create plugin directory structure.
- [X] Create empty plugin files.
- [X] Add standard plugin header to `plugin.php`.
- [X] Create `GEMINI.md` project context file.
- [X] Initialize Git repository and push to GitHub.

## Current Plan (ACF Integration)

The current strategy focuses on integrating ACF fields directly with the main Tribe Community Events form submission.

*   **PHP (`tribe-acf-frontend.php`):**
    *   Hook `init_acf_form_head` to `wp_head` to ensure ACF scripts and styles are loaded correctly.
    *   Hook `output_acf_fields` to `tribe_events_community_form_before_template` to ensure the fields are available before the form is fully rendered.
    *   In `output_acf_fields`, wrap the `acf_form()` output in a hidden div for JS targeting.
    *   Add a new action hook to `tribe_community_events_event_submitted`.
    *   In this new hook, retrieve the `post_id` of the event that was just submitted/updated by Tribe.
    *   Call `acf_save_post($post_id)` to save the ACF fields, ensuring they are associated with the correct event post.

*   **JavaScript (`assets/js/tribe-acf-frontend.js`):**
    *   Use a `MutationObserver` to reliably wait for the Tribe form (identified by `.tribe-community-events form`) to be added to the DOM.
    *   Once the form is found, move the ACF wrapper into it, make it visible, and re-initialize ACF's JavaScript using `acf.do_action('append', ...)`.
    *   The JavaScript no longer intercepts the form submission; ACF fields are submitted along with the main form.

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

6.  **AJAX Submission of ACF fields followed by programmatic form re-submission:**
    *   **Problem:** Form submitted and redirected, but ACF data was not consistently saved or associated with the correct event post, leading to blank forms on edit.
    *   **Reason for failure:** Timing issues and conflicts with Tribe Community Events' internal post-saving mechanisms. The programmatic re-submission of the main form after an AJAX save of ACF fields did not guarantee that Tribe would correctly associate the ACF data with the newly created or updated event post. This approach was overly complex and prone to race conditions. The solution was to allow the main Tribe form to handle the submission of all data, including ACF fields, and then use a PHP hook (`tribe_community_events_event_submitted`) to explicitly save the ACF data after Tribe has processed the event post.