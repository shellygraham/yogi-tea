<?php 

class MRP_Utils {
	
	/** 
	 * Gets the Font Awesome icon classes based on version
	 * 
	 * @param $font_awesome_version
	 * @return array icon classes
	 */
	public static function get_icon_classes( $font_awesome_version ) {
	
		$icon_classes = array();
		
		if ( $font_awesome_version == '4.0.3' || $font_awesome_version == '4.1.0' ) {
			$icon_classes['star_full'] = 'fa fa-star mrp-star-full';
			$icon_classes['star_half'] = 'fa fa-star-half-o mrp-star-half';
			$icon_classes['star_empty'] = 'fa fa-star-o mrp-star-empty';
			$icon_classes['minus'] = 'fa fa-minus-circle mrp-minus';
			$icon_classes['thumbs_up_on'] = 'fa fa-thumbs-up mrp-thumbs-up-on';
			$icon_classes['thumbs_up_off'] = 'fa fa-thumbs-o-up mrp-thumbs-up-off';
			$icon_classes['thumbs_down_on'] = 'fa fa-thumbs-down mrp-thumbs-down-on';
			$icon_classes['thumbs_down_off'] = 'fa fa-thumbs-o-up mrp-thumbs-down-off';
		} else if ( $font_awesome_version == '3.2.1' ) {
			$icon_classes['star_full'] = 'icon-star mrp-star-full';
			$icon_classes['star_half'] = 'icon-star-half-full mrp-star-half';
			$icon_classes['star_empty'] = 'icon-star-empty mrp-star-empty';
			$icon_classes['minus'] = 'icon-minus-sign mrp-minus';
			$icon_classes['thumbs_up_on'] = 'icon-thumbs-up mrp-thumbs-up-on';
			$icon_classes['thumbs_up_off'] = 'icon-thumbs-up-alt mrp-thumbs-up-off';
			$icon_classes['thumbs_down_on'] = 'icon-thumbs-down mrp-thumbs-down-on';
			$icon_classes['thumbs_down_off'] = 'icon-thumbs-down-alt mrp-thumbs-down-off';
		}
		
		return $icon_classes;
	}

