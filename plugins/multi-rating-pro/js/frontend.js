jQuery(document).ready(function() {	
	
	/**
	 * Saves or updates a rating
	 */
	jQuery(".rating-form .save-rating:button").click(function(e) {
	
		var ratingItems = [];
		var btnId = e.currentTarget.id;
		var parts = btnId.split("-"); 
		var ratingFormId = parts[0];
		var postId = parts[1];
		var sequence = parts[2];
		
		// each rating item has a hidden id field using the ratig form id, post id and sequence
		jQuery( '.rating-form input[type="hidden"].rating-form-' + ratingFormId + '-' + postId + '-' + sequence + '-item').each(function( index ) {			
			var ratingItemId = jQuery(this).val();
			
			// get values for 3 types of rating items: select, radio and star rating
			var element = jQuery('[name=rating-item-' +ratingItemId + '-' + sequence + ']');
			var selectedValue = null;
			if (jQuery(element).is(':radio')) {
				selectedValue = jQuery('input[type="radio"][name=rating-item-' +ratingItemId + '-' + sequence + ']:checked').val(); 
			} else if (jQuery(element).is('select')) {
				selectedValue = jQuery('select[name=rating-item-' +ratingItemId + '-' + sequence + '] :selected').val(); 
			} else {
				selectedValue = jQuery('input[type=hidden][name=rating-item-' +ratingItemId + '-' + sequence + ']').val();
			}
			
			var ratingItem = { 
						'id' : ratingItemId, 
						'value' : selectedValue
					};
			ratingItems[index] = ratingItem;
		});
		
		var comment = jQuery('#comment-' + sequence);
		var name = jQuery('#name-' + sequence);
		var email = jQuery('#email-' + sequence);
		
		// check if updating
		var ratingItemEntryId = jQuery("#rating-item-entry-id-" + btnId);
		
		var data = {
				action : "save_rating",
				nonce : mrp_frontend_data.ajax_nonce,
				ratingItems : ratingItems,
				postId : postId,
				ratingFormId : ratingFormId,
				name : (name != undefined) ? name.val() : '',
				email : (email != undefined) ? email.val() : '',
				comment : (comment != undefined) ? comment.val() : '',
				ratingItemEntryId : (ratingItemEntryId != undefined) ? ratingItemEntryId.val() : '' // for updates
			};
	
			jQuery.post(mrp_frontend_data.ajax_url, data, function(response) {
				alert(response);
			});
	});
	
	/**
	 * Deletes an existing rating
	 */
	jQuery(".rating-form .delete-rating:button").click(function(e) { 
		var anchorId = e.currentTarget.id;
		var parts = anchorId.split("-"); 
		var ratingFormId = parts[0];
		var postId = parts[1];
		var ratingItemEntryId = parts[2];
		var sequence = parts[3]; // not used
		
		var data = {
				action : "delete_rating",
				nonce : mrp_frontend_data.ajax_nonce,
				postId : postId,
				ratingFormId : ratingFormId,
				ratingItemEntryId : ratingItemEntryId
			};
	
			jQuery.post(mrp_frontend_data.ajax_url, data, function(response) {
				alert(response);
			});
	});
	
	
	/**
	 * Selected rating item value on hover and click
	 */
	var ratingItemStatus = {};
	
	// supporting different versions of Font Awesome icons
	var icon_classes = jQuery.parseJSON(mrp_frontend_data.icon_classes);
	
	jQuery(".star-rating-select .mrp-star-empty, .star-rating-select .mrp-star-full").click(function(e) {
		
		updateRatingItemStatus(this, 'clicked');
		
		jQuery(this).not('.mrp-minus').removeClass(icon_classes.star_empty + " mrp-star-hover").addClass(icon_classes.star_full);
		jQuery(this).prevAll().not('.mrp-minus').removeClass(icon_classes.star_empty + " mrp-star-hover").addClass(icon_classes.star_full);
		jQuery(this).nextAll().not('.mrp-minus').removeClass(icon_classes.star_full + " mrp-star-hover").addClass(icon_classes.star_empty);
		
		updateSelectedHiddenValue(this);
	});
	
	jQuery(".star-rating-select .mrp-minus").click(function(e) {
		
		updateRatingItemStatus(this, '');
		
		jQuery(this).not('.mrp-minus').removeClass(icon_classes.star_empty + " mrp-star-hover").addClass(icon_classes.star_full);
		jQuery(this).prevAll().not('.mrp-minus').removeClass(icon_classes.star_empty + " mrp-star-hover").addClass(icon_classes.star_full);
		jQuery(this).nextAll().not('.mrp-minus').removeClass(icon_classes.star_full + " mrp-star-hover").addClass(icon_classes.star_empty);
		
		updateSelectedHiddenValue(this);
	});
	
	jQuery(".star-rating-select .mrp-minus, .star-rating-select .mrp-star-empty, .star-rating-select .mrp-star-full").hover(function(e) {

		var elementId = getRatingItemElementId(this);
		var ratingItemIdSequence = getRatingItemIdSequence(elementId);
		
		if (ratingItemStatus[ratingItemIdSequence] != 'clicked' && ratingItemStatus[ratingItemIdSequence] != undefined) {
			
			updateRatingItemStatus(this, 'hovered');
			
			jQuery(this).not('.mrp-minus').removeClass(icon_classes.star_empty).addClass(icon_classes.star_full + " mrp-star-hover");
			jQuery(this).prevAll().not('.mrp-minus').removeClass(icon_classes.star_empty).addClass(icon_classes.star_full + " mrp-star-hover");
			jQuery(this).nextAll().not('.mrp-minus').removeClass(icon_classes.star_full + " mrp-star-hover").addClass(icon_classes.star_empty);	
		}
	});
	
	jQuery(".thumbs-select .mrp-thumbs-down-on, .thumbs-select .mrp-thumbs-down-off").click(function(e) {
		jQuery(this).removeClass(icon_classes.down_up_off).addClass(icon_classes.thumbs_down_on);
		jQuery(this).next().removeClass(icon_classes.thumbs_up_on).addClass(icon_classes.thumbs_up_off);
		
		updateSelectedHiddenValue(this);
	});
	
	jQuery(".thumbs-select .mrp-thumbs-up-on, .thumbs-select .mrp-thumbs-up-off").click(function(e) {
		jQuery(this).removeClass(icon_classes.thumbs_up_off).addClass(icon_classes.thumbs_up_on);
		jQuery(this).prev().removeClass(icon_classes.thumbs_down_on).addClass(icon_classes.thumbs_down_off);
		
		updateSelectedHiddenValue(this);
	});
	
	// now cater for touch screen devices
	var touchData = {
		started : null, // detect if a touch event is sarted
		currrentX : 0,
		yCoord : 0,
		previousXCoord : 0,
		previousYCoord : 0,
		touch : null
	};
	
	jQuery(".star-rating-select .mrp-star-empty, .star-rating-select .mrp-star-full, .star-rating-select .mrp-minus, " +
			".thumbs-select .mrp-thumbs-up-on, .thumbs-select .mrp-thumbs-up-off, .thumbs-select .mrp-thumbs-down-on, " +
			".thumbs-select .mrp-thumbs-down-on").on("touchstart", function(e) {
		touchData.started = new Date().getTime();
		var touch = e.originalEvent.touches[0];
		touchData.previousXCoord = touch.pageX;
		touchData.previousYCoord = touch.pageY;
		touchData.touch = touch;
	});
	
	jQuery(".star-rating-select .mrp-star-empty, .star-rating-select .mrp-star-full, .star-rating-select .mrp-minus").on("touchend touchcancel", function(e) {
			var now = new Date().getTime();
			// Detecting if after 200ms if in the same position.
			if ((touchData.started !== null)
					&& ((now - touchData.started) < 200)
					&& (touchData.touch !== null)) {
				var touch = touchData.touch;
				var xCoord = touch.pageX;
				var yCoord = touch.pageY;
				if ((touchData.previousXCoord === xCoord)
						&& (touchData.previousYCoord === yCoord)) {
					
					jQuery(this).removeClass(icon_classes.star_empty).addClass(icon_classes.star_full);
					jQuery(this).prevAll().removeClass(icon_classes.star_empty).addClass(icon_classes.star_full);
					jQuery(this).nextAll().removeClass(icon_classes.star_full).addClass(icon_classes.star_empty);
					
					updateSelectedHiddenValue(this);
				}
			}
			touchData.started = null;
			touchData.touch = null;
	});
	jQuery(".thumbs-select .mrp-thumbs-down-off, .thumbs-select .mrp-thumbs-down-on").on( "touchend touchcancel", function(e) {
			var now = new Date().getTime();
			// Detecting if after 200ms if in the same position.
			if ((touchData.started !== null)
					&& ((now - touchData.started) < 200)
					&& (touchData.touch !== null)) {
				var touch = touchData.touch;
				var xCoord = touch.pageX;
				var yCoord = touch.pageY;
				if ((touchData.previousXCoord === xCoord)
						&& (touchData.previousYCoord === yCoord)) {
					
					jQuery(this).removeClass(icon_classes.thumbs_down_off).addClass(icon_classes.thumbs_down_on);
					jQuery(this).next().removeClass(icon_classes.thumbs_up_on).addClass(icon_classes.thumbs_up_off);
					
					updateSelectedHiddenValue(this);
				}
			}
			touchData.started = null;
			touchData.touch = null;
	});
	
	jQuery(".thumbs-select .mrp-thumbs-up-off, .thumbs-select .mrp-thumbs-up-on").on( "touchend touchcancel", function(e) {
			var now = new Date().getTime();
			// Detecting if after 200ms if in the same position.
			if ((touchData.started !== null)
					&& ((now - touchData.started) < 200)
					&& (touchData.touch !== null)) {
				var touch = touchData.touch;
				var xCoord = touch.pageX;
				var yCoord = touch.pageY;
				if ((touchData.previousXCoord === xCoord)
						&& (touchData.previousYCoord === yCoord)) {
					
					jQuery(this).removeClass(icon_classes.thumbs_up_off).addClass(icon_classes.thumbs_up_on);
					jQuery(this).next().removeClass(icon_classes.thumbs_down_on).addClass(icon_classes.thumbs_down_off);
					
					updateSelectedHiddenValue(this);
				}
			}
			touchData.started = null;
			touchData.touch = null;
	});	
	
	/**
	 * Updates the rating item status to either hovered or clicked
	 */
	function updateRatingItemStatus(element, status) {
		var elementId = getRatingItemElementId(element);
		var ratingItemIdSequence = getRatingItemIdSequence(elementId);
		if (ratingItemIdSequence != null) {
			ratingItemStatus[ratingItemIdSequence] = status;
		}
	}
	
	function getRatingItemIdSequence(elementId) {
		var parts = elementId.split("-"); 
		
		var ratingItemId = parts[4]; /// skip 2: rating-item-
		var sequence = parts[5];
		
		var ratingItemIdSequence = 'rating-item-' + ratingItemId + '-' + sequence;
		return ratingItemIdSequence;
	}
	
	function getRatingItemElementId(element) {
		var clazz = jQuery(element).attr("class");
		
		if (clazz && clazz.length && clazz.split) {
			clazz = clazz.trim();
			clazz = clazz.replace(/\s+/g, ' ');
			var classes = clazz.split(' ');
			var index=0;
			for (index; index<classes.length; index++) {
				var currentClass = classes[index];
		        if (currentClass !== '' && currentClass.indexOf('index-') == 0) {
		        	
		        	// index-X-ratingItemId-sequence
		        	var parts = currentClass.split("-"); 
		    		var value = parts[1]; // this is the star index
		    		var ratingItemId = parts[4]; /// skipt 2: rating-item-
		    		var sequence = parts[5];
		    		
		    		var elementId = 'index-' + value + '-rating-item-' + ratingItemId + '-' + sequence;
		    		//index-1-rating-item-1-1
		    		return elementId;
		        }
			}
		}
		
		return null;
	}
	
	/**
	 * Updates the selected hidden value for a rating item
	 */
	function updateSelectedHiddenValue(element) {
		var clazz = jQuery(element).attr("class");
		
		if (clazz && clazz.length && clazz.split) {
			clazz = clazz.trim();
			clazz = clazz.replace(/\s+/g, ' ');
			var classes = clazz.split(' ');
			var index=0;
			for (index; index<classes.length; index++) {
				var currentClass = classes[index];
		        if (currentClass !== '' && currentClass.indexOf('index-') == 0) {
		        	
		        	// FIXME this should use a unique element Id - not a class
		        	
		        	// index-X-ratingItemId-sequence
		        	var parts = currentClass.split("-"); 
		    		var value = parts[1]; // this is the star index
		    		var ratingItemId = parts[4]; /// skipt 2: rating-item-
		    		var sequence = parts[5];
		    		
		    		var elementId = '#rating-item-'+ ratingItemId + '-' + sequence;
		    		
		    		if (jQuery("." + currentClass).hasClass("exclude-zero") && value == 0) {
			    		var newSelectedRatingItemID = "#index-1-rating-item-" + ratingItemId + "-" + sequence;
			    		jQuery(newSelectedRatingItemID).removeClass(icon_classes.star_empty);
				    	jQuery(newSelectedRatingItemID).addClass(icon_classes_star_full);
			    		value = 1;
		    		}
		    		
		    		jQuery(elementId).val(value);
		    		return;
		        }
			}
		}
	}
});