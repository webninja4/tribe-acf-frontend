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
