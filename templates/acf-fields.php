<?php
/**
 * Custom template for outputting ACF fields within the Tribe Community Events form.
 *
 * This template is included as a module in the form layout via the `tec_events_community_form_layout` filter.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get the event ID from the context, if available.
$tribe_event_id = isset( $data['event_id'] ) ? $data['event_id'] : null;

// Call the static method to output ACF fields.
Tribe_ACF_Frontend::output_acf_fields( $tribe_event_id );
