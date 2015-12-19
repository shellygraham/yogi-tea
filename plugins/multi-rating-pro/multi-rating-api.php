<?php
/**
 * API functions for multi rating
 * 
 * @author dpowney
 *
 */
class MRP_Multi_Rating_API {
	
	
	/**
	 * Returns whether a user has already submitted a rating form for a post
	 * 
	 * @param int $rating_form_id
	 * @param int $post_id
	 * @param string $username
	 * @return boolean true if a user has already submitted a rating form for a post
	 */
	public static function has_user_already_submitted_rating_form( $rating_form_id, $post_id, $username ) {
		
		global $wpdb;
		
		$query = 'SELECT COUNT(*) FROM '.$wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME 
		. ' WHERE post_id = ' . $post_id . ' AND rating_form_id = "' . $rating_form_id . '" AND username = "' . $username . '"';

		$count = $wpdb->get_col( $query, 0 );
		
		return ( is_array( $count ) && $count[0] > 0 );
	}
	
	
	/**
	 * Get rating items
	 * 
	 * @param array $params	rating_item_entry_id, post_id and rating_form_id
	 * @return rating items
	 */
	public static function get_rating_items( $params = array() ) {
		
		global $wpdb;
		
		// base query
		$rating_items_query = 'SELECT ri.rating_item_id, ri.rating_id, ri.description, ri.default_option_value, '
				. 'ri.max_option_value, ri.weight, ri.active, ri.type, ri.option_value_text, ri.include_zero FROM '
				. $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME . ' as ri';
		
		if ( isset( $params['rating_item_entry_id'] ) || isset($params['post_id'] ) ) {
			
			$rating_items_query .= ', ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' AS rie, '
					. $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME
					. ' AS riev';
		}
		
		$added_to_query = false;
		if ( isset( $params['rating_item_entry_id'] ) || isset( $params['post_id'] ) ) {
			
			$rating_items_query .= ' WHERE';
			$rating_items_query .= ' riev.rating_item_entry_id = rie.rating_item_entry_id';
			$added_to_query = true;
		}
		
		
		// rating_form_id - check the rating items are associated with the rating form
		if ( isset( $params['rating_form_id'] ) ) {
			
			$rating_form_id = $params['rating_form_id'];

			$rating_form_query = 'SELECT rating_form_id, rating_items FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME . ' WHERE rating_form_id = "' . $rating_form_id . '"';
			$rating_form = $wpdb->get_row( $rating_form_query, ARRAY_A, 0 );
			
			$rating_items = preg_split( '/[,\s]+/', $rating_form['rating_items'], -1, PREG_SPLIT_NO_EMPTY );
			
			$count_rating_items = count( $rating_items );
			if ( $count_rating_items > 0) {
				
				if ( ! isset( $params['rating_item_entry_id'] ) && ! isset( $params['post_id'] ) ) {
					$rating_items_query .= ' WHERE';
				}
				
				if ( $added_to_query == true ) {
					$rating_items_query .= ' AND';
					$added_to_query = false;
				}
				
				$rating_items_query .= ' ri.rating_item_id in (';
			
				$index = 0;
				foreach ( $rating_items as $rating_item ) {
					$rating_items_query .= $rating_item;
					$index++;
					if ( $index < $count_rating_items ) {
						$rating_items_query .= ', ';
					}
				}
				$rating_items_query .= ')';
					
				$added_to_query = true;
			}
		}
		
		// rating_item_entry_id
		if ( isset( $params['rating_item_entry_id'] ) ) {
			
			if ( $added_to_query == true ) {
				$rating_items_query .= ' AND';
				$added_to_query = false;
			}
			
			$rating_items_query .= ' rie.rating_item_entry_id =  "' . $params['rating_item_entry_id'] . '" AND ri.rating_item_id = riev.rating_item_id';
			$added_to_query = true;
		}
		
		// post_id
		if ( isset( $params['post_id'] ) ) {
			
			if ( $added_to_query == true ) {
				$rating_items_query .= ' AND';
				$added_to_query = false;
			}
			
			$rating_items_query .= ' rie.post_id = "' . $params['post_id'] . '"';
			$added_to_query = true;
			
			//$post_type = get_post_type( $params['post_id'] );
		}
		
		$rating_item_rows = $wpdb->get_results($rating_items_query);
		
		// construct rating items array
		$rating_items = array();
		foreach ( $rating_item_rows as $rating_item_row ) {
			$rating_item_id = $rating_item_row->rating_item_id;
			$weight = $rating_item_row->weight;
			$description = $rating_item_row->description;
			$default_option_value = $rating_item_row->default_option_value;
			$max_option_value = $rating_item_row->max_option_value;
			$option_value_text = $rating_item_row->option_value_text;
			$type = $rating_item_row->type;
			$include_zero = $rating_item_row->include_zero ? true : false;
			
			$rating_items[$rating_item_id] = array(
					'max_option_value' => $max_option_value,
					'weight' => $weight,
					'rating_item_id' => $rating_item_id,
					'description' => $description,
					'default_option_value' => $default_option_value,
					'option_value_text' => $option_value_text,
					'type' => $type,
					'include_zero' => $include_zero
			);
		}
		
		return $rating_items;
	}
	
	
	/**
	 * Calculates the total weight of rating items
	 * 
	 * @param array $rating_items
	 * @return total weight
	 */
	public static function get_total_weight( $rating_items ) {
		
		$total_weight = 0;
	
		foreach ( $rating_items as $rating_item => $rating_item_array ) {
			//if ($rating_item_array['exclude_result'] == false) {
				$total_weight += $rating_item_array['weight'];
			//}
		}

		return $total_weight;
	}
	
	
	/**
	 * Retrieves the rating item entry values
	 * 
	 * @param $params => rating_item_entry_id, rating form_id, post_id
	 */
	public static function get_rating_item_entry_values( $params = array() ) {
		
		extract( wp_parse_args( $params, array(
				'rating_item_entry_id' => null,
				'rating_form_id' => null,
				'post_id' => null
		)));
		
		global $wpdb;
		
		$query = null;
		if ( $rating_item_entry_id == null && $post_id != null && $rating_form_id != null ) {
			
			$query = 'SELECT ri.description AS description, ri.type as type, riev.value AS value, ri.max_option_value AS max_option_value, '
				. 'riev.rating_item_entry_id AS rating_item_entry_id, ri.rating_item_id AS rating_item_id, ri.option_value_text as option_value_text '
				. 'FROM '.$wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' AS riev, '
				. $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME . ' AS ri, ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' as rie'
				. ' WHERE ri.rating_item_id = riev.rating_item_id'
				. ' AND rie.rating_item_entry_id = riev.rating_item_entry_id'
				. ' AND rie.post_id = "' . $post_id . '" AND rie.rating_form_id = "' . $rating_form_id . '"';
			
		} else if ( $rating_item_entry_id != null ) {
			
			$query = 'SELECT ri.description AS description, ri.type as type, riev.value AS value, ri.max_option_value AS max_option_value, '
					. 'riev.rating_item_entry_id AS rating_item_entry_id, ri.rating_item_id AS rating_item_id, ri.option_value_text as option_value_text '
					. 'FROM '.$wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' AS riev, '
					. $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME . ' AS ri WHERE ri.rating_item_id = riev.rating_item_id' 
					. ' AND riev.rating_item_entry_id = "' . $rating_item_entry_id . '"';
			
		} 
		
		if ( $query == null) {
			return;
		}
			
		$rating_item_entry_value_rows = $wpdb->get_results( $query, ARRAY_A );
			
		foreach ( $rating_item_entry_value_rows as &$rating_item_entry_value_row ) {
			$option_value_text_array = preg_split( '/[\r\n,]+/',  $rating_item_entry_value_row['option_value_text'], -1, PREG_SPLIT_NO_EMPTY );
		
			$value = intval( $rating_item_entry_value_row['value'] );
			$rating_item_entry_value_row['value_text'] = $value;
		
			// try to find the option value text if it has been set
			foreach ( $option_value_text_array as $current_option_value_text ) {
				
				$parts = explode( '=', $current_option_value_text );
					
				if ( isset( $parts[0] ) && isset( $parts[1] ) ) {
					
					$curent_value = intval($parts[0]);
					$current_text = $parts[1];
		
					if ( $value == $curent_value ) {
						$rating_item_entry_value_row['value_text'] = $current_text;
						
						break;
					}
		
				}
			}
		}
		
		return $rating_item_entry_value_rows;
	}
	
