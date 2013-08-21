
$(document).ready( function() {

	// toggle subscription
	$('.comment-tracker-toggle-tmp').live('click', function(event) {

		var guid = $(this).children(":first").attr('data-guid');
		var subscribe = 1;
		
        // change text on any other controllers for this item
        $('.comment-tracker-toggle').each( function() {
            if ($(this).children(':first').attr('data-guid') == guid) {
                // this is a controller, update the text
                var text = $(this).children(':first').text();
                
                if (text == elgg.echo('comment_tracker:unsubscribe')) {
                    $(this).children(':first').text(elgg.echo('comment_tracker:subscribe'));
                    subscribe = 0;
                }
                
                if (text == elgg.echo('comment_tracker:unsubscribe:long')) {
                    $(this).children(':first').text(elgg.echo('comment_tracker:subscribe:long'));
                    subscribe = 0;
                }
                
                if (text == elgg.echo('comment_tracker:subscribe')) {
                   $(this).children(':first').text(elgg.echo('comment_tracker:unsubscribe'));
                    subscribe = 1;
                }
                
                if (text == elgg.echo('comment_tracker:subscribe:long')) {
                   $(this).children(':first').text(elgg.echo('comment_tracker:unsubscribe:long'));
                    subscribe = 1;
                }
            }
        });
		
		// now update the database
		elgg.action('comment_tracker/subscribe', {
			data: {
				guid: guid,
				subscribe: subscribe,
				user: elgg.get_logged_in_user_guid()
			}
		});

		//event.preventDefault();
	});

	/**
	 * Repositions the likes popup
	 *
	 * @param {String} hook    'getOptions'
	 * @param {String} type    'ui.popup'
	 * @param {Object} params  An array of info about the target and source.
	 * @param {Object} options Options to pass to
	 *
	 * @return {Object}
	 */
	elgg.ui.commentTrackerPopupHandler = function(hook, type, params, options) {
		if (params.target.hasClass('comment-tracker-popup')) {
			options.my = 'left top';
			options.at = 'left bottom';
			return options;
		}
		return null;
	};
	
	elgg.register_hook_handler('getOptions', 'ui.popup', elgg.ui.commentTrackerPopupHandler);
});