	/**
	 * Gets the client ip address
	 *
	 * @since 2.1
	 */
	public static function get_ip_address() {
		$client_IP_address = '';
		
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$client_IP_address = $_SERVER['HTTP_CLIENT_IP'];
		} else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$client_IP_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			$client_IP_address = $_SERVER['HTTP_X_FORWARDED'];
		} else if ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			$client_IP_address = $_SERVER['HTTP_FORWARDED_FOR'];
		} else if ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
			$client_IP_address = $_SERVER['HTTP_FORWARDED'];
		} else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$client_IP_address = $_SERVER['REMOTE_ADDR'];
		}
		
		return $client_IP_address;
	}
	
	/**
	 * Validates the option value textarea
	 *
	 * @param unknown_type $option_value_text
	 * @return string
	 */
	public static function validate_option_value_text( $option_value_text, $max_option_value ) {
		$option_value_text_array = preg_split( '/[\r\n,]+/', $option_value_text, -1, PREG_SPLIT_NO_EMPTY );
	
		$error_message = '';
	
		$check_curent_value_check = array();
		foreach ( $option_value_text_array as $current_option_value_text ) {
			// validate format
			$parts = explode("=", $current_option_value_text);
			
			if ( isset( $parts ) && ( count( $parts ) > 2 || ( isset( $parts[0] ) && ! is_numeric( $parts[0] ) ) ) ) {
				$error_message .= sprintf( __('Invalid option value text %s.', 'multi-rating-pro' ) , $current_option_value_text );
				break;
			}
	
			if ( isset( $parts[0] ) ) {
				$check_curent_value = intval( $parts[0] );
				
				if ( isset( $check_curent_value_check[$check_curent_value] ) ) {
					$error_message .= sprintf( __( 'Duplicate option value %s.', 'multi-rating-pro' ), $check_curent_value );
					break;
				}
				
				$check_curent_value_check[$check_curent_value] = TRUE;
	
				if ( $check_curent_value > $max_option_value ) {
					$error_message .= sprintf( __('Option value greater than max option value %s.', 'multi-rating-pro' ), $check_curent_value );
					break;
				}
			}
		}
		
		// TODO return an object, not a string
		return $error_message;
	}
	
	/**
	 * Validates rating item text
	 * 
	 * @param $rating_items
	 */
	public static function validate_rating_items_text( $rating_items ) {
		
		$rating_items = preg_split( '/[\r\n\s,]+/', $rating_items, -1, PREG_SPLIT_NO_EMPTY );
		
		$count_rating_items = count( $rating_items );
		if ( $count_rating_items == 0 ) {
			return;
		}
		
		global $wpdb;
		$query = 'SELECT COUNT(rating_item_id) FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME . ' WHERE rating_item_id in (';
		
		$index = 0;
		foreach ( $rating_items as $rating_item ) {
			$index++;
			
			$query .= $rating_item;
				
			if ( $index < $count_rating_items ) {
				$query .= ', ';
			}
		}
		$query .= ')';
		
		$count = $wpdb->get_var( $query );
		
		if ( $count != $count_rating_items ) {
			return __( 'An invalid rating item was used.', 'multi-rating-pro' );
		}
		
		// TODO return an object, not a string
		return;
	}
	
	/**
	 * Validates a rating form id
	 * 
	 * @param $rating_form_id
	 */
	public static function validate_rating_form( $rating_form_id ) {
		global $wpdb;
		$query = 'SELECT rating_form_id FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME . ' WHERE rating_form_id = "' . $rating_form_id . '"';
		
		$row = $wpdb->get_row( $query, ARRAY_A, 0 );
		if ( $row == null ) {
			return __( 'Invalid Rating Form Id was used.', 'multi-rating-pro' );
		}
		
		// TODO return an object, not a string
		return;
	}
	
	/**
	 * Checks the filters
	 * 
	 * @param $check_values
	 * @param $filter_values
	 * @param $filter_type
	 */
	public static function check_filter( $check_values, $filter_array, $filter_type ) {
		
		if ( ! is_array( $check_values ) ) {
			$check_values = array( $check_values );
		}
		
		foreach ( $check_values as $check_current_value ) {
			if ( $filter_type == MRP_Multi_Rating::WHITELIST_VALUE ) {
				if ( ! in_array( $check_current_value, $filter_array ) ) {
					return false;
				}
				
			} else if ( $filter_type == MRP_Multi_Rating::BLACKLIST_VALUE ) {
				if ( in_array( $check_current_value, $filter_array ) ) {
					return false;
				}
			}
		}
	
		return true;
	}
	
	/**
	 * Gets the current URL
	 *
	 * @return current URL
	 */
	public static function get_current_url() {
		$url = 'http';
		
		if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') {
			$url .= "s";
		}
		
		$url .= '://';
		
		if ( $_SERVER['SERVER_PORT'] != '80') {
			$url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
		} else {
			$url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		}
	
		return MRP_Utils::normalize_url( $url );
	}
	
	/**
	 * Normalizes the URL (some of the best parts of RFC 3986)
	 *
	 * @param unknown_type $url
	 * @return string
	 */
	public static function normalize_url( $url ) {
	
		// TODO return error for bad URLs
	
		// Process from RFC 3986 http://en.wikipedia.org/wiki/URL_normalization
	
		// Limiting protocols.
		if ( ! parse_url( $url, PHP_URL_SCHEME ) ) {
			$url = 'http://' . $url;
		}
	
		$parsed_url = parse_url( $url );
		if ( $parsed_url === false ) {
			return '';
		}
	
		// user and pass components are ignored
	
		// TODO Removing or adding “www” as the first domain label.
		$host = preg_replace( '/^www\./', '', $parsed_url['host'] );
	
		// Converting the scheme and host to lower case
		$scheme = strtolower( $parsed_url['scheme'] );
		$host = strtolower( $host );
	
		$path = $parsed_url['path'];
		// TODO Capitalizing letters in escape sequences
		// TODO Decoding percent-encoded octets of unreserved characters
	
		// Removing the default port
		$port = '';
		if ( isset( $parsed_url['port'] ) ) {
			$port = $parsed_url['port'];
		}
		if ( $port == 80 ) {
			$port = '';
		}
	
		// Removing the fragment # (do not get fragment component)
	
		// Removing directory index (i.e. index.html, index.php)
		$path = str_replace( 'index.html', '', $path );
		$path = str_replace( 'index.php', '', $path );
	
		// Adding trailing /
		$path_last_char = $path[strlen( $path ) -1];
		if ( $path_last_char != '/' ) {
			$path = $path . '/';
		}
	
		// TODO Removing dot-segments.
	
		// TODO Replacing IP with domain name.
	
		// TODO Removing duplicate slashes
		$path = preg_replace( "~\\\\+([\"\'\\x00\\\\])~", "$1", $path );
	
		// construct URL
		$url =  $scheme . '://' . $host . $path;
	
		// Add query params if they exist
		// Sorting the query parameters.
		// Removing unused query variables
		// Removing default query parameters.
		// Removing the "?" when the query is empty.
		$query = '';
		if ( isset( $parsed_url['query'] ) ) {
			$query = $parsed_url['query'];
		}
		if ( $query ) {
			$query_parts = explode( '&', $query );
			$params = array();
			foreach ( $query_parts as $param ) {
				$items = explode( '=', $param, 2 );
				$name = $items[0];
				$value = '';
				if ( count( $items ) == 2 ) {
					$value = $items[1];
				}
				$params[$name] = $value;
			}
			ksort( $params );
			$count_params = count( $params );
			if ( $count_params > 0 ) {
				$url .= '?';
				$index = 0;
				foreach ( $params as $name => $value ) {
					$url .= $name;
					if ( strlen( $value ) != 0 ) {
						$url .= '=' . $value;
					}
					if ( $index++ < ( $count_params - 1 ) ) {
						$url .= '&';
					}
				}
			}
		}
	
		// Remove some query params which we do not want
		$url = MRP_Utils::remove_query_string_params( $url, array() );
	
		return $url;
	}
	
	/**
	 * Removes query string parameters from URL
	 * @param $url
	 * @param $param
	 * @return string
	 *
	 * @since 1.2
	 */
	public static function remove_query_string_params( $url, $params ) {
		foreach ( $params as $param ) {
			$url = preg_replace( '/(.*)(\?|&)' . $param . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&' );
			$url = substr( $url, 0, -1 );
		}
		return $url;
	}
}
?>