<?php
/**
 * Plugin Name: Tribe ACF Frontend
 * Plugin URI:  https://github.com/webninja4/tribe-acf-frontend
 * Description: Integrates Advanced Custom Fields (ACF) with The Events Calendar Community Events frontend submission form.
 * Version:     1.0.0
 * Author:      Paul Steele | Project A, Inc.
 * Author URI:  https://projecta.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: tribe-acf-frontend
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin class.
 */
class Tribe_ACF_Frontend {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'check_dependencies' ) );
        add_action( 'wp_head', array( $this, 'init_acf_form_head' ) );
        add_action( 'tribe_events_community_form_before_template', array( $this, 'output_acf_fields' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Enqueue scripts and styles.
     */
    public function enqueue_scripts() {
        if ( is_singular( 'tribe_events' ) || ( function_exists( 'tribe_is_community_edit_event_page' ) && tribe_is_community_edit_event_page() ) ) {
            wp_enqueue_script(
                'tribe-acf-frontend-js',
                plugins_url( 'assets/js/tribe-acf-frontend.js', __FILE__ ),
                array( 'jquery', 'acf-input' ),
                null,
                true
            );

            wp_localize_script(
                'tribe-acf-frontend-js',
                'tribe_acf_frontend_ajax',
                array( 
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'redirect_url' => function_exists('tribe_community_events_events_list_url') ? tribe_community_events_events_list_url() : ''
                )
            );
        }
    }

    /**
     * Check if required plugins are active.
     */
    public function check_dependencies() {
        if ( ! class_exists( 'ACF' ) ) {
            add_action( 'admin_notices', array( $this, 'acf_missing_notice' ) );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            return;
        }

        if ( ! class_exists( 'Tribe__Events__Main' ) ) {
            add_action( 'admin_notices', array( $this, 'tec_missing_notice' ) );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            return;
        }

        if ( ! class_exists( 'Tribe__Events__Community__Main' ) ) {
            add_action( 'admin_notices', array( $this, 'tec_community_events_missing_notice' ) );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            return;
        }
    }

    /**
     * Admin notice for missing ACF.
     */
    public function acf_missing_notice() {
        echo '<div class="notice notice-error"><p><strong>Tribe ACF Frontend</strong> requires <strong>Advanced Custom Fields</strong> to be installed and active.</p></div>';
    }

    /**
     * Admin notice for missing The Events Calendar.
     */
    public function tec_missing_notice() {
        echo '<div class="notice notice-error"><p><strong>Tribe ACF Frontend</strong> requires <strong>The Events Calendar</strong> to be installed and active.</p></div>';
    }

    /**
     * Admin notice for missing The Events Calendar Community Events.
     */
    public function tec_community_events_missing_notice() {
        echo '<div class="notice notice-error"><p><strong>Tribe ACF Frontend</strong> requires <strong>The Events Calendar Community Events</strong> to be installed and active.</p></div>';
    }

    /**
     * Call acf_form_head() early.
     */
    public function init_acf_form_head() {
        error_log( 'Tribe ACF Frontend: init_acf_form_head called.' );
        // Only run on the frontend and if ACF is active.
        if ( ! is_admin() && class_exists( 'ACF' ) ) {
            acf_form_head();
        }
    }

    /**
     * Output ACF fields on the frontend submission form.
     */
    public function output_acf_fields( $tribe_event_id = null ) {
        error_log( 'Tribe ACF Frontend: output_acf_fields called.' );
        // Ensure ACF is active and we are on the frontend.
        if ( ! class_exists( 'ACF' ) || is_admin() ) {
            return;
        }

        $post_id = 'new_post'; // Default for new events.

        // If an event ID is provided, use it.
        if ( ! empty( $tribe_event_id ) ) {
            $post_id = $tribe_event_id;
        }

        error_log( 'Tribe ACF Frontend: output_acf_fields function called. Current post_id: ' . $post_id );

        // Define ACF form settings.
        $acf_settings = array(
            'post_id'       => $post_id,
            'field_groups'  => array(), // ACF will automatically detect field groups assigned to 'tribe_events'.
            'form'          => false,   // Do not wrap in a <form> tag, as Community Events provides its own.
            'return'        => '', // Let Tribe handle the redirect naturally
            'html_before_fields' => '',
            'html_after_fields'  => '',
            'submit_value'  => __( 'Update Event', 'tribe-acf-frontend' ), // This won't be used as 'form' is false.
            'updated_message' => __( 'Event updated.', 'tribe-acf-frontend' ), // This won't be used as 'form' is false.
        );

        // Output the ACF form, wrapped for JS targeting.
        echo '<div id="tribe-acf-fields-wrapper" style="display:none;">';
        acf_form( $acf_settings );
        echo '</div>';
    }

    /**
     * Save ACF fields after Tribe Community Events form submission.
     *
     * @param int $event_id The ID of the event post.
     */
    public function save_acf_community_event( $event_id ) {
        // Ensure ACF is active and we have a valid event ID.
        if ( ! class_exists( 'ACF' ) || empty( $event_id ) ) {
            return;
        }

        // Check if ACF data was submitted.
        if ( empty( $_POST['acf'] ) ) {
            return;
        }

        // Save ACF fields. ACF will automatically handle the nonce and validation
        // since the fields are part of the main form submission.
        acf_save_post( $event_id );
    }
}

// Initialize the plugin.
$tribe_acf_frontend = new Tribe_ACF_Frontend();

// Add action to save ACF fields after Tribe Community Events form submission.
add_action( 'tribe_community_events_event_submitted', array( $tribe_acf_frontend, 'save_acf_community_event' ) );

//======================================================================
// FUNCTIONS MOVED FROM THEME
//======================================================================

// Shortcode for event related documents
function event_related_documents_shortcode() {
    global $post;
    $docs = get_field('related_documents', $post->ID);

    if (!$docs || !is_array($docs)) {
        return '<p>No documents associated with this event.</p>';
    }

    $output = '<ul class="event-documents">';
    foreach ($docs as $doc) {
        $title = get_the_title($doc);
        $link = get_permalink($doc);
        $output .= '<li><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></li>';
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode('event_documents', 'event_related_documents_shortcode');

// Custom shortcode for Events Calendar add to calendar links
function tec_add_to_calendar_shortcode() {
    if (!is_singular('tribe_events')) {
        return '';
    }
    
    $event_id = get_the_ID();
    $output = '';
    
    // Get event details
    $title = get_the_title($event_id);
    $description = wp_strip_all_tags(get_the_excerpt($event_id));
    $start_date = '';
    $end_date = '';
    $location = '';
    
    // Try TEC functions first
    if (function_exists('tribe_get_start_date')) {
        $start_date = tribe_get_start_date($event_id, false, 'Y-m-d H:i:s');
        $end_date = tribe_get_end_date($event_id, false, 'Y-m-d H:i:s');
    }
    
    // Get location
    if (function_exists('tribe_get_venue')) {
        $location = tribe_get_venue($event_id);
    }
    
    // Fallback to meta fields if TEC functions don't work
    if (empty($start_date)) {
        $start_date = get_post_meta($event_id, '_EventStartDate', true);
        $end_date = get_post_meta($event_id, '_EventEndDate', true);
    }
    
    if ($start_date && $end_date) {
        // Get WordPress timezone
        $wp_timezone = wp_timezone();
        
        // Create DateTime objects with proper timezone handling
        $start_dt = new DateTime($start_date, $wp_timezone);
        $end_dt = new DateTime($end_date, $wp_timezone);
        
        // Format dates for different calendar systems
        // For UTC format (Google Calendar, ICS)
        $start_utc = clone $start_dt;
        $start_utc->setTimezone(new DateTimeZone('UTC'));
        $start_formatted = $start_utc->format('Ymd\THis\Z');
        
        $end_utc = clone $end_dt;
        $end_utc->setTimezone(new DateTimeZone('UTC'));
        $end_formatted = $end_utc->format('Ymd\THis\Z');
        
        // Google Calendar URL
        $google_url = 'https://calendar.google.com/calendar/render?action=TEMPLATE';
        $google_url .= '&text=' . urlencode($title);
        $google_url .= '&dates=' . $start_formatted . '/' . $end_formatted;
        $google_url .= '&details=' . urlencode($description);
        $google_url .= '&location=' . urlencode($location);
        
        // Outlook 365 URL
        $outlook_url = 'https://outlook.live.com/calendar/0/deeplink/compose?';
        $outlook_url .= 'subject=' . urlencode($title);
        $outlook_url .= '&startdt=' . $start_utc->format('Y-m-d\TH:i:s.000\Z');
        $outlook_url .= '&enddt=' . $end_utc->format('Y-m-d\TH:i:s.000\Z');
        $outlook_url .= '&body=' . urlencode($description);
        $outlook_url .= '&location=' . urlencode($location);
        
        // ICS download URL (we'll create this endpoint)
        $ics_url = add_query_arg(array(
            'download_ics' => '1',
            'event_id' => $event_id
        ), get_permalink($event_id));
        
        $output .= '<div class="tribe-events-cal-links">';
        $output .= '<a href="' . esc_url($google_url) . '" target="_blank" class="tribe-gcal">+ Google Calendar</a> ';
        $output .= '<a href="' . esc_url($outlook_url) . '" target="_blank" class="tribe-outlook">+ Outlook 365</a> ';
        
        // Try TEC's built-in iCal link first
        if (function_exists('tribe_get_ical_link')) {
            $ical_link = tribe_get_ical_link();
            if ($ical_link) {
                $output .= '<a href="' . esc_url($ical_link) . '" target="_blank" class="tribe-ical">+ iCal Export</a> ';
            }
        }
        
        // Add our custom ICS download
        $output .= '<a href="' . esc_url($ics_url) . '" class="tribe-ics" download>+ Download ICS</a>';
        $output .= '</div>';
    }
    
    return $output;
}
add_shortcode('event_calendar_links', 'tec_add_to_calendar_shortcode');

// Handle ICS file download
function handle_ics_download() {
    if (isset($_GET['download_ics']) && isset($_GET['event_id'])) {
        $event_id = intval($_GET['event_id']);
        
        if (get_post_type($event_id) === 'tribe_events') {
            $title = get_the_title($event_id);
            $description = wp_strip_all_tags(get_the_excerpt($event_id));
            $start_date = '';
            $end_date = '';
            $location = '';
            
            // Get event details
            if (function_exists('tribe_get_start_date')) {
                $start_date = tribe_get_start_date($event_id, false, 'Y-m-d H:i:s');
                $end_date = tribe_get_end_date($event_id, false, 'Y-m-d H:i:s');
            }
            
            if (function_exists('tribe_get_venue')) {
                $location = tribe_get_venue($event_id);
            }
            
            if ($start_date && $end_date) {
                // Get WordPress timezone
                $wp_timezone = wp_timezone();
                
                // Create DateTime objects with proper timezone handling
                $start_dt = new DateTime($start_date, $wp_timezone);
                $end_dt = new DateTime($end_date, $wp_timezone);
                
                // Convert to UTC for ICS format
                $start_utc = clone $start_dt;
                $start_utc->setTimezone(new DateTimeZone('UTC'));
                $end_utc = clone $end_dt;
                $end_utc->setTimezone(new DateTimeZone('UTC'));
                
                // Create ICS content
                $ics_content = "BEGIN:VCALENDAR\r\n";
                $ics_content .= "VERSION:2.0\r\n";
                $ics_content .= "PRODID:-//Your Site//Event Calendar//EN\r\n";
                $ics_content .= "BEGIN:VEVENT\r\n";
                $ics_content .= "UID:" . md5($event_id . $start_date) . "@" . $_SERVER['HTTP_HOST'] . "\r\n";
                $ics_content .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
                $ics_content .= "DTSTART:" . $start_utc->format('Ymd\THis\Z') . "\r\n";
                $ics_content .= "DTEND:" . $end_utc->format('Ymd\THis\Z') . "\r\n";
                $ics_content .= "SUMMARY:" . $title . "\r\n";
                if ($description) {
                    $ics_content .= "DESCRIPTION:" . str_replace(array("\r\n", "\n", "\r"), "\\n", $description) . "\r\n";
                }
                if ($location) {
                    $ics_content .= "LOCATION:" . $location . "\r\n";
                }
                $ics_content .= "END:VEVENT\r\n";
                $ics_content .= "END:VCALENDAR\r\n";
                
                // Set headers for download
                header('Content-Type: text/calendar; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . sanitize_file_name($title) . '.ics"');
                header('Content-Length: ' . strlen($ics_content));
                
                echo $ics_content;
                exit;
            }
        }
    }
}
add_action('template_redirect', 'handle_ics_download');

// Shortcode for event related committees
function event_related_committee_shortcode() {
    global $post;
    $committees = get_field('related_committee', $post->ID);

    if (!$committees || !is_array($committees)) {
        return '<p>No committees associated with this event.</p>';
    }

    $output = '<ul class="event-committees">';
    foreach ($committees as $committee) {
        $title = get_the_title($committee);
        $link = get_permalink($committee);
        $output .= '<li><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></li>';
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode('event_committees', 'event_related_committee_shortcode');

/*
 * Template Override Instructions:
 *
 * If you need to customize the output of the ACF fields, you can override the
 * 'tribe_community_events_before_event_details' action by removing it and
 * adding your own custom action.
 *
 * Example (in your theme's functions.php or a custom plugin):
 *
 * remove_action( 'tribe_community_events_before_event_details', array( Tribe_ACF_Frontend::get_instance(), 'output_acf_fields' ) );
 * add_action( 'tribe_community_events_before_event_details', 'my_custom_acf_output' );
 *
 * function my_custom_acf_output() {
 *     // Your custom logic to output ACF fields.
 *     // Remember to call acf_form_head() early if you're not using the plugin's init hook.
 *     // You'll need to replicate the post_id detection logic if you want to use acf_form().
 *     // Example:
 *     $post_id = 'new_post';
 *     if ( isset( $_GET['event_id'] ) && ! empty( $_GET['event_id'] ) ) {
 *         $event_id = absint( $_GET['event_id'] );
 *         if ( get_post_type( $event_id ) === Tribe__Events__Main::POSTTYPE ) {
 *             $post_id = $event_id;
 *         }
 *     }
 *     acf_form( array( 'post_id' => $post_id, 'form' => false ) );
 * }
 */
