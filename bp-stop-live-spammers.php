<?php
/*
Plugin Name: BP Stop Live Spammers!
Description: When you mark a live user as a spammer, that user can still surf around and cause havoc on the site until the person is logged out. This plugin stops live spammers in their tracks!
Author: r-a-y
Author URI: http://buddypress.org/community/members/r-a-y/
Version: 0.1
License: GPLv2 or later
*/

/**
 * BP Stop Live Spammers
 *
 * @package BP_Stop_Live_Spammers
 * @subpackage Loader
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * BP Stop Live Spammers Core
 *
 * @see https://buddypress.trac.wordpress.org/ticket/4814
 *
 * @package BP_Stop_Live_Spammers
 * @subpackage Core
 */
class BP_Stop_Live_Spammers {
	/**
	 * Holds the single-running class object.
	 *
	 * @var BP_Stop_Live_Spammers
	 */
	private static $instance = false;

	/**
	 * Creates a singleton instance of the class.
	 *
	 * @return BP_Stop_Live_Spammers object
	 * @static
	 */
	public static function init() {
		if ( self::$instance === false ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->spam_status();

		add_action( 'login_form_bp-spam', array( __CLASS__, 'wp_login_error' ) );
	}

	/**
	 * Check if the logged-in user is marked as a spammer.
	 *
	 * Runs on 'bp_init' at priority 5 so the members component globals are setup
	 * before we do our spammer checks.
	 *
	 * This is important as the $bp->loggedin_user object is setup at priority 4.
	 */
	protected function spam_status() {
		// if we're on the login page, stop now to prevent redirect loop
		if ( strpos( $GLOBALS['pagenow'], 'wp-login.php' ) !== false ) {
			return;
		}

		global $bp;

		// user isn't logged in, so stop!
		if ( empty( $bp->loggedin_user ) ) {
			return;
		}

		// get logged-in userdata
		$user = $bp->loggedin_user->userdata;

		// setup spammer boolean
		$spammer = false;

		// multisite spammer
		if ( ! empty( $user->spam ) ) {
			$spammer = true;

		// single site spammer
		} elseif ( $user->user_status == 1 ) {
			$spammer = true;
		}

		// if spammer, stop this user now!
		if ( $spammer ) {
			// setup login args
			$args = array(
				// custom action used to throw an error message
				'action' => 'bp-spam',

				// reauthorize user to login
				'reauth' => 1
			);

			// setup login URL
			$login_url = apply_filters( 'bp_live_spammer_redirect', add_query_arg( $args, wp_login_url() ) );

			// redirect user to login page
			wp_redirect( $login_url );

			// or perhaps just kill the site?
			// cons with this approach is this doesn't clear the auth cookies
			//wp_die( __( '<strong>ERROR</strong>: Your account has been marked as a spammer.', 'buddypress' ) );
			exit;
		}
	}

	/**
	 * Setup custom error message when a user is marked as a spammer live.
	 */
	public function wp_login_error() {
		global $error;

		$error = __( '<strong>ERROR</strong>: Your account has been marked as a spammer.', 'buddypress' );

		// shake shake shake!
		add_action( 'login_head', 'wp_shake_js', 12 );
	}

}
add_action( 'bp_init', array( 'BP_Stop_Live_Spammers', 'init' ), 5 );
