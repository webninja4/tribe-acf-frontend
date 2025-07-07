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
*   **Post ID Detection:** Logic is implemented to determine if the form is for a new event (`new_post`) or an existing event (using `$_GET['event_id']` or `$_POST['post_ID']`).

### Phase 2: ACF Integration Debugging and Enhancement

- [ ] Investigate why ACF fields are not showing on the "add event" screen.
- [ ] Debug why ACF fields are not saving when submitting the frontend form.
