<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       feedfocal.com
 * @since      1.0.0
 *
 * @package    FeedFocal
 * @subpackage FeedFocal/includes
 */

/**
 * Setup API endpoints.
 *
 * @since    1.0.0
 */
function feedfocal_api_setup($request) {

	// Get data
	$data = $request->get_body();
	$data = json_decode($data, true); // Convert json array to PHP array

	// Extract individual array data
	$code = (string) (isset($data['code']) ? htmlentities( $data['code'] ) : null);

	// Add tracking code to database
	update_option( 'feedfocal_survey_code', $code );

	return true;

}

/**
 * Setup template pages.
 *
 * @since    1.0.0
 */
function feedfocal_template_projects() {

	// Assign default variable values
	$textarea = (string) null;

	// Get current survey code, if it exists
	if( get_option( 'feedfocal_survey_code' ) ) {
		$textarea = get_option( 'feedfocal_survey_code' );
	}

?>
	<div class="feedfocal-body uk-scope">

		<div class="container projects-header">
			<div class="container-core block-flex flex-gap-35">

				<div class="block-header">
					<h4 class="block-title"><?php _e( 'JustFeedback', 'feedfocal' ); ?></h4>
				</div>

				<div class="block-text">
					<p><?php _e( 'Easily collect feedback from your users.', 'feedfocal' ); ?></p>
				</div>

			</div>
		</div>
		<!-- .projects-header -->

		<div class="container projects-tips">
			<div class="container-core block-flex block-flex-start w800-block-flex-column-reverse flex-gap-30">

				<div class="container-column container-col1 flex-col-rel-1">

<?php // 					<div class="block-card block-card-link block-style2 xC6426E"> ?>
 					<div class="block-card block-card-link block-style2 x426BC6">

						<a href="https://justfeedback.com/" target="_blank">

							<div class="block-header">
								<h3 class="block-title"><span class="dashicons dashicons-megaphone"></span>Why use JustFeedback?</h3>
							</div>

							<div class="block-body">
								<div class="block-text">
									<p>Adding a JustFeedback survey to your site is <strong>100% FREE</strong><br />Plus... Feedback helps improve user experience, so you can get more sales and positive reviews!</p>
<?php /*									<p>How to get more sales with<br />better feedback</p> */ ?>
									<p>Join For Free -&gt;</p>
								</div>
							</div>

						</a>

					</div>

				</div>

				<div class="container-column container-col2 flex-col-rel-3 hidden">

					<div class="block-card block-card-link block-style2 xC0392B">

						<a href="" target="_blank">

							<div class="block-header">
								<h3 class="block-title"><span class="dashicons dashicons-megaphone"></span>Tip 2</h3>
							</div>

							<div class="block-body">
								<div class="block-text">
									<p>Keep your visitors happy and<br />improve user experience</p>
									<p>Read now -&gt;</p>
								</div>
							</div>

						</a>

					</div>

				</div>

				<div class="container-column container-col3 flex-col-rel-3 hidden">

					<div class="block-card block-card-link block-style2 x642b73">

						<a href="" target="_blank">

							<div class="block-header">
								<h3 class="block-title"><span class="dashicons dashicons-megaphone"></span>Tip 3</h3>
							</div>

							<div class="block-body">
								<div class="block-text">
									<p>Boost feedback response rates<br />with FeedFocal</p>
									<p>Read now -&gt;</p>
								</div>
							</div>

						</a>

					</div>

				</div>

			</div>
		</div>
		<!-- .projects-tips -->

		<div class="container projects-connect">
			<div class="container-core block-flex block-flex-start w800-block-flex-column-reverse flex-gap-30">

				<div class="container-column container-col1 flex-col-rel-2">

					<div class="block-card block-style1">

						<div class="block-header">
							<h3 class="block-title"><?php _e( 'Connect JustFeedback Account', 'feedfocal' ); ?></h3>
						</div>

						<div class="block-body">

							<div class="block-text">
								<p><?php _e( 'Add your JustFeedback survey code to connect your account', 'feedfocal' ); ?></p>
							</div>

							<div class="block-code">
								<textarea id="feedfocal-code" name="feedfocal-code" rows="12" cols="50" placeholder="Add your JustFeedback survey code here."><?php esc_html_e( $textarea ); ?></textarea>
								<button id="feedfocal-save" class="uk-button uk-button-default"><?php _e( 'Save', 'feedfocal' ); ?></button>
							</div>

						</div>

					</div>

				</div>

				<div class="container-column container-col2 flex-col-rel-2">

					<div class="block-card block-style1">

						<div class="block-header">
							<h3 class="block-title"><?php _e( 'Register JustFeedback Account', 'feedfocal' ); ?></h3>
						</div>

						<div class="block-body">

						<div class="block-text">
							<p><?php _e( 'To get setup, first register your account at <a href="https://justfeedback.com" target="_blank">justfeedback.com</a>.', 'feedfocal' ); ?></p>
						</div>

						<div class="block-media">
							<img src="<?php echo plugin_dir_url( FEEDFOCAL_PLUGIN_FILE ) . 'assets/register-1.png'; ?>" /></p>
						</div>

						<div class="block-text hidden">
							<p><?php _e( 'Need less than 10 results / mo? Create a free acount <a href="https://app.justfeedback.com/register?pid=free101m0" target="_blank">here</a>.', 'feedfocal' ); ?></p>
<?php /*							<p><?php _e( 'Running a small site? You can create a free acount <a href="https://app.feedfocal.com/register?pp=free&pt=10" target="_blank">here</a>.', 'feedfocal' ); ?></p> */ ?>
<?php /*							<p><?php _e( 'Once registered, create a survey and add survey code to this site.', 'feedfocal' ); ?></p> */ ?>
						</div>

						</div>

					</div>

				</div>

			</div>
		</div>
		<!-- .projects-connect -->

	</div>

<?php
}