	/**
	 * Calculates the rating result of a rating form for a post with filters for username
	 * 
	 * @param array $params post_id, rating_items, rating_form_id and username
	 * @return rating result
	 */
	public static function calculate_rating_result( $params = array() ) {
		
		if ( ! isset($params['rating_items'] ) || ! isset( $params['rating_form_id'] ) || !isset( $params['post_id'] ) ) {
			return;
		}
		
		$rating_items = $params['rating_items'];
		$post_id = $params['post_id'];
		$rating_form_id = $params['rating_form_id'];
		
		$username = null;
		if ( isset($params['username'] ) ) {
			$username = $params['username'];
		}
		
		$rating_item_entries = MRP_Multi_Rating_API::get_rating_item_entries( array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'username' => $username
		) );
			
		$total_weight = MRP_Multi_Rating_API::get_total_weight( $rating_items );
		
		$score_result_total = 0;
		$adjusted_score_result_total = 0;
		$star_result_total = 0;
		$adjusted_star_result_total = 0;
		$percentage_result_total = 0;
		$adjusted_percentage_result_total = 0;
		$total_max_option_value = 0;
		
		$count_entries = count($rating_item_entries);
		// process all entries for the post and construct a rating result for each post
		foreach ( $rating_item_entries as $rating_item_entry ) {
			$total_value = 0;
	
			// retrieve the entry values for each rating item
			$rating_item_entry_id = $rating_item_entry['rating_item_entry_id'];
			
			$rating_result = MRP_Multi_Rating_API::calculate_rating_item_entry_result( $rating_item_entry_id, $rating_items );
			
			$score_result_total += $rating_result['score_result'];
			$adjusted_score_result_total += $rating_result['adjusted_score_result'];
			
			$star_result_total += $rating_result['star_result'];
			$adjusted_star_result_total += $rating_result['adjusted_star_result'];
			
			$percentage_result_total += $rating_result['percentage_result'];
			$adjusted_percentage_result_total += $rating_result['adjusted_percentage_result'];
			
			if ( $total_max_option_value == 0 ) { // no need to set again
				$total_max_option_value = $rating_result['total_max_option_value'];
			}
		}
		
		$score_result = 0;
		$adjusted_score_result = 0;
		$star_result = 0;
		$adjusted_star_result = 0;
		$percentage_result = 0;
		$adjusted_percentage_result = 0;
		$overall_rating_result = 0;
		$overall_adjusted_rating_result = 0;
		
		if ($count_entries > 0) {
			// calculate 5 star result
			$score_result = round( doubleval($score_result_total ) / $count_entries, 2 );
			$adjusted_score_result =round(doubleval($adjusted_score_result_total ) / $count_entries, 2 );
			
			// calculate star result
			$star_result = round( doubleval( $star_result_total ) / $count_entries, 2 );
			$adjusted_star_result = round( doubleval( $adjusted_star_result_total ) / $count_entries, 2 );
			
			// calculate percentage result
			$percentage_result = round( doubleval( $percentage_result_total ) / $count_entries, 2 );
			$adjusted_percentage_result = round( doubleval( $adjusted_percentage_result_total ) / $count_entries, 2 );
		}
		
