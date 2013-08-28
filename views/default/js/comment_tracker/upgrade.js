/**
 * Run comment tracker upgrade scripts
 */

elgg.provide('elgg.commentTrackerUpgrade');

/**
 * @todo
 */
elgg.commentTrackerUpgrade.init = function() {
	$('.elgg-progressbar').progressbar();
};

elgg.register_hook_handler('init', 'system', elgg.commentTrackerUpgrade.init);