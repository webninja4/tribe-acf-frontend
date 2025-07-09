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
    *   Hook `init_acf_form_head` to `wp_head` to ensure ACF scripts and styles are loaded correctly and early.
    *   Hook `output_acf_fields` to `tribe_events_community_form_after_template`.
    *   In `output_acf_fields`, wrap the `acf_form()` output in a `<div id="tribe-acf-fields-wrapper" style="display:none;">` to allow JavaScript to reliably target the fields and prevent a flash of unstyled content.
    *   Implement a single, clean AJAX handler `ajax_save_acf_community_event` that:
        *   Verifies the ACF nonce.
        *   Uses `acf_save_post()` without a second parameter, allowing it to automatically use the serialized `$_POST['acf']` data.
    *   Remove the redundant `save_acf_fields` hook to prevent conflicts.

*   **JavaScript (`assets/js/tribe-acf-frontend-init.js`):**
    *   Use a polling mechanism (`setTimeout`) to reliably wait for both the `#tribe-acf-fields-wrapper` and the main Tribe Events form (`form[name="tribe-community-event"]`) to be present in the DOM.
    *   Once found, move the ACF wrapper into the main form and make it visible.
    *   Trigger `acf.do_action('append', ...)` to re-initialize all ACF field JavaScript (e.g., Select2, date pickers).
    *   Intercept the main form's `submit` event.
    *   Use jQuery's `.serialize()` method on the ACF wrapper to collect all field data reliably.
    *   Send the serialized data, along with the post ID and nonce, to the custom AJAX endpoint.
    *   On a successful AJAX response, programmatically re-submit the main Tribe Events form.

## Failed Approaches (and why they failed)

1.  **Manual AJAX Data Collection & `setInterval`:**
    *   **Problem:** The previous JavaScript implementation attempted to manually iterate over ACF fields to build a data object and used `setInterval` to find the form. This was unreliable.
    *   **Reason for failure:** Manual data collection is error-prone and often fails to correctly format data for complex fields (like Relationship or Repeater fields). `setInterval` is not ideal for DOM readiness checks and can lead to race conditions or performance issues. The script often failed to find the ACF wrapper or the form at the right time.

2.  **Adding `acf_save_post()` hooks directly to `functions.php` in the child theme:**
    *   **Problem:** Initially caused a PHP parse error due to a syntax mistake. After correction, ACF data still did not save, and debug logs showed "The provided input is not a valid Event type."
    *   **Reason for failure:** This approach conflicted with The Events Calendar Community Events' internal saving mechanisms, as `acf_save_post()` was being called at an inappropriate time or with an invalid post ID in the context of the event submission.

3.  **Directly embedding `acf_form_data()` and `acf_form()` calls within the `edit-event.php` template override:**
    *   **Problem:** ACF fields either disappeared, were duplicated, or, when visible, their data was not saved (`$_POST['acf']` was empty). In some instances, the base event itself failed to save.
    *   **Reason for failure:** Incorrect placement of ACF form elements within the complex HTML structure of the Tribe Events form. ACF's hidden inputs (`acf_form_data()`) and field rendering (`acf_form()`) need to be precisely positioned within the main `<form>` tags to ensure their data is included in the submission. Also, a critical `post_ID` input was inadvertently removed during template modifications, preventing the base event from saving.

4.  **Using the `output_acf_fields()` function within the plugin, hooked to `tribe_events_community_form`:**
    *   **Problem:** ACF fields were not showing, and debug logs indicated that the `output_acf_fields` function was not being called.
    *   **Reason for failure:** The `tribe_events_community_form` hook, while seemingly appropriate, fires at a point in the template rendering process where ACF's output might be overwritten or not correctly integrated into the final HTML form structure. There were also issues with repeatedly adding and removing this hook, leading to inconsistent states.

5.  **Attempting to load ACF scripts via `init` or `wp_enqueue_scripts` hooks without specific page targeting:**
    *   **Problem:** ACF fields appeared as containers but were not populated with selectable options.
    *   **Reason for failure:** The `acf_form_head()` function, which enqueues necessary ACF scripts and styles, was not being called at the correct time or on the correct page context, preventing the ACF JavaScript from initializing the fields properly.

6.  **Duplicate `do_action` call in child theme's `edit-event.php` template override:**
    *   **Problem:** ACF fields were displayed twice on the page; one instance had selectable options, the other did not.
    *   **Reason for failure:** The child theme's template was directly calling `do_action('tribe_events_community_form_before_template')` and also manually rendering ACF fields, leading to a redundant output of the ACF form elements.

7.  **Hooking `output_acf_fields` to `tec_events_community_form_after_module_description`:**
    *   **Problem:** ACF fields did not show up.
    *   **Reason for failure:** This hook, while inside the form, did not reliably render the ACF fields, possibly due to dynamic rendering or conflicts with Tribe Events' JavaScript.

8.  **Calling `acf_form_head()` directly within `output_acf_fields` when hooked to `tec_events_community_form_after_module_description`:**
    *   **Problem:** ACF fields did not show up, and sometimes caused critical errors.
    *   **Reason for failure:** Similar to the above, the hook might not be suitable, or the timing of `acf_form_head()` was still incorrect in that specific context.

9.  **Directly modifying `edit-event.php` in the plugin directory to include `acf_form_head()` and `Tribe_ACF_Frontend::output_acf_fields()`:**
    *   **Problem:** ACF fields did not show up, and debug logs were empty.
    *   **Reason for failure:** The plugin's `edit-event.php` was not the active template due to a theme override.

10. **Directly modifying `edit-event.php` in the theme directory to include `acf_form_head()` and `Tribe_ACF_Frontend::output_acf_fields()`:**
    *   **Problem:** ACF fields did not show up, and sometimes caused critical errors.
    *   **Reason for failure:** Even with the correct template, the direct insertion of `acf_form_head()` and `acf_form()` might conflict with Tribe Events' JavaScript-driven form rendering, or the `output_acf_fields` function itself is not being called correctly in this context.

11. **Adding debug logs to `init_acf_form_head()` and `output_acf_fields()`:**
    *   **Problem:** Debug logs were empty when ACF fields were not showing.
    *   **Reason for failure:** Confirmed that the functions were not being called, indicating the template being modified was not the active one, or the hooks were not firing.
