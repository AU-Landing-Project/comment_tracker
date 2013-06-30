
$(document).ready( function() {

	// toggle subscription
	$('.comment-tracker-toggle').live('click', function(event) {
		event.preventDefault();
		
		var guid = $(this).children(":first").attr('data-guid');
		
		//switch the display text
		var text = $(this).children(":first").text();
		var replace = elgg.echo('comment:unsubscribe');
		var subscribe = 1;
		
		if (text == elgg.echo('comment:unsubscribe')) {
			replace = elgg.echo('comment:subscribe');
			subscribe = 0;
		}
		
        
        // also change text on any other controllers for this item
        $('.comment-tracker-toggle').each( function() {
            if ($(this).children(':first').attr('data-guid') == guid) {
                // this is a controller, update the text
                $(this).children(':first').text(replace);
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
		
	});
});