<?php 

/**
 * View class for rating form
 * 
 * @author dpowney
 *
 */
class MRP_Rating_Form_View {
	
	public static $sequence = 0;
	
	/**
	 * Gets a rating item field used in the comment_form()
	 * 
	 * @param unknown_type $rating_item
	 */
	public static function get_rating_item_field( $rating_item, $rating_form_id, $post_id, $sequence ) {
		
		// index-X-ratingFormId-postId-sequence-ratingItemId
		$rating_item_id = $rating_item['rating_item_id'];
		$element_id = 'rating-item-' . $rating_item_id . '-' . $sequence;
		return MRP_Rating_Form_View::get_rating_item_html( $rating_item, $element_id, null );
	}
	
	/**
	 * Get the rating form HTML
	 * @param $rating_items
	 * @param $post_id
	 * @param $rating_form_id
	 * @param $params
	 */
	public static function get_rating_form( $rating_items, $post_id, $rating_form_id = null, $params = array() ) {
		
		extract( wp_parse_args( $params, array(
				'title' => '',
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'submit_button_text' => '' ,
				'update_button_text' => '',
				'delete_button_text' => '',
				'show_name_input' => false,
				'show_email_input' => false,
				'show_comment_textarea' => false,
				'already_submitted_rating_form_message' => '',
				'class' => ''
		)));
		
		MRP_Rating_Form_View::$sequence++;
		
		// get username
		global $wp_roles;
		$current_user = wp_get_current_user();
		$username = $current_user->user_login;
		
		$selected_option_lookup = null;
		$rating_item_entry_id = null ;
		
		$name = $current_user->display_name; // default to current logged in user display name
		$email = $current_user->user_email; // default to current logged in user e-mail
		$comment = '';
		
		// if user has already submitted the rating form, set default values and allow them to delete or update
		if (strlen($username) > 0 
				&& MRP_Multi_Rating_API::has_user_already_submitted_rating_form( $rating_form_id, $post_id, $username ) ) {
			
			$selected_option_lookup = array();
			$rating_item_entries = MRP_Multi_Rating_API::get_rating_item_entries( array(
					'post_id' => $post_id,
					'rating_form_id' => $rating_form_id,
					'username' => $username,
					'limit' => 1
			) );
			
			// get the first one
			if ( count( $rating_item_entries ) > 0 ) {
				$rating_item_entry = $rating_item_entries[0];
				
				// this is also used to determine whether to display the update/delete buttons as well
				$rating_item_entry_id = $rating_item_entry['rating_item_entry_id'];
				
				$rating_item_entry_values = MRP_Multi_Rating_API::get_rating_item_entry_values( array(
						'rating_item_entry_id' => $rating_item_entry_id
				) );
				
				$selected_option_lookup = array();
				foreach ( $rating_item_entry_values as $rating_item_entry_value ) {
					$selected_option_lookup[$rating_item_entry_value['rating_item_id']] = $rating_item_entry_value['value'];	
				}
				$name =  stripslashes( $rating_item_entry['name'] );
				$email = stripslashes( $rating_item_entry['email'] );
				$comment = stripslashes( $rating_item_entry['comment'] );
			} 
			
		}

		$html = '<div class="rating-form ' . $class . '">';
		
		if ( ! empty( $title ) ) {
			$html .=  $before_title . $title . $after_title;
		}
		
		if ( $rating_item_entry_id != null ) {
			$html .= '<p>' . $already_submitted_rating_form_message . '</p>';
		}
		
		$html .= '<form name="rating-form-' . $rating_form_id . '-' . $post_id . '-' . MRP_Rating_Form_View::$sequence . '" action="#">';
		
		// add the rating items
		foreach ( $rating_items as $rating_item ) {
			$rating_item_id = $rating_item['rating_item_id'];
			$element_id = 'rating-item-' . $rating_item_id . '-' . MRP_Rating_Form_View::$sequence ;
			
			$html .= MRP_Rating_Form_View::get_rating_item_html( $rating_item, $element_id, $selected_option_lookup );
			
			// hidden field to identify the rating item
			// this is used in the JavaScript to construct the AJAX call when submitting the rating form
			$html .= '<input type="hidden" value="' . $rating_item_id . '" class="rating-form-' . $rating_form_id . '-' . $post_id . '-' . MRP_Rating_Form_View::$sequence . '-item" id="hidden-rating-item-id-' . $rating_item_id .'" />';
		}
		
			
		// add the custom fields
		if ( $show_name_input == true ) {
			$html .= '<p><label for="name-' . MRP_Rating_Form_View::$sequence . '" class="input-label">' . __( 'Name', 'multi-rating-pro' ) . '</label><br />';
			$html .= '<input type="text" name="name-' . MRP_Rating_Form_View::$sequence . '" size="30" placeholder="' .  __( 'Enter your name', 'multi-rating-pro' ) . '" id="name-' . MRP_Rating_Form_View::$sequence . '" class="name" value="' . $name . '" maxlength="100"></input></p>';
		}
		if ($show_email_input == true) {
			$html .= '<p><label for="email-' . MRP_Rating_Form_View::$sequence . '" class="input-label">' . __( 'E-mail', 'multi-rating-pro' ) . '</label><br />';
			$html .= '<input type="text" name="email-' . MRP_Rating_Form_View::$sequence . '" size="30" placeholder="' .  __( 'Enter your e-mail address', 'multi-rating-pro' ) . '"id="email-' . MRP_Rating_Form_View::$sequence . '"class="email" value="' . $email . '" maxlength="255"></input></p>';
		}
		if ($show_comment_textarea == true) {
			$html .= '<p><label for="comment-' . MRP_Rating_Form_View::$sequence . '" class="textarea-label">' . __( 'Comments', 'multi-rating-pro' ) . '</label><br />';
			$html .= '<textarea rows="5" name="comment-' . MRP_Rating_Form_View::$sequence . '" placeholder="' . __( 'Enter comments', 'multi-rating-pro' ) . '" id="comment-' . MRP_Rating_Form_View::$sequence . '" class="comments" value="" maxlength="1020">' . $comment . '</textarea></p>';
		}
		
		$button_text = $submit_button_text;
		if ( $rating_item_entry_id != null ) {
			$button_text = $update_button_text;
			$html .= '<input type="button" class="btn btn-default delete-rating"  id="' . $rating_form_id . '-' . $post_id . '-' . $rating_item_entry_id . '-' . MRP_Rating_Form_View::$sequence . '" value="' . $delete_button_text . '"></input>';
		}
		$html .= '<input type="button" class="btn btn-default save-rating" id="' . $rating_form_id . '-' . $post_id . '-' . MRP_Rating_Form_View::$sequence . '" value="' . $button_text . '"></input>';
		$html .= '<input type="hidden" value="' . $rating_item_entry_id . '" id="rating-item-entry-id-' . $rating_form_id . '-' . $post_id . '-' . MRP_Rating_Form_View::$sequence . '" />';
		$html .= '<input type="hidden" name="sequence" value="' . MRP_Rating_Form_View::$sequence . '" />';
		
		$html .= '</form>';
		
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Returns HTML for the rating items in the rating form
	 * 
	 * @param unknown_type $rating_item
	 * @param unknown_type $element_id
	 * @param unknown_type $selected_option_lookup
	 */
	public static function get_rating_item_html( $rating_item, $element_id, $selected_option_lookup ) {
		
		$style_settings = (array) get_option( MRP_Multi_Rating::STYLE_SETTINGS );
		$star_rating_colour = $style_settings[MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION];
		$font_awesome_version = $style_settings[MRP_Multi_Rating::FONT_AWESOME_VERSION_OPTION];
		$icon_classes = MRP_Utils::get_icon_classes( $font_awesome_version );
			
		$rating_item_id = $rating_item['rating_item_id'];
		$description = stripslashes($rating_item['description']);
		$default_option_value = $rating_item['default_option_value'];
		$max_option_value = $rating_item['max_option_value'];
		$option_value_text = $rating_item['option_value_text'];
		$rating_item_type = $rating_item['type'];
		$include_zero = $rating_item['include_zero'];
		
		$html = '<p class="rating-item"><label class="description" for="' . $element_id . '">' . $description . '</label>';
			
		if ($rating_item_type == "star_rating") {
			
			$html .= '<span class="star-rating star-rating-select">';
			
			// add star icons
			$index = 0;
			for ( $index; $index<=$max_option_value; $index++ ) {
					if ( $index == 0 ) {
					$html .= '<i id="index-' . $index . '-' . $element_id . '" class="' . $icon_classes['minus'] . ' index-' . $index . '-' . $element_id;
					
					// add a class so that the frontend knows whether zero is included
					if ( $include_zero == false ) {
							$html .= ' exclude-zero';
					}
					
					$html .=  '"></i>';
					continue;
				}
				
				$class = $icon_classes['star_full'];
				
				// if default is less than current icon, it must be empty
				if ( $default_option_value < $index ) {
					$class = $icon_classes['star_empty'];
				}
				$html .= '<i id="index-' . $index . '-' . $element_id . '" class="' . $class . ' index-' . $index . '-' . $element_id . '"></i>';
			}
			$html .= '</span>';
			
			// hidden field for storing selected star rating value
			$html .= '<input type="hidden" name="' . $element_id . '" id="' . $element_id . '" value="' . $default_option_value . '">';
		
		} else if ( $rating_item_type == 'thumbs' ) {
			
			$html .= '<span class="thumbs thumbs-select">';
			
			if ( $default_option_value != 0 || $default_option_value != 1 ) {
				$default_option_value = 1;
			}
			
			$thumbs_down_class = $icon_classes['thumbs_down_on'];
			if ( $default_option_value != 0 ) {
				$thumbs_down_class = $icon_classes['thumbs_down_off'];
			}
			$html .= '<i id="index-0-' . $element_id . '" class="' . $thumbs_down_class . ' index-0-' . $element_id . '"></i>';
			
			$thumbs_up_class = $icon_classes['thumbs_up_on'];
			if ( $default_option_value != 1 && $default_option_value == 0 ) {
				$thumbs_up_class = $icon_classes['thumbs_up_off'];
			}
			$html .= '<i id="index-1-' . $element_id . '" class="' . $thumbs_up_class . ' index-1-' . $element_id . '"></i>';
			
			// hidden field for storing selected star rating value
			$html .= '<input type="hidden" name="' . $element_id . '" id="' . $element_id . '" value="' . $default_option_value . '"  style="color: ' . $star_rating_colour . ' !important;">';
			
			$html .= '</span>';
		} else { // select or radio
			
			// lookup the option text descriptions for select and radio rating item types
			$option_value_text_lookup = array();
			$option_value_text_array = preg_split( '/[\r\n,]+/' , $option_value_text, -1, PREG_SPLIT_NO_EMPTY );
			foreach  ($option_value_text_array as $current_option_value_text ) {
				$parts = explode( '=', $current_option_value_text );
				if ( count( $parts ) == 2 && is_numeric( $parts[0] ) ) {
					$option_value_text_lookup[intval( $parts[0] )] = stripslashes( $parts[1] );
				}
			}
	
			if ( $rating_item_type == 'select' ) {
				$html .= '<select name="' . $element_id . '" id="' . $element_id . '">';
			}
	
			// option values
			$index = 0;
			if ( $include_zero == false ) {
				$index = 1;
			}
			for ( $index; $index<=$max_option_value; $index++ ) {
					
				$is_selected = false;
				if ( $selected_option_lookup != null ) {
					// if user has already submitted a rating, set their previous selected option
					if ( isset( $selected_option_lookup[$rating_item_id] ) && $selected_option_lookup[$rating_item_id] == $index ) {
						$is_selected = true;
					}
					
				} else if ( $default_option_value == $index ) {
					$is_selected = true;
				}
					
				$text = $index;
				if ( isset( $option_value_text_lookup[$index] ) ) {
					$text = $option_value_text_lookup[$index];
				}
					
				if ( $rating_item_type == 'select' ) {
					$html .= '<option value="' . $index . '"';
					
					if ( $is_selected ) {
						$html .= ' selected="selected"';
					}
					
					$html .= '>' . $text . '</option>';
				} else {
					$html .= '<span class="radio-option">';
					$html .= '<input type="radio" name="' . $element_id . '" id="' . $element_id . '-' . $index . '" value="' . $index . '"';
					
					if ( $is_selected ) {
						$html .= ' checked="checked"';
					}
					
					$html .= '>' . $text . '</input></span>';
				}
			}
			
			if ( $rating_item_type == 'select' ) {
				$html .= '</select>';
			}
		}
			
		$html .= '</p>';

		return $html;
	}
}
?>