		return array(
				'adjusted_star_result' => $adjusted_star_result,
				'star_result' => $star_result,
				'total_max_option_value' => $total_max_option_value,
				'adjusted_score_result' => $adjusted_score_result,
				'score_result' => $score_result,
				'percentage_result' => $percentage_result,
				'adjusted_percentage_result' => $adjusted_percentage_result,
				'count' => $count_entries,
				'post_id' => $post_id
		);
	}
	
	/**
	 * Gets rating item entries of a rating form for a post with filters for username
	 * 
	 * @param array $params post_id, rating_form_id, username and category_id, rating_item_entry_id, limit
	 * @return rating item entries
	 */
	public static function get_rating_item_entries( $params = array() ) {

		extract( wp_parse_args( $params, array(
				'post_id' => null,
				'rating_form_id' => null,
				'username' => null,
				'category_id' => null,
				'comments_only' => null,
				'rating_item_entry_ids' => null,
				'limit' => null,
				'comment_id' => null,
				'from_date' => null,
				'to_date' => null
		) ) );	
		
		global $wpdb;

		$query = 'SELECT rie.username, rie.rating_item_entry_id, rie.name, rie.email, rie.comment, rie.rating_form_id, ' . 
		'rie.post_id, rie.entry_date, rie.comment_id FROM ' . $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' as rie';
		
		if ( $category_id != null ) {
			$query .= ', ' . $wpdb->prefix . 'posts as p';
			$query .= ' LEFT JOIN ' . $wpdb->prefix . 'term_relationships rel ON rel.object_id = p.ID';
			$query .= ' LEFT JOIN ' . $wpdb->prefix . 'term_taxonomy tax ON tax.term_taxonomy_id = rel.term_taxonomy_id';
			$query .= ' LEFT JOIN ' . $wpdb->prefix . 'terms t ON t.term_id = tax.term_id';
		}
		
		$added_to_query = false;
		if ( $rating_form_id || $post_id || $username || $category_id || $comment_id || $comments_only 
				|| $rating_item_entry_ids || $from_date || $to_date ) {
			$query .= ' WHERE';
		}
		
		if ( $rating_form_id ) {
			if ($added_to_query) {
				$query .= ' AND';
			}
			
			$query .= ' rie.rating_form_id = "' . $rating_form_id . '"';
			$added_to_query = true;
		}
		
		if ( $post_id ) {
			if ($added_to_query) {
				$query .= ' AND';
			}
			
			$query .= ' rie.post_id = "' . $post_id . '"';
			$added_to_query = true;
		}
		
		if ( $username ) {
			if ($added_to_query) {
				$query .= ' AND';
			}
			
			$query .= ' rie.username = "' . $username . '"';
			$added_to_query = true;
		}
		
		if ( $category_id ) {
			if ($added_to_query) {
				$query .= ' AND';
			}
			
			$query .= ' p.ID = rie.post_id AND t.term_id IN (' . $category_id . ')';
			$added_to_query = true;
		}
		
		if ($comments_only == true) {
			if ($added_to_query) {
				$query .= ' AND';
			}
			$query .= ' (rie.comment != "" OR rie.comment_id != "")';
			$added_to_query = true;
		}
		
		if ( $comment_id ) {
			if ($added_to_query) {
				$query .= ' AND';
			}
			
			$query .= ' rie.comment_id = "' . $comment_id . '"';
			$added_to_query = true;
		}
		
		if ( $from_date ) {
			if ($added_to_query) {
				$query .= ' AND';
			}
			
			$query .= ' rie.entry_date >= "' . $from_date . '"';
			$added_to_query = true;
		}
		
		if ( $to_date ) {
			if ($added_to_query) {
				$query .= ' AND';
			}
			
			$query .= ' rie.entry_date <= "' . $to_date . '"';
			$added_to_query = true;
		}
		
		if ( $rating_item_entry_ids ) {
			
			// comma separated list to array
			$temp_array = preg_split('/[,]+/', $rating_item_entry_ids, -1, PREG_SPLIT_NO_EMPTY) ;
			
			if ( $added_to_query ) {
				$query .= ' AND';
				$added_to_query = false;
			}
			
			if ( is_array( $temp_array ) && count( $temp_array ) ) {
				$query .= ' (';
				
			}
			foreach ( $temp_array as $rating_item_entry_id ) {
				if ( $added_to_query ) {
					$query .= ' OR ';
				}
				
				$query .= 'rie.rating_item_entry_id = "' . $rating_item_entry_id . '"';
				$added_to_query = true;
			}
			
			if ( is_array( $temp_array ) && count( $temp_array ) ) {
				$query .= ')';
			}
		}
		
		if ( $limit && is_numeric( $limit ) ) {
			if ( intval( $limit ) > 0 ) {
				$query .= ' LIMIT 0, ' . intval( $limit );
			}
		}
		
		$rating_item_entry_rows = $wpdb->get_results( $query );
		
		// construct rating item entries array
		$rating_item_entries = array();
		foreach ( $rating_item_entry_rows as $rating_item_entry_row ) {
			
			$rating_item_entry = array(
					'rating_item_entry_id' => $rating_item_entry_row->rating_item_entry_id,
					'username' => $rating_item_entry_row->username,
					'name' => $rating_item_entry_row->name,
					'email' => $rating_item_entry_row->email,
					'comment' => $rating_item_entry_row->comment,
					'rating_form_id' => $rating_item_entry_row->rating_form_id,
					'post_id' => $rating_item_entry_row->post_id,
					'entry_date' => $rating_item_entry_row->entry_date,
					'comment_id' => $rating_item_entry_row->comment_id
				);
			
			array_push( $rating_item_entries, $rating_item_entry );
		}
		
		return $rating_item_entries;
	}
	
	
	/**
	 * Calculates the result for a single rating item
	 * 
	 * @param array $rating_item
	 * @param int $rating_form_id
	 * @param int $post_id
	 * @return rating item result
	 */
	public static function calculate_rating_item_result( $rating_item, $rating_form_id, $post_id, $username = null ) {
		
		$max_option_value = $rating_item['max_option_value'];
		$total_value = 0;

		global $wpdb;
		
		$rating_item_entry_value_query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME 
			. ' as riev, ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' as rie WHERE riev.rating_item_id = "' . $rating_item['rating_item_id'] 
			. '" AND rie.rating_form_id = "' . $rating_form_id . '" AND riev.rating_item_entry_id = rie.rating_item_entry_id AND rie.post_id = "' . $post_id . '"';
		if ( $username != null ) {
			$rating_item_entry_query .= ' AND rie.username = "' . $username . '"';
		}

		$rating_form_query = 'SELECT rating_form_id, rating_items FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME . ' WHERE rating_form_id = "' . $rating_form_id . '"';
		$rating_form = $wpdb->get_row( $rating_form_query, ARRAY_A, 0 );
			
		$rating_items = preg_split( '/[,\s]+/', $rating_form['rating_items'], -1, PREG_SPLIT_NO_EMPTY );
		$rating_item_entry_value_query .= ' AND riev.rating_item_id in (';
		
		$count_rating_items = count( $rating_items );
		$index = 0;
		
		foreach ( $rating_items as $rating_item ) {
			$rating_item_entry_value_query .= $rating_item;
			$index++;
			
			if ( $index < $count_rating_items ) {
				$rating_item_entry_value_query .=', ';
			}
		}
		$rating_item_entry_value_query .= ')';
		
		$rating_item_entry_value_rows = $wpdb->get_results( $rating_item_entry_value_query );
		
		$star_result = 0;
		$score_result = 0;
		$percentage_result = 0;
		
		// initialise value totals array - used for showing how many times a value was chosen
		$option_totals = array();
		for ( $index=0; $index <= $max_option_value; $index++ ) {
			$option_totals[$index] = 0;
		}
		
		foreach ( $rating_item_entry_value_rows as $rating_item_entry_value_row ) {
			$value = $rating_item_entry_value_row->value;
			
			if ( $value <= $max_option_value ) {
				$option_totals[$value]++;
			} else {
				$option_totals[$max_option_value]++;
				$value = $max_option_value;
			}
			
			$score_result += intval( $value );
		}

		$count_entries = count( $rating_item_entry_value_rows );
		if ($count_entries > 0) {
			$score_result = round( doubleval( $score_result ) / $count_entries, 2 );
			
			// calculate 5 star result
			$star_result = round( doubleval( $score_result ) / doubleval($max_option_value ), 2 ) * 5;
		
			// calculate percentage result
			$percentage_result = round( doubleval( $score_result ) / doubleval( $max_option_value ), 2 ) * 100;
		}
		
		return array(
				'star_result' => $star_result,
				'adjusted_star_result' => $star_result,
				'score_result' => $score_result,
				'adjusted_score_result' => $score_result,
				'percentage_result' => $percentage_result,
				'adjusted_percentage_result' => $percentage_result,
				'max_option_value' => $max_option_value,
				'total_max_option_value' => $max_option_value,
				'count' => $count_entries,
				'option_totals' => $option_totals
		);
	}

	
	/**
	 * Calculates the rating item entry result.
	 *
	 * @param int $rating_item_entry_id
	 * @param array $rating_items optionally used to save an additional call to the database if the 
	 * rating items have already been loaded
	 */
	public static function calculate_rating_item_entry_result( $rating_item_entry_id, $rating_items = null ) {
		
		if ( $rating_items == null ) {
			$rating_items = MRP_Multi_Rating_API::get_rating_items(array(
					'rating_item_entry_id' => $rating_item_entry_id
			) );
		}
		
		global $wpdb;
		
		$query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME 
				. ' WHERE rating_item_entry_id = ' . $rating_item_entry_id;
		$rating_item_entry_value_rows = $wpdb->get_results( $query );

		$total_max_option_value = 0;
		$total_rating_item_result = 0;
		$total_adjusted_rating_item_result = 0;
		$star_result = 0;
		$adjusted_star_result = 0;
		$score_result = 0;
		$adjusted_score_result = 0;
		$percentage_result = 0;
		$adjusted_percentage_result = 0;
		$rating_result = 0;
		$total_weight = MRP_Multi_Rating_API::get_total_weight( $rating_items );
		
		// use the rating items to determine total max option value
		// we do not use the entry values in case some rating items can be added/deleted
		$count_rating_items = 0;
		foreach ( $rating_items as $rating_item ) {
			//if ($rating_item['exclude_result'] == false) {
				$total_max_option_value += $rating_item['max_option_value'];
				$count_rating_items++;
			//}
		}
		
		foreach ( $rating_item_entry_value_rows as $rating_item_entry_value_row ) {
			
			$rating_item_id = $rating_item_entry_value_row->rating_item_id;
		
			// check rating item is available, if it's been deleted it wont be included in rating result
			if ( isset( $rating_items[$rating_item_id] ) && isset( $rating_items[$rating_item_id]['max_option_value'] ) ) {
		
				//if ($rating_items[$rating_item_id]['exclude_result'] == true) {
				//	continue;
				//}
				
				// add value and max option values
				$value = $rating_item_entry_value_row->value;
				$max_option_value = $rating_items[$rating_item_id]['max_option_value'];
				
				if ( $value > $max_option_value ) {
					$value = $max_option_value;
				}
				
				// make adjustments to the rating for weights
				$weight = $rating_items[$rating_item_id]['weight'];
				$adjustment = ( $weight / $total_weight ) * $count_rating_items;
				
				// score result
				$score_result += intval( $value) ;
				$adjusted_score_result += $value * $adjustment;
				
				$total_rating_item_result += round( doubleval( $value ) / doubleval( $max_option_value ), 2 );
				$total_adjusted_rating_item_result += round( doubleval( $value * $adjustment ) / doubleval( $max_option_value ), 2 );
			} else {
				continue; // skip
			}
		}
		
		if ( count( $rating_item_entry_value_rows ) > 0 ) {
			// calculate 5 star result
			$star_result = round( doubleval( $total_rating_item_result ) / doubleval( $count_rating_items ), 2 ) * 5;
			$adjusted_star_result = round( doubleval($total_rating_item_result ) / doubleval( $count_rating_items ), 2 ) * 5;
		
			// calculate percentage result
			$percentage_result = round( doubleval( $total_rating_item_result ) / doubleval( $count_rating_items ), 2 ) * 100;
			$adjusted_percentage_result = round( doubleval( $total_rating_item_result) / doubleval( $count_rating_items ), 2 ) * 100;
			
			$rating_result = round( doubleval( $total_rating_item_result ) / doubleval( $count_rating_items ), 2 );
			$adjusted_rating_result = round( doubleval( $total_adjusted_rating_item_result ) / doubleval( $count_rating_items ), 2 );
		}
		
		return array(
				'adjusted_star_result' => $adjusted_star_result,
				'star_result' => $star_result,
				'total_max_option_value' => $total_max_option_value,
				'adjusted_score_result' => $adjusted_score_result,
				'score_result' => $score_result,
				'percentage_result' => $percentage_result,
				'adjusted_percentage_result' => $adjusted_percentage_result
			);
	}
	
	/**
	 * Helper to sort the top rating results
	 * 
	 * @param unknown_type $a
	 * @param unknown_type $b
	 */
	private static function sort_top_rating_results( $a, $b ) {
		
		if ( $a['score_result'] == $b['score_result'] ) {
			return 0;
		}
		
		return ( $a['score_result'] > $b['score_result'] ) ? -1 : 1;
	}
	
	/**
	 * Helper to sort the user rating results
	 *
	 * @param unknown_type $a
	 * @param unknown_type $b
	 */
	private static function sort_user_rating_results_by_rating_result( $a, $b ) {
		
		$rating_result_a = $a['rating_result'];
		$rating_result_b = $b['rating_result'];
		
		// sort by rating result
		if ( $rating_result_a['score_result'] == $rating_result_b['score_result'] ) {
			return 0;
		}
		return ( $rating_result_a['score_result'] > $rating_result_b['score_result'] ) ? -1 : 1;
	}
	
	
	/**
	 * Helper to sort the user rating results
	 *
	 * @param unknown_type $a
	 * @param unknown_type $b
	 */
	private static function sort_user_rating_results_by_entry_date( $a, $b ) {
		
		$entry_date_a = $a['entry_date'];
		$entry_date_b = $b['entry_date'];
	
		// sort by rating result
		if ( $entry_date_a == $entry_date_b ) {
			return 0;
		}
		return ( $entry_date_a > $entry_date_b ) ? -1 : 1;
	}
	
	
	/**
	 * Get the top rating results
	 * 
	 * @param int $count the count of top rating results to return
	 * @param int $rating_form_id
	 * @return array top rating results
	 */
	public static function get_top_rating_results( $limit = 10, $rating_form_id, $category_id = null ) {

		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$posts = get_posts( array(
				'numberposts' => -1,
				'post_type' => $general_settings[MRP_Multi_Rating::POST_TYPES_OPTION]
		) );
		
		$rating_items = MRP_Multi_Rating_API::get_rating_items( array(
				'rating_form_id' => $rating_form_id
		) );
		
		// iterate the post types and calculate rating results
		$top_rating_results = array();
		foreach ( $posts as $current_post ) {
			
			if ( $category_id != null ) { 
				// skip if not in category
				if ( ! in_category($category_id, $current_post->ID ) ) {
					continue;
				}
			}
			
			$rating_result = MRP_Multi_Rating_API::calculate_rating_result(array(
					'post_id' => $current_post->ID,
					'rating_items' => $rating_items,
					'rating_form_id' => $rating_form_id
			) );
			
			if ( intval( $rating_result['count'] ) > 0 ) {
				array_push( $top_rating_results, $rating_result );
			}
		}
		
		uasort( $top_rating_results, array( 'MRP_Multi_Rating_API' , 'sort_top_rating_results' ) );
		
		$top_rating_results = array_slice( $top_rating_results, 0, $limit );
		
		return $top_rating_results;
	}
	
	/**
	 * Get the top rating results
	 *
	 * @param int limit the limit of top rating results to return
	 * @param int rating_form_id
	 * @param string username
	 * @param int category_id
	 * @return array top rating results
	 */
	public static function get_user_rating_results( $limit = 10, $username, $category_id = null ) {
	
		$user_rating_result_rows = array();
		
		$rating_item_entries = MRP_Multi_Rating_API::get_rating_item_entries( array(
				'username' => $username,
				'category_id' => $category_id
		) );
			
		if ( count( $rating_item_entries ) > 0 ) {
			
			foreach ( $rating_item_entries as $rating_item_entry ) {
				
				$post_id = $rating_item_entry['post_id'];
				$rating_form_id =  $rating_item_entry['rating_form_id'];
				$rating_item_entry_id = $rating_item_entry['rating_item_entry_id'];
		
				$rating_items = MRP_Multi_Rating_API::get_rating_items( array(
						'post' => $post_id,
						'rating_item_entry_id' => $rating_item_entry_id
				) );
		
				$rating_result = MRP_Multi_Rating_API::calculate_rating_result( array(
						'post_id' => $post_id,
						'username' => $username,
						'rating_form_id' => $rating_form_id,
						'rating_items' => $rating_items
				) );
					
				array_push( $user_rating_result_rows, array(
						'rating_result' => $rating_result,
						'entry_date' => $rating_item_entry['entry_date']
				) );
			}
		}
	
		uasort( $user_rating_result_rows, array( 'MRP_Multi_Rating_API' , 'sort_user_rating_results_by_entry_date' ) );
	
		$user_rating_result_rows = array_slice( $user_rating_result_rows, 0, $limit );
		
		return $user_rating_result_rows;
	}
	
	
	
	/**
	 * Displays the rating form
	 * 
	 * @param unknown_type $params
	 */
	public static function display_rating_form( $params = array()) {
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
		$position_settings = (array) get_option( MRP_Multi_Rating::POSITION_SETTINGS );
		
		extract( wp_parse_args($params, array(
				'post_id' => null,
				'title' => $custom_text_settings[MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION],
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'submit_button_text' => $custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION],
				'update_button_text' => $custom_text_settings[MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION],
				'delete_button_text' => $custom_text_settings[MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION],
				'show_name_input' => $position_settings[MRP_Multi_Rating::SHOW_NAME_INPUT_OPTION],
				'show_email_input' => $position_settings[MRP_Multi_Rating::SHOW_EMAIL_INPUT_OPTION],
				'show_comment_textarea' => $position_settings[MRP_Multi_Rating::SHOW_COMMENT_TEXTAREA_OPTION],
				'rating_form_id' => $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ],
				'echo' => true,
				'class' => ''
		) ) );
	
		if ( is_string( $show_name_input ) ) {
			$show_name_input = $show_name_input == 'true' ? true : false;
		}
		if ( is_string( $show_email_input ) ) {
			$show_email_input = $show_email_input == 'true' ? true : false;
		}
		if ( is_string( $show_comment_textarea ) ) {
			$show_comment_textarea = $show_comment_textarea == 'true' ? true : false;
		}
		
		// get the post id
		global $post;
	
		if ( ! isset( $post_id ) && isset( $post ) ) {
			$post_id = $post->ID;
		} else if ( !isset($post) && !isset( $post_id ) ) {
			return; // No post Id available to display rating form
		}
	
		$rating_items = MRP_Multi_Rating_API::get_rating_items( array(
				'rating_form_id' => $rating_form_id
		) );
		
		$already_submitted_rating_form_message = $custom_text_settings[ MRP_Multi_Rating::ALREADY_SUBMITTED_RATING_FORM_MESSAGE_OPTION ];
		
		$html = MRP_Rating_Form_View::get_rating_form( $rating_items, $post_id, $rating_form_id, array(
				'title' => $title,
				'before_title' => $before_title,
				'after_title' => $after_title,
				'submit_button_text' => $submit_button_text,
				'update_button_text' => $update_button_text,
				'delete_button_text' => $delete_button_text,
				'show_name_input' => $show_name_input,
				'show_email_input' => $show_email_input,
				'show_comment_textarea' => $show_comment_textarea,
				'already_submitted_rating_form_message' => $already_submitted_rating_form_message,
				'class' => $class
		) );
		
		if ( $echo == true ) {
			echo $html;
		}
		
		return $html;
	}
	
	/**
	 * Displays the rating item results
	 * 
	 * @param unknown_type $params
	 */
	public static function display_rating_item_results( $params = array() ) {
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
		$position_settings = (array) get_option( MRP_Multi_Rating::POSITION_SETTINGS );
		
		extract( wp_parse_args( $params, array(
				'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
				'rating_form_id' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION],
				'show_count' => true,
				'title' => '',
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
				'post_id' => null,
				'echo' => true,
				'title' => $custom_text_settings[MRP_Multi_Rating::RATING_ITEM_RESULTS_TITLE_TEXT_OPTION],
				'show_title' => true,
				'class' => '',		
						// new
				'preserve_max_option' => true,
				'show_options' => false
		) ) );

		if ( $post_id == null ) { 
			return;
		}
		
		if ( is_string( $show_count ) ) {
			$show_count = $show_count == 'true' ? true : false;
		}
		if ( is_string( $echo ) ) {
			$echo = $echo == 'true' ? true : false;
		}
		if ( is_string( $show_title ) ) {
			$show_title = $show_title == 'true' ? true : false;
		}
		if ( is_string( $preserve_max_option ) ) {
			$preserve_max_option = $preserve_max_option == 'true' ? true : false;
		}
		if ( is_string( $show_options ) ) {
			$show_options = $show_options == 'true' ? true : false;
		}
		
		$rating_items = MRP_Multi_Rating_API::get_rating_items( array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id
		) );
		
		$rating_item_result_rows = array();
		$count = 0;
		foreach ($rating_items as $rating_item) {
			
			$rating_result = MRP_Multi_Rating_API::calculate_rating_item_result( $rating_item, $rating_form_id, $post_id );
					
			$count = intval( $rating_result['count'] );
			if ( intval( $rating_result['count'] ) > $count ) {
				$count = intval( $rating_result['count'] );
			}
			
			array_push( $rating_item_result_rows, array(
					'rating_item' => $rating_item,
					'rating_result' => $rating_result
			) );
		}
			
		$html = MRP_Rating_Result_View::get_rating_item_results_html( $rating_item_result_rows, array(
				'result_type' => $result_type,
				'show_count'=> $show_count,
				'title' => $title,
				'show_title' => $show_title,
				'before_title' => $before_title,
				'after_title' => $after_title,
				'no_rating_results_text' => $no_rating_results_text,
				'count' => $count,
				'class' => $class,
				'preserve_max_option' => $preserve_max_option,
				'show_options' => $show_options
		) );
		
		if ( $echo == true ) {
			echo $html;
		}
		
		return $html;
	}
	
	
	/**
	 * Displays the rating result
	 * 
	 * @param unknown_type $atts
	 * @return void|string
	 */
	public static function display_rating_result( $params = array()) {
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
		
		extract( wp_parse_args( $params, array(
				'post_id' => null,
				'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
				'rating_form_id' =>  $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ],
				'show_rich_snippets' => false,
				'show_title' => false,
				'show_date' => true,
				'show_count' => true,
				'echo' => true,
				'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
				'class' => ''
		) ) );
		
		if ( is_string( $show_rich_snippets ) ) {
			$show_rich_snippets = $show_rich_snippets == 'true' ? true : false;
		}
		if ( is_string( $show_title ) ) {
			$show_title = $show_title == 'true' ? true : false;
		}
		if ( is_string( $show_date ) ) {
			$show_date = $show_date == 'true' ? true : false;
		}
		if ( is_string( $show_count ) ) {
			$show_count = $show_count == 'true' ? true : false;
		}
		if ( is_string( $echo ) ) {
			$echo = $echo == 'true' ? true : false;
		}
		
		// get the post id
		global $post;
		
		if ( ! isset( $post_id ) && isset( $post ) ) {
			$post_id = $post->ID;
		} else if ( ! isset( $post ) && ! isset( $post_id ) ) {
			return; // No post Id available to display rating form
		}
	
		$rating_items = MRP_Multi_Rating_API::get_rating_items( array(
				'rating_form_id' => $rating_form_id
		) );
		
		$rating_result = MRP_Multi_Rating_API::calculate_rating_result( array(
				'post_id' => $post_id,
				'rating_items' => $rating_items,
				'rating_form_id' => $rating_form_id
		) );
		
		$html = MRP_Rating_Result_View::get_rating_result_html( $rating_result, array(
				'no_rating_results_text' => $no_rating_results_text,
				'show_rich_snippets' => $show_rich_snippets,
				'show_title' => $show_title,
				'show_date' => $show_date,
				'show_count' => $show_count,
				'no_rating_results_text' => $no_rating_results_text,
				'result_type' => $result_type,
				'class' => $class
		) );
		
		if ( $echo == true ) {
			echo $html;
		}
		
		return $html;
	}
	
	
	/**
	 * Displays reviews of rating forms with comments, star rating, name and also selected individual rating items
	 * 
	 * @param unknown_type $params
	 */
	public static function display_rating_result_reviews( $params = array() ) {
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
		
		extract( wp_parse_args($params, array(
				'post_id' => null,
				'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
				'rating_form_id' =>  $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ],
				'show_title' => false,
				'show_date' => true,
				'show_count' => true,
				'comments_only' => true,
				'title' => $custom_text_settings[MRP_Multi_Rating::RATING_RESULT_REVIEWS_TITLE_TEXT_OPTION],
				'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'echo' => false,
				'before_name' => '- ',
				'after_name' => '',
				'before_comment' => '"',
				'after_comment' => '"',
				'show_name' => true,
				'show_comment' => true,
				'rating_item_entry_ids' => null,
				'limit' => null,
				'show_indv_rating_item_results' => true,
				'echo' => true,
		        'show_category_filter' => false,
		        'category_id' => 0,
				'all_posts' => false,
				'view_format' => MRP_Multi_Rating::INLINE_VIEW_FORMAT,
				'show_view_more' => false,
				'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
				'class' => '',
				
				// added also removed show_rank
				'before_date' => '(',
				'after_date' => ')'
		) ) );
		
		if ( is_string( $show_title ) ) {
			$show_title = $show_title == 'true' ? true : false;
		}
		if ( is_string( $show_date ) ) {
			$show_date = $show_date == 'true' ? true : false;
		}
		if ( is_string( $show_count ) ) {
			$show_count = $show_count == 'true' ? true : false;
		}
		if ( is_string( $comments_only ) ) {
			$comments_only = $comments_only == 'true' ? true : false;
		}
		if ( is_string( $show_name ) ) {
			$show_name = $show_name == 'true' ? true : false;
		}
		if ( is_string( $show_comment ) ) {
			$show_comment = $show_comment == 'true' ? true : false;
		}
		if ( is_string( $show_indv_rating_item_results ) ) {
			$show_indv_rating_item_results = $show_indv_rating_item_results == 'true' ? true : false;
		}
		if ( is_string($echo ) ) {
			$echo = $echo == 'true' ? true : false;
		}
		if ( is_string( $all_posts ) ) {
			$all_posts = $all_posts == 'true' ? true : false;
		}
		if ( is_string( $show_category_filter ) ) {
			$show_category_filter = $show_category_filter == 'true' ? true : false;
		}
		if ( is_string( $show_view_more ) ) {
			$show_view_more = $show_view_more == 'true' ? true : false;
		}
		
		if ( $all_posts == false && $post_id == null ) {
			
			// get the post id
			global $post;
			
			if ( ! isset( $post_id ) && isset( $post ) ) {
				$post_id = $post->ID;
			} else if ( ! isset($post) && ! isset( $post_id ) ) {
				return; // No post Id available to display rating form
			}
		}
		
		// show the filter for categories
		if ( $show_category_filter == true ) {
			// override category id if set in HTTP request
			if ( isset( $_REQUEST['category-id'] ) ) {
				$category_id = $_REQUEST['category-id'];
			}
		}
		
		if ( isset( $_REQUEST['view-more'] ) ) {
			$limit = null;
		}
		
		if ( $category_id == 0 ) {
			$category_id = null; // so that all categories are returned
		}
		
		$rating_item_entries = MRP_Multi_Rating_API::get_rating_item_entries( array(
				'rating_form_id' => $rating_form_id,
				'post_id' => $post_id,
				'comments_only' => $comments_only,
				'rating_item_entry_ids' => $rating_item_entry_ids,
				'limit' => $limit,
				'category_id' => $category_id
		) );
		
		$review_data_rows = array();
		
		$html = '';
		if ( count( $rating_item_entries ) > 0 ) {
			$rating_items = MRP_Multi_Rating_API::get_rating_items( array(
					'post_id' => $post_id,
					'rating_form_id' => $rating_form_id
			) );
			
			$index = 0;
			
			foreach ( $rating_item_entries as $rating_item_entry ) {
				
				$rating_item_entry_id = $rating_item_entry['rating_item_entry_id'];
				
				$rating_result = MRP_Multi_Rating_API::calculate_rating_item_entry_result( $rating_item_entry_id, $rating_items );
			
				$rating_item_entry_values = null;
				if ($show_indv_rating_item_results) {
					$rating_item_entry_values = MRP_Multi_Rating_API::get_rating_item_entry_values( array(
							'rating_item_entry_id' => $rating_item_entry_id
					) );
				}
				
				$rating_item_entry['comment'];
				$name = $rating_item_entry['name'];
				$comment = $rating_item_entry['comment'];
				
				// override if a WP comment exists
				if ( $rating_item_entry['comment_id'] != '' ) {
					
					$comment_obj = get_comment( $rating_item_entry['comment_id'] );
					
					// only add comment if approved
					if ( $comment_obj->comment_approved == '1' ) {
						$name = $comment_obj->comment_author;
						$comment = $comment_obj->comment_content;
					} else { // skip
						continue;
					}
				}
			
				array_push( $review_data_rows, array(
						'rating_result' => $rating_result,
						'name' => $name,
						'comment' => $comment,
						'entry_date' => $rating_item_entry['entry_date'],
						'rating_item_entry_values' => $rating_item_entry_values,
						'rank' => $index
				) );
				
				$index++;
			}
		}
		
		$count_reviews = count( $review_data_rows );

		$view_params = array(
				'show_date' => $show_date,
				'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
				'show_name' => $show_name,
				'show_comment' => $show_comment,
				'show_indv_rating_item_results' => $show_indv_rating_item_results,
				'before_name' => $before_name,
				'after_name' => $after_name,
				'before_comment' => $before_comment,
				'after_comment' => $after_comment,
				'before_date' => $before_date,
				'after_date' => $after_date,
				'count' => $count_reviews,
				'show_count' => $show_count,
				'title' => $title,
				'show_title' => $show_title,
				'no_rating_results_text' => $no_rating_results_text,
				'show_category_filter' => $show_category_filter,
				'category_id' => $category_id,
				'show_view_more' => $show_view_more,
				'result_type' => $result_type,
				'class' => $class
		);
		
		// TODO pass the view format to the view class to decide how to handle it, then call an action
		if ( $view_format == MRP_Multi_Rating::TABLE_VIEW_FORMAT ) {
			$html .= MRP_Rating_Result_View::get_rating_result_review_table_html( $review_data_rows, $view_params );
		} else {
			$html .= MRP_Rating_Result_View::get_rating_result_review_inline_html( $review_data_rows, $view_params );
		}
		
		if ( $echo == true ) {
			echo $html;
		}
		
		return $html;
	}
	
	/**
	 * Displays the top rating results
	 * 
	 * @param $params
	 */
	public static function display_top_rating_results( $params = array()) {
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
		
		extract( wp_parse_args( $params, array(
				'rating_form_id' => $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ],
				'title' => $custom_text_settings[MRP_Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION],
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION ],
				'show_count' => true,
				'echo' => true,
				'show_category_filter' => true,
				'category_id' => 0, // 0 = All,
				'limit' => 10, // modified was count
				'show_rank' => true,
				'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
				'show_title' => true,
				'class' => ''
		) ) );
		
		if ( is_string($show_count) ) {
			$show_count = $show_count == 'true' ? true : false;
		}
		if ( is_string( $echo ) ) {
			$echo = $echo == 'true' ? true : false;
		}
		if ( is_string( $show_category_filter ) ) {
			$show_category_filter = $show_category_filter == 'true' ? true : false;
		}
		if ( is_string($show_rank ) ) {
			$show_rank = $show_rank == 'true' ? true : false;
		}
		if ( is_string( $show_title ) ) {
			$show_title = $show_title == 'true' ? true : false;
		}
		
		// show the filter for categories
		if ( $show_category_filter == true ) {
			// override category id if set in HTTP request
			if ( isset( $_REQUEST['category-id'] ) ) {
				$category_id = $_REQUEST['category-id'];
			}
		}
				
		if ( $category_id == 0 ) {
			$category_id = null; // so that all categories are returned
		}
	
		$top_rating_result_rows = MRP_Multi_Rating_API::get_top_rating_results( $limit, $rating_form_id, $category_id );
	
		$html = MRP_Rating_Result_View::get_top_rating_results_html( $top_rating_result_rows, array(
				'show_title' => $show_title,
				'show_count' => $show_count,
				'show_category_filter' => $show_category_filter,
				'category_id' => $category_id,
				'before_title' => $before_title,
				'after_title' => $after_title,
				'title' => $title,
				'show_rank' => $show_rank,
				'no_rating_results_text' => $no_rating_results_text,
				'result_type' => $result_type,
				'class' => $class
		) );
		
		if ( $echo == true ) {
			echo $html;
		}
		
		return $html;
	}
	
	/**
	 * Displays ratings for a specified user
	 *
	 * @param $params
	 */
	public static function display_user_rating_results( $params = array()) {
	
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
		// get username
		global $wp_roles;
		$current_user = wp_get_current_user();
		$username = $current_user->user_login;
		
		extract( wp_parse_args( $params, array(
				'title' => $custom_text_settings[MRP_Multi_Rating::USER_RATING_RESULTS_TITLE_TEXT_OPTION],
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION ],
				'echo' => true,
				'show_category_filter' => true,
				'category_id' => 0, // 0 = All
				'username' => $username,
				'show_date' => true,
				'show_rank' => true,
				'before_date' => '(',
				'after_date' => ')',
				'limit'=> 10,
				'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
				'class' => '',
				'show_title' => true,
				'show_count' => true
		) ) );
		
		if ( is_string( $show_date ) ) {
			$show_date = $show_date == 'true' ? true : false;
		}
		if ( is_string( $echo ) ) {
			$echo = $echo == 'true' ? true : false;
		}
		if ( is_string( $show_category_filter ) ) {
			$show_category_filter = $show_category_filter == 'true' ? true : false;
		}
		if ( is_string( $show_rank ) ) {
			$show_rank = $show_rank == 'true' ? true : false;
		}
		
		$user_rating_result_rows = array();
		
		// if user is logged in, retrieve their ratings and list them
		if ( strlen( $username ) > 0 ) {
			
			// show the filter for categories
			if ( $show_category_filter == true ) {
				// override category id if set in HTTP request
				if ( isset( $_REQUEST['category-id'] ) ) {
					$category_id = $_REQUEST['category-id'];
				}
			}
			
			if ( $category_id == 0 ) {
				$category_id = null; // so that all categories are returned
			}
				
			$user_rating_result_rows = MRP_Multi_Rating_API::get_user_rating_results( $limit, $username, $category_id );
			
		}
		
		// TODO sort based on rank and entry date - currently entry date sort is done
		
		$html = MRP_Rating_Result_View::get_user_rating_results_html( $user_rating_result_rows, array(
				'show_title' => $show_title,
				'show_date' => $show_date,
				'show_count' => $show_count,
				'show_category_filter' => $show_category_filter,
				'category_id' => $category_id,
				'before_title' => $before_title,
				'after_title' => $after_title,
				'title' => $title,
				'show_rank' => $show_rank,
				'before_date' => $before_date,
				'after_date' => $after_date,
				'no_rating_results_text' => $no_rating_results_text,
				'result_type' => $result_type,
				'class' => $class
		) );
	
		if ( $echo == true ) {
			echo $html;
		}
	
		return $html;
	}
	
	
	/**
	 * Gets the rating result and selected rating items for a comment
	 * 
	 * @param $params
	 */
	public static function get_comment_rating_result( $params = array()) {
		
		extract( wp_parse_args( $params, array(
				'echo' => false,
				'comment_id' => null,
				'class' => ''
		) ) );
		
		$rating_item_entries = MRP_Multi_Rating_API::get_rating_item_entries( array(
				'comment_id' => $comment_id
		) );
		
		if ( count( $rating_item_entries ) != 1 ) {
			return;
		}
		
		$rating_item_entry_id = $rating_item_entries[0]['rating_item_entry_id'];
		
		$rating_result = MRP_Multi_Rating_API::calculate_rating_item_entry_result( $rating_item_entry_id );
		
		$rating_item_entry_values = MRP_Multi_Rating_API::get_rating_item_entry_values( array(
				'rating_item_entry_id' => $rating_item_entry_id
		) );
		
		$html = MRP_Rating_Result_View::get_comment_rating_result( $rating_result, $rating_item_entry_values, array(
				'class' => $class
		) );
		
		if ( $echo == true ) {
			echo $html;
		}
		
		return $html;
	}
	
	/**
	 * Displays the comment rating form
	 * 
	 * @param $params
	 */
	public static function display_comment_rating_form( $params = array() ) {
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
		$position_settings = (array) get_option( MRP_Multi_Rating::POSITION_SETTINGS );
		
		// get the post id
		global $post;
		
		$post_id = null;
		if (isset( $post ) ) {
			$post_id = $post->ID;
		}
		
		extract( wp_parse_args( $params, array(
				'post_id' => $post_id,
				'title' => $custom_text_settings[MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION],
				'submit_button_text' => $custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION],
				//'rating_form_id' => $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ],
				'echo' => true,
				'class' => ''
		) ) );
		
		if ( $post_id == null ) {
			return; // No post Id available
		}
		
		ob_start();
		
		comment_form( array(
				'title_reply' => $title,
				'label_submit' => $submit_button_text,
				/*'fields' => $fields*/
		), $post_id );
		
		$html = ob_get_contents();
		
		ob_end_clean();

		if ( $echo == true ) {
			echo $html;
		}
		
		return $html;
	}
	
	/**
	 * Generates rating results in CSV format.
	 *
	 * @param $file_name the file_name to save
	 * @param $filters used to filter the report e.g. from_date, to_date, username etc...
	 * @returns true if report successfully generated and written to file
	 */
	public static function generate_rating_results_csv_file( $file_name, $filters ) {
		
		$rating_item_entries = MRP_Multi_Rating_API::get_rating_item_entries( $filters );
			
		
		$header_row = __( 'Entry ID', 'mrp' ) . ', '
				. __( 'Entry Date', 'multi-rating-pro' ) . ', '
				. __( 'Post ID', 'multi-rating-pro' ) . ', '
				. __( 'Post Title', 'multi-rating-pro' ) . ','
				. __( 'Rating Form ID', 'multi-rating-pro' ) . ','
				. __( 'Rating Form Name', 'multi-rating-pro' ) . ', '
				. __( 'Score Rating Result', 'multi-rating-pro' ) . ', '
				. __( 'Adjusted Score Rating Result', 'multi-rating-pro' ) . ', '
				. __( 'Total Max Option Value' , 'multi-rating-pro' ) . ', '
				. __('Percentage Rating Result', 'multi-rating-pro' ) . ', '
				. __( 'Adjusted Percentage Rating Result', 'multi-rating-pro' ) . ', '
				. __( 'Star Rating Result', 'multi-rating-pro' ) . ', ' 
				. __( 'Adjusted Star Rating Result', 'multi-rating-pro' ) . ', '
				. __( 'Username', 'multi-rating-pro' ) . ', '
				. __( 'Comment ID', 'multi-rating-pro' ) . ', '
				. __( 'Comment', 'multi-rating-pro' ) .', '
				. __( 'Name', 'multi-rating-pro' ) . ', '
				. __( 'E-mail', 'multi-rating-pro' );
		
		$export_data_rows = array( $header_row );
		
		if ( count( $rating_item_entries ) > 0 ) {
			
			foreach ( $rating_item_entries as $rating_item_entry ) {
				
				$post_id = $rating_item_entry['post_id'];
				$rating_form_id =  $rating_item_entry['rating_form_id'];
				$rating_item_entry_id = $rating_item_entry['rating_item_entry_id'];
		
				$rating_items = MRP_Multi_Rating_API::get_rating_items( array(
						'post' => $post_id,
						'rating_item_entry_id' => $rating_item_entry_id
				) );
				
				$rating_result = MRP_Multi_Rating_API::calculate_rating_item_entry_result( $rating_item_entry_id,  $rating_items );

				global $wpdb;
				
				$query = 'SELECT name FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME . ' WHERE rating_form_id = "' . $rating_form_id . '"';
				$result = $wpdb->get_col( $query, 0 );
				
				$rating_form_name = '';
				if ( isset( $result[0] ) ) {
					$rating_form_name = $result[0];
				}
				
				$comment_id = $rating_item_entry['comment_id'];
				$comment = stripslashes($rating_item_entry['comment']);
				$name = $rating_item_entry['name'];
				$email = $rating_item_entry['email'];
				if ( $comment_id != '' ) {
					$comment_obj = get_comment( $comment_id );
					$name = $comment_obj->comment_author;
					$email = $comment_obj->comment_author_email;
					$comment = $comment_obj->comment_content;
				}
				
				$current_row = $rating_item_entry_id .', ' . $rating_item_entry['entry_date'] . ', ' 
						. $post_id . ', ' . get_the_title($post_id) . ', ' . $rating_form_id . ', ' 
						. $rating_form_name . ', ' . $rating_result['score_result'] . ', '  
						. $rating_result['adjusted_score_result'] . ', ' . $rating_result['total_max_option_value'] . ', ' 
						. $rating_result['percentage_result'] . ', ' . $rating_result['adjusted_percentage_result'] . ', ' 
						. $rating_result['star_result'] . ', ' . $rating_result['adjusted_star_result'] . ', ' 
						. $rating_item_entry['username'] . ', ' . $comment_id . ', ' . $comment . ', ' . $name . ', ' . $email;
				
				array_push( $export_data_rows, $current_row );
			}
		}
		
		$file = null;
		try {
			$file = fopen( $file_name, 'w' );
			foreach ( $export_data_rows as $row ) {
				fputcsv( $file, explode(',', $row ) );
			}
			fclose( $file );
		} catch ( Exception $e ) {
			return false;
		}
		
		return true;
	}
}?>