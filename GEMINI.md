# Project: Tribe ACF Frontend

This document outlines the development plan and key decisions for the Tribe ACF Frontend WordPress plugin.

## Development Plan

### Phase 1: Initial Plugin Setup (Complete)

- [X] Create plugin directory structure (`wp-content/plugins/tribe-acf-frontend/`).
- [X] Create main plugin file (`tribe-acf-frontend.php`) with standard header.
- [X] Implement dependency checks for ACF, The Events Calendar, and Community Events.
- [X] Call `acf_form_head()` early in the WordPress lifecycle.
- [X] Hook into `tribe_community_events_before_event_details` to output ACF fields using `acf_form()`.
- [X] Implement logic to dynamically detect and pass the correct post ID to `acf_form()` for new and existing events.
- [X] Add template override instructions in plugin comments.

## Key Decisions

*   **Framework:** Standard WordPress functions and APIs, and ACF's `acf_form()` function.
*   **UI Hiding:** ACF fields are intentionally displayed only on the frontend Community Events submission form, not in the WordPress admin.
*   **Post ID Detection:** Logic is implemented to determine if the form is for a new event (`new_post`) or an existing event (using `$_GET['event_id']` or `$_POST['post_ID']`).

### Phase 5: Settings UI Enhancement

- [ ] Implement a tabbed interface for user roles.
- [ ] Create collapsible sections for post types.
- [ ] Set the default state to show the first role tab and expand the first post type.
- [ ] Add a "Check/Uncheck All" toggle for each post type section.
- [ ] Enqueue dedicated CSS and JavaScript for the new UI.
- [ ] Apply styling to match the WordPress admin theme.

### Phase 6: ACF Integration Debugging and Enhancement

- [ ] Investigate why ACF fields are not showing on the "add event" screen.
- [ ] Debug why ACF fields are not saving when submitting the frontend form.

## Changelog

### 1.1.0 (In Progress)
*   Started work on a major UI overhaul for the settings page.
*   fix: Collapsed all post types by default in the settings UI.
*   fix: Corrected apostrophe rendering in custom CSS helper text.
*   fix: Applied UI rules to roles with `manage_options` capability, excluding only the built-in administrator role.
*   fix: Correctly hide Discussions metabox and add Relevanssi metabox to configurable elements.
*   feat: Added Content Permissions (AME) metabox to configurable elements.

### 1.0.0 - 2025-07-02
*   feat: Complete Phase 4 development tasks, including security enhancements and the addition of an `uninstall.php` script.
*   Initial release.
