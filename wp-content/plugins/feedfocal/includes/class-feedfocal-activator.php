<?php

/**
 * Fired during plugin activation
 *
 * @link       feedfocal.com
 * @since      1.0.0
 *
 * @package    FeedFocal
 * @subpackage FeedFocal/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    FeedFocal
 * @subpackage FeedFocal/includes
 * @author     FeedFocal <wordpress@feedfocal.com>
 */
class FeedFocal_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// Add option confirming plugin was ctivated (used for redirect)
		add_option( 'feedfocal_activation_redirect', true );
	}

}
