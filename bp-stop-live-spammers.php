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
			// kills access to the site
			// the spammer will not be able to view any portion of the site whatsoever
			wp_die( __( '<strong>ERROR</strong>: Your account has been marked as a spammer.', 'buddypress' ) );
			exit;
		}
	}

}
add_action( 'bp_init', array( 'BP_Stop_Live_Spammers', 'init' ), 5 );
