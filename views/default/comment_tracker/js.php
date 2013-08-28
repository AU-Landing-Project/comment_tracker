/* <script> */

elgg.provide('elgg.commentTracker');

/**
 * Toggle subscribtion for an entity via AJAX
 * 
 * @param {Object} event
 *
 * @return void
 */
elgg.commentTracker.toggle = function(event) {

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

	elgg.action('comment_tracker/subscribe', {
		data: {
			guid: guid,
			subscribe: subscribe,
			user: elgg.get_logged_in_user_guid()
		}
	});

	event.preventDefault();
};

/**
 * Reposition the likes popup
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

/**
 * Toggle settings popup visibility
 * 
 * @todo This is propably not needed anymore
 * 
 * @param {Object} event
 *
 * @return void
 */
elgg.commentTracker.toggleSettings = function(event) {
	var guid = $(this).children(":first").attr('data-guid');
	var settings = $('.comment-tracker-settings-' + guid);
	var link = $(this);

	if (event.type == 'mouseenter') {
		if (settings.hasClass('hidden')) {
			settings.removeClass('hidden');
		}
	} else {
		// Give user time to click before hiding the item
		settings.delay(1000).queue(function(){
			var settingsHover = $('.comment-tracker-settings-' + guid + ':hover').length;
			//var popupHover = $('#comment-tracker-popup-' + guid + ':hover').length;
			//var popupHidden = $('#comment-tracker-popup-' + guid).hasClass('hidden');
			var popupHidden = $('#comment-tracker-popup-' + guid).css('display') == 'none';

			//console.log('#comment-tracker-popup-' + guid);
			//console.log(settingsHover)
			//console.log(popupHover)
			//console.log(popupHidden)

			if (settingsHover == 0 && popupHidden) {
				settings.addClass('hidden').dequeue();
	    	} else {
	        	// todo
			}
		});
	}
};

/**
 * Save entity subscribtion settings via AJAX
 * 
 * @param {Object} event
 *
 * @return void
 */
elgg.commentTracker.manage = function(event) {
	var form = $(this);

	var guid = form.find('input[name=guid]').val();
	var url = form.attr('action');
	var data = form.serialize();

	elgg.action(url, {
		data: data,
		success: function(json) {
			$('#comment-tracker-popup-' + guid).css('display', 'none');

			// TODO If all methods were disabled change the subscription
			// menu item text to "Subscribe" (and the other way around)
		}
	});

	event.preventDefault();
}

/**
 * 
 */
elgg.commentTracker.init = function() {
	// TODO Is this a good idea since it doesn't work with touch devices?
	//$('.comment-tracker-toggle').live('hover', elgg.commentTracker.toggleSettings);
	//$('.comment-tracker-toggle').live('blur', elgg.commentTracker.toggleSettings);

	$('.comment-tracker-toggle').live('click', elgg.commentTracker.toggle);

	$('.elgg-form-comment-tracker-manage').live('submit', elgg.commentTracker.manage);
};

elgg.register_hook_handler('getOptions', 'ui.popup', elgg.ui.commentTrackerPopupHandler);
elgg.register_hook_handler('init', 'system', elgg.commentTracker.init);