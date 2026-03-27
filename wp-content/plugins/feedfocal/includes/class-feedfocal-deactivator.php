<?php

/**
 * Fired during plugin deactivation
 *
 * @link       feedfocal.com
 * @since      1.0.0
 *
 * @package    FeedFocal
 * @subpackage FeedFocal/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    FeedFocal
 * @subpackage FeedFocal/includes
 * @author     FeedFocal <wordpress@feedfocal.com>
 */
class FeedFocal_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		// Delete any existing survey code
		delete_option( 'feedfocal_survey_code' );
		delete_option( 'feedfocal_activation_redirect' );
	}

}
