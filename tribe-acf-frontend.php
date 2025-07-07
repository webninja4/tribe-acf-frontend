<?php
/**
 * Plugin Name: Tribe ACF Frontend
 * Plugin URI:  https://github.com/webninja4/tribe-acf-frontend
 * Description: Integrates Advanced Custom Fields (ACF) with The Events Calendar Community Events frontend submission form.
 * Version:     1.2.0
 * Author:      Paul
 * Author URI:  https://webninja.com
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

        // These are required for ACF form processing and script loading.
        add_action( 'wp_head', array( $this, 'acf_form_head' ), 1 );
        add_action( 'wp_enqueue_scripts', array( $this, 'acf_form_scripts' ) );

        // Use the hook for the 'Edit Event' form. It passes the event ID.
        add_action( 'tribe_events_community_form_before_template', array( $this, 'display_form_on_edit_page' ) );

        // Use the specific hook for the 'Add Event' form.
        add_action( 'tribe_events_community_add_event_form_fields', array( $this, 'display_form_on_add_page' ) );
    }

    /**
     * Check if required plugins are active.
     */
    public function check_dependencies() {
        if ( ! class_exists( 'ACF' ) || ! class_exists( 'Tribe__Events__Main' ) || ! class_exists( 'Tribe__Events__Community__Main' ) ) {
            add_action( 'admin_notices', array( $this, 'dependencies_missing_notice' ) );
            deactivate_plugins( plugin_basename( __FILE__ ) );
        }
    }

    /**
     * Admin notice for missing dependencies.
     */
    public function dependencies_missing_notice() {
        echo '<div class="notice notice-error"><p><strong>Tribe ACF Frontend</strong> requires <strong>Advanced Custom Fields</strong>, <strong>The Events Calendar</strong>, and <strong>Community Events</strong> to be installed and active.</p></div>';
    }

    /**
     * Call acf_form_head() to handle form submission and validation.
     */
    public function acf_form_head() {
        if ( ! is_admin() && function_exists( 'acf_form_head' ) ) {
            acf_form_head();
        }
    }

    /**
     * Enqueue ACF's form scripts to make fields interactive.
     */
    public function acf_form_scripts() {
        if ( ! is_admin() && function_exists( 'acf_enqueue_form_scripts' ) ) {
            acf_enqueue_form_scripts();
        }
    }

    /**
     * Displays the ACF form on the 'Add New' page.
     */
    public function display_form_on_add_page() {
        $this->render_acf_form( 'new_post' );
    }

    /**
     * Displays the ACF form on the 'Edit' page.
     * This hook reliably passes the event ID.
     */
    public function display_form_on_edit_page( $event_id ) {
        if ( is_numeric( $event_id ) && $event_id > 0 ) {
            $this->render_acf_form( $event_id );
        }
    }

    /**
     * Renders the ACF form with the specified post ID.
     */
    private function render_acf_form( $post_id ) {
        if ( ! function_exists( 'acf_form' ) || is_admin() ) {
            return;
        }

        // Ensure we have a valid post ID or 'new_post'
        if ( 'new_post' !== $post_id ) {
            $post = get_post( $post_id );
            if ( ! $post || 'tribe_events' !== $post->post_type ) {
                // If the ID is invalid, do not render the form.
                return;
            }
        }

        $settings = array(
            'post_id'      => $post_id,
            'form'         => false, // Let Community Events handle the <form> tag and submit buttons.
            'post_title'   => false, // We use the TEC title field.
            'post_content' => false, // We use the TEC content field.
            'uploader'     => 'basic',
            'return'       => '', // Let Community Events handle the redirect on success.
        );

        acf_form( $settings );
    }
}

// Initialize the plugin.
new Tribe_ACF_Frontend();
