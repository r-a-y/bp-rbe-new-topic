<?php
/*
Plugin Name: BP Reply By Email - Simple New Topic Email Address
Description: Use a simple-to-share email address to create new forum topics in a group instead of the default, user-hashed email address.
Author: r-a-y
Author URI: http://profiles.wordpress.org/r-a-y
Version: 0.1-alpha
License: GPLv2 or later
*/

/**
 * Loader.
 *
 * @since 0.1
 */
function bp_rbe_new_topic_loader() {
	// RBE isn't loaded or RBE's requirements are not fulfilled.
	if ( ! function_exists( 'bp_rbe_is_required_completed' ) || ! bp_rbe_is_required_completed() ) {
		return;
	}

	// Check for bbPress and if the Groups component is active or not.
	if ( ! function_exists( 'bbpress' ) || ! bp_is_active( 'groups' ) ) {
		return;
	}

	// Bail if PHP 5.2 and lower.
	if ( version_compare( phpversion(), '5.3', '<' ) ) {
		add_action( 'admin_notices', create_function( '', "echo '<div class=\"error\"><p>" . __( 'BuddyPress Reply By Email - Custom Group New Topic Email Address requires PHP 5.3 or higher. Please upgrade PHP or deactivate this plugin.', 'bp-rbe-new-topic' ) . "</p></div>';" ) );
		return;
	}

	// If using IMAP mode and not using bleeding version of RBE, add notice.
	if ( ! bp_rbe_is_inbound() && ! is_callable( array( $GLOBALS['bp_rbe'], 'load_inbound_provider' ) ) ) {
		add_action( 'admin_notices', create_function( '', "echo '<div class=\"error\"><p>" . __( 'BP Reply By Email - Simple New Topic Email Address requires switching to Inbound Mode or upgrading to the latest version of RBE if you want to use IMAP mode for replies and Inbound Mode for new topics.', 'bp-rbe-new-topic' ) . "</p></div>';" ) );
		return;
	}

	// Bail if in wp-admin or if this is an AJAX request.
	if ( defined( 'WP_NETWORK_ADMIN' ) && defined( 'DOING_AJAX' ) ) {
		return;
	}

	/*
	 * If using IMAP mode, manually load Inbound mode.
	 *
	 * This is so our custom, simple group new topic email addresses will work.
	 */ 
	if ( ! bp_rbe_is_inbound() ) {
		$GLOBALS['bp_rbe']->load_inbound_provider();
		add_action( 'wp_loaded', 'bp_rbe_inbound_catch_callback', 0 );
	}

	// Autoloader.
	spl_autoload_register( function( $class ) {
		$prefix = 'BP_RBE_New_Topic\\';

		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		// Get the relative class name.
		$relative_class = substr( $class, strlen( $prefix ) );

		$base_dir = dirname( __FILE__ ) . '/classes/';

		$file = $base_dir . str_replace( '\\', '/', $relative_class . '.php' );

		if ( file_exists( $file ) ) {
			require $file;
		}
	} );

	// Group integration.
	add_action( 'bp_init', function() {
		if ( bp_is_group() && bp_is_current_action( 'forum' ) && ! bp_action_variable() && ! bp_is_post_request() && is_user_logged_in() ) {
			BP_RBE_New_Topic\Group\Frontend::init();
		}

		if ( bp_is_group() && bp_is_current_action( 'admin' ) && bp_is_action_variable( 'notifications', 0 ) ) {
			BP_RBE_New_Topic\Group\Manage::init();
		}
	} );

	// Parser integration.
	add_action( 'bp_rbe_before_parser', 'BP_RBE_New_Topic\Parser::init' );	
}
add_action( 'bp_include', 'bp_rbe_new_topic_loader', 20 );