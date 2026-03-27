(function( $ ) {
	'use strict';

	// Wait for document to fully load
	$( document ).ready(function() {

		// Save FeedFocal survey code
		$("#feedfocal-save").click(function(){

			// Assign selector variables
			var button = $( '#feedfocal-save' );
			var code   = $( '#feedfocal-code' );

			// Data object to send with API request
			var data = {
				'code' : code.val(),
			};

			// API endpoint
			var api_endpoint = WPURLS.api_root + 'feedfocal/v1/setup';

			// Ajax request to add new site
			var response = jQuery.ajax( {

				// Send data to API endpoint
				url        : api_endpoint,
				data       : JSON.stringify( data ),
				dataType   : 'json',
				method     : 'POST',
				beforeSend : function( xhr ) {
					$(this).attr('disabled');
					xhr.setRequestHeader( 'X-WP-Nonce', WPURLS.api_nonce );
				},
			} );

			// Disable login button
			button.attr( 'disabled', 'disabled' )

			// Proceed when response is received
			response.done( function( response ) {

				// Re-enable login button
				button.removeAttr( 'disabled' )

				// Display notice on successful save
				UIkit.notification({
					message : 'Survey code updated!',
					status  : 'success',
					pos     : 'top-center',
					timeout : 5000
				});
			});
		});
	});

})( jQuery );
