<?php
/**
 * View class for rating results
 * 
 * @author dpowney
 *
 */
class MRP_Rating_Result_View {
	
	/**
	 * Gets the comment rating result HTML
	 * 
	 * @param $rating_result
	 * @param $rating_item_entry_values
	 * @param $params
	 */
	public static function get_comment_rating_result( $rating_result, $rating_item_entry_values, $params = array() ) {
		
		extract(wp_parse_args($params, array(
				'class' => ''
		) ) );
		
		$html = '';
		
		$style_settings = (array) get_option( MRP_Multi_Rating::STYLE_SETTINGS );
		$star_rating_colour = $style_settings[MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION];
		$font_awesome_version = $style_settings[MRP_Multi_Rating::FONT_AWESOME_VERSION_OPTION];
		$icon_classes = MRP_Utils::get_icon_classes( $font_awesome_version );
		
		foreach ( $rating_item_entry_values as $rating_item_entry_value ) {
			
			$html .= '<p><label class="description">' . stripslashes( $rating_item_entry_value['description'] ) . '</label>';
			
			$rating_item_type = $rating_item_entry_value['type'];
			
			if ( $rating_item_type == 'star_rating' ) {
				
				$max_option_value = $rating_item_entry_value['max_option_value'];
				$value = $rating_item_entry_value['value'];
				
				$html .= '<span class="star-rating" style="color: ' . $star_rating_colour . '">';
				$index = 0;
				for ( $index; $index<$max_option_value; $index++ ) {
					$class = $icon_classes['star_full'];
					if ( $value < $index+1 ) { // if value is less than current icon, it must be empty
						$class = $icon_classes['star_empty'];
					}
					$html .= '<i class="' . $class .'"></i>';
				}
				$html .= '</span>';
				
			} else if ( $rating_item_type == 'thumbs' ) {
				$value = $rating_item_entry_value['value'];
				
				$class = $icon_classes['thumbs_up_on'];
				$html .= '<span class="thumbs">';
				if ( $value == 0 ) {
					$class = $icon_classes['thumbs_down_on'];
				}
				$html .= '<i class="' . $class . '"></i>';
				$html .= '</span>';
			} else {
				$html .= '<span class="value-text">' . stripslashes( $rating_item_entry_value['value_text'] ) . '</span>';
			}
			$html .= '</p>';
		}
		
		return $html;
	}
	
	/**
	 * Gets the User Rating Results HTML
	 * 
	 * @param unknown_type $user_rating_result_rows
	 * @param unknown_type $params
	 */
	public static function get_user_rating_results_html( $user_rating_result_rows, $params = array() ) {

		extract( wp_parse_args( $params, array(
				'show_title' => true,
				'show_date' => false,
				'show_count' => false,
				'show_category_filter' => true,
				'category_id' => 0,
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'title' => null,
				'show_rank' => true,
				'before_date' => '(',
				'after_date' => ')',
				'no_rating_results_text' => '',
				'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
				'class' => ''
		)));
		
		if ( $category_id == null ) {
			$category_id = 0; // so that all categories are returned
		}
		
		$html = '<div class="user-rating-results ' . $class . '">';
		
		if ( ! empty( $title )) {
			$html .=  $before_title . $title . $after_title;
		}
		
		if ($show_category_filter == true) {
			$html .= '<form action="" class="category-id-filter" method="POST">';
			$html .= '<label for="category-id">' . __( 'Category', 'multi-rating-pro' ) . '</label>';
			$html .= wp_dropdown_categories( array(
					'echo' => false,
					'class' => 'category-id',
					'name' => 'category-id',
					'id' => 'category-id', 
					'selected' => $category_id,
					'show_option_all' => 'All'
			) );
			$html .= '<input type="submit" value="' . __( 'Filter', 'multi-rating-pro' ) . '" />';
			$html .= '</form>';
		}
		
		if ( count( $user_rating_result_rows ) == 0 ) {
			$html .= '<p>' . $no_rating_results_text . '</p>';
		} else {
			$html .= '<table>';
			$index = 1;
			foreach ( $user_rating_result_rows as $user_rating_result_row ) {
				$html .= '<tr>';
				
				$rating_result = $user_rating_result_row['rating_result'];
				$entry_date = $user_rating_result_row['entry_date'];
				
				if ( $show_rank ) {
					$html .= '<td>';
					$html .= '<span class="rank">' . $index . '</span>';
					$html .= '</td>';
				}
				
				$html .= '<td>';
				
				$params['show_date'] = false;
				$params['show_title'] = false;
				$html .= MRP_Rating_Result_View::get_rating_result_type_html( $rating_result, array(
						'result_type' => $result_type,
						'show_date' => false,
						'show_title' => false,
						'show_count' => $show_count
				) );
				$html .= '</td>';
				
				if ( $show_title == true ) {
					$html .= '<td>';
					$post_id = $rating_result['post_id'];
					$post = get_post( $post_id );
					$html .= '<a class="title" href="' . get_permalink( $post_id ) . '">' . $post->post_title . '</a>';
					$html .= '</td>';
				}
					
				if ( $show_date == true && $entry_date != null ) {
					$html .= '<td>';
					$html .= '<span class="date">' . $before_date . mysql2date( get_option( 'date_format' ), $entry_date ) . $after_date . '</span>';
					$html .= '</td>';
				}
				
				$html .= '</tr>';
				$index++;
			}
			
			$html .= '</table>';
		}
		
		$html .= '</div>';
		
		return $html;
		
	}
	
	/**
	 * Gets the Top Rating Results HTML
	 * 
	 * @param unknown_type $top_rating_result_rows
	 * @param unknown_type $params
	 */
	public static function get_top_rating_results_html( $top_rating_result_rows, $params = array() ) {
	
		extract(wp_parse_args( $params, array(
				'show_title' => true,
				'show_count' => false,
				'show_category_filter' => true,
				'category_id' => 0,
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'title' => null,
				'show_rank' => true,
				'no_rating_results_text' => '',
				'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
				'class' => ''
		) ) );
	
		if ( $category_id == null ) {
			$category_id = 0; // so that all categories are returned
		}
	
		$html = '<div class="top-rating-results ' . $class . '">';
	
		if ( ! empty( $title ) ) {
			$html .=  $before_title . $title . $after_title;
		}
	
		if ( $show_category_filter == true ) {
			$html .= '<form action="" class="category-id-filter" method="POST">';
			$html .= '<label for="category-id">' . __( 'Category', 'multi-rating-pro' ) . '</label>';
			$html .= wp_dropdown_categories( array(
					'echo' => false,
					'class' => 'category-id',
					'name' => 'category-id',
					'id' => 'category-id',
					'selected' => $category_id,
					'show_option_all' => 'All'
			) );
			$html .= '<input type="submit" value="' . __( 'Filter', 'multi-rating-pro' ) . '" />';
			$html .= '</form>';
		}
	
		if ( count( $top_rating_result_rows ) == 0 ) {
			$html .= '<p>' . $no_rating_results_text . '</p>';
		} else {
			$html .= '<table>';
			$index = 1;
			foreach ( $top_rating_result_rows as $rating_result ) {
				$html .= '<tr>';
				
				if ( $show_rank ) {
					$html .= '<td>';
					$html .= '<span class="rank">' . $index . '</span>';
					$html .= '</td>';
				}
	
				$html .= '<td>';
	
				$html .= MRP_Rating_Result_View::get_rating_result_type_html( $rating_result, array(
						'show_date' => false,
						'show_title' => false,
						'show_count' => true,
						'result_type' => $result_type
				) );
				$html .= '</td>';
	
				if ( $show_title == true ) {
					$html .= '<td>';
					$post_id = $rating_result['post_id'];
					$post = get_post ($post_id );
					$html .= '<a  class="title" href="' . get_permalink( $post_id ) . '">' . $post->post_title . '</a>';
					$html .= '</td>';
				}
	
				$html .= '</tr>';
				
				$index++;
			}
				
			$html .= '</table>';
		}
	
		$html .= '</div>';
	
		return $html;
	
	}
	
	/**
	 * Gets the Rating Result Reviews in an inline HTML format i.e. no table
	 * 
	 * @param $review_data_rows
	 * @param $params
	 */
	public static function get_rating_result_review_table_html( $review_data_rows, $params = array() ) {
	
		extract ( wp_parse_args( $params, array(
				'show_date' => true,
				'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
				'before_name' => '- ',
				'after_name' => '',
				'before_comment' => '',
				'after_comment' => '',
				'before_date' => '(',
				'after_date' => ')',
				'show_name' => true,
				'show_comment' => true,
				'show_title' => true,
				'title' => null,
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'show_count' => true,
				'count' => null,
				'no_rating_results_text' => '',
				'show_indv_rating_item_results' => true,
				'show_category_filter' => false,
				'category_id' => 0,
				'show_view_more' => false,
				'class' => ''
		)));
		
		$html = '<div class="rating-result-reviews ' . $class . '">';
		
		if ( $show_title && strlen( $title ) > 0 ) {
			if ( $show_count && is_numeric( $count ) ) {
				$title .= ' <span class="count">(' . $count . ')</span>';
			}
				
			$html .= $before_title . $title . $after_title;
		}
		
		if ( $show_category_filter == true ) {
			$html .= '<form action="" class="category-id-filter" method="POST">';
			$html .= '<label for="category-id">' . __( 'Category', 'multi-rating-pro' ) . '</label>';
			$html .= wp_dropdown_categories( array(
					'echo' => false,
					'class' => 'category-id',
					'name' => 'category-id',
					'id' => 'category-id',
					'selected' => $category_id,
					'show_option_all' => 'All'
			) );
			$html .= '<input type="submit" value="' . __( 'Filter', 'multi-rating-pro' ) . '" />';
			$html .= '</form>';
		}
		
		if ( $count == 0 ) {
			$html .= '<p>' . $no_rating_results_text . '</p>';
		} else {
			
			$style_settings = (array) get_option( MRP_Multi_Rating::STYLE_SETTINGS );
			$star_rating_colour = $style_settings[MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION];
			$font_awesome_version = $style_settings[MRP_Multi_Rating::FONT_AWESOME_VERSION_OPTION];
			$icon_classes = MRP_Utils::get_icon_classes( $font_awesome_version );
		
			$html .= '<table>';
			foreach ( $review_data_rows as $review_data_row ) {
				
				$rating_result = $review_data_row['rating_result'];
				$comment = $review_data_row['comment'];
				$name = $review_data_row['name'];
				$entry_date = $review_data_row['entry_date'];
				$rating_result['count'] = null;
				
				$html .= '<tr class="rating-result-review">';
				
				$html .= '<td>';
				
				if ( $show_name || $show_date ) {
					$html .= '<p class="review-meta">';
					
					if ( $show_name ) {
						$html .= '<span class="name">';
						
						if ( strlen( trim( $name ) ) == 0 ) {
							$name = __( 'Anonymous', 'multi-rating-pro' );
						}
						
						$html .= $before_name . stripslashes( $name ) . $after_name;
						$html .= '</span>';
					}
				
					if ( $show_date ) {
						$html .= ' <span class="date">' . $before_date . mysql2date( get_option( 'date_format' ), $entry_date ) . $after_date . '</span>';
					}
					
					$html .= '</p>';
				}
				$html .= '</td>';
				
				$html .= '<td>';
				$html .= '<div class="review-details">';
				
				if ( strlen( $comment ) > 0 && $show_comment ) {
					$html .= '<p class="comment">' . $before_comment . nl2br( stripslashes( $comment ) ) . $after_comment . '</p>';
				}
				
				if ( $show_indv_rating_item_results && isset( $review_data_row['rating_item_entry_values'] ) ) {
					
					foreach ( $review_data_row['rating_item_entry_values'] as $rating_item_entry_value ) {
						$html .= '<p class="rating-item-result">';
						$html .= '<label class="description">' . stripslashes( $rating_item_entry_value['description'] ) . '</label>';
						
						$rating_item_type = $rating_item_entry_value['type'];
						
						if ( $rating_item_type == 'star_rating' ) {
							$style_settings = (array) get_option( MRP_Multi_Rating::STYLE_SETTINGS );
							$star_rating_colour = $style_settings[MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION];
						
							$max_option_value = $rating_item_entry_value['max_option_value'];
							$value = $rating_item_entry_value['value'];
						
							$html .= '<span class="star-rating" style="color: ' . $star_rating_colour . ' !important;">';
							$index = 0;
							for ( $index; $index<$max_option_value; $index++ ) {
								$class = $icon_classes['star_full'];
								if ( $value < $index+1 ) { // if value is less than current icon, it must be empty
									$class = $icon_classes['star_empty'];
								}
								$html .= '<i class="' . $class .'"></i>';
							}
							$html .= '</span>';
							
						} else if ( $rating_item_type == 'thumbs' ) {
							$value = $rating_item_entry_value['value'];
							
							$class = $icon_classes['thumbs_up_on'];
							$html .= '<span class="thumbs" style="color: ' . $star_rating_colour . ' !important;">';
							if ( $value == 0 ) {
								$class = $icon_classes['thumbs_down_on'];
							}
							$html .= '<i class="' . $class . '"></i>';
							$html .= '</span>';
							
						} else {
							$html .= '<span class="value-text">' . stripslashes( $rating_item_entry_value['value_text'] ) . '</span>';
						}
						
						$html .= '</p>';
					}
				}
				$html .= '</div>';
				$html .= '</td>';
				$html .= '</tr>';
			}
			
			$html .= '</table>';
			
			// TODO move to a common function
			if ( $show_view_more == true && ! isset( $_GET['view-more'] ) ) {
				$show_view_more = MRP_Utils::get_current_url();
				
				if ( parse_url( $show_view_more, PHP_URL_QUERY ) ) {
					$show_view_more.= '&';
				} else {
					$show_view_more .= '?';
				}
				$show_view_more .= 'view-more=true';
				
				$html .= '<a href="' . $show_view_more . '">' . __( 'View more', 'multi-rating-pro' ) . '</a>';
			}
		}
		$html .= '</div>';

		return $html;
	}
	
	/**
	 * Returns the HTML for the Rating Result Reviews in an inline format (i.e. no table)
	 *
	 * @param unknown_type $review_data_rows
	 * @param unknown_type $params
	 */
	public static function get_rating_result_review_inline_html( $review_data_rows, $params = array() ) {
	
		extract ( wp_parse_args( $params, array(
				'show_date' => true,
				'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
				'before_name' => '- ',
				'after_name' => '',
				'before_comment' => '',
				'after_comment' => '',
				'before_date' => '(',
				'after_date' => ')',
				'show_name' => true,
				'show_comment' => true,
				'show_title' => true,
				'title' => null,
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'show_count' => true,
				'count' => null,
				'no_rating_results_text' => '',
				'show_indv_rating_item_results' => true,
				'show_view_more' => false,
				'class' => ''
		) ) );
	
		$html = '<div class="rating-result-reviews ' . $class  . '">';
	
		if ( $show_title && strlen($title) > 0 ) {
			if ( $show_count && is_numeric( $count ) ) {
				$title .= ' <span class="count">(' . $count . ')</span>';
			}
	
			$html .= $before_title . $title . $after_title;
		}
		
		if ( $show_category_filter == true ) {
			$html .= '<form action="" class="category-id-filter" method="POST">';
			$html .= '<label for="category-id">' . __( 'Category', 'multi-rating-pro' ) . '</label>';
			$html .= wp_dropdown_categories( array(
					'echo' => false,
					'class' => 'category-id',
					'name' => 'category-id',
					'id' => 'category-id', 
					'selected' => $category_id,
					'show_option_all' => 'All'
			) );
			$html .= '<input type="submit" value="' . __( 'Filter', 'multi-rating-pro' ) . '" />';
			$html .= '</form>';
		}
	
		if ( $count == 0 ) {
			$html .= '<p>' . $no_rating_results_text . '</p>';
		} else {
	
			foreach ( $review_data_rows as $review_data_row ) {
	
				$html .= '<div class="rating-result-review">';
				
				$rating_result = $review_data_row['rating_result'];
				$comment = $review_data_row['comment'];
				$name = $review_data_row['name'];
				$entry_date = $review_data_row['entry_date'];
				$rating_result['count'] = null;

				if ( ( strlen($comment) > 0 && $show_comment ) || ( $show_indv_rating_item_results && isset( $review_data_row['rating_item_entry_values'] ) ) ) {
					$html .= '<div class="review-details">';
					
					if ( strlen($comment) > 0 && $show_comment ) {
						$html .= '<p class="comment">' . $before_comment . nl2br( stripslashes( $comment ) ) . $after_comment . '</p>';
					}
		
					if ( $show_indv_rating_item_results && isset( $review_data_row['rating_item_entry_values'] ) ) {
						
						$style_settings = (array) get_option( MRP_Multi_Rating::STYLE_SETTINGS );
						$star_rating_colour = $style_settings[MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION];
						$font_awesome_version = $style_settings[MRP_Multi_Rating::FONT_AWESOME_VERSION_OPTION];
						$icon_classes = MRP_Utils::get_icon_classes( $font_awesome_version );
						
						foreach ( $review_data_row['rating_item_entry_values'] as $rating_item_entry_value ) {
							$html .= '<p class="rating-item-result">';
							
							$html .= '<label class="description">' . stripslashes( $rating_item_entry_value['description'] ) . '</label>';
							
							$rating_item_type = $rating_item_entry_value['type'];
							
							if ( $rating_item_type == 'star_rating' ) {
								$style_settings = (array) get_option( MRP_Multi_Rating::STYLE_SETTINGS );
								$star_rating_colour = $style_settings[MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION];
							
								$max_option_value = $rating_item_entry_value['max_option_value'];
								$value = $rating_item_entry_value['value'];
							
								$html .= '<span class="star-rating" style="color: ' . $star_rating_colour . ' !important;">';
								$index = 0;
								for ( $index; $index<$max_option_value; $index++ ) {
									$class = $icon_classes['star_full'];
									if ( $value < $index+1 ) { // if value is less than current icon, it must be empty
										$class = $icon_classes['star_empty'];
									}
									$html .= '<i class="' . $class .'"></i>';
								}
								$html .= '</span>';
								
							} else if ($rating_item_type == 'thumbs') {
								$value = $rating_item_entry_value['value'];
								
								$class = $icon_classes['thumbs_up_on'];
								$html .= '<span class="thumbs" style="color: ' . $star_rating_colour . ' !important;">';
								if ( $value == 0 ) {
									$class = $icon_classes['thumbs_down_on'];
								}
								$html .= '<i class="' . $class . '"></i>';
								$html .= '</span>';
								
							} else {
								$html .= '<span class="value-text">' . stripslashes( $rating_item_entry_value['value_text'] ) . '</span>';
							}
							
							$html .= '</p>';
						}
					}
					$html .= '</div>';
				}
				
				if ( $show_name || $show_date ) {
					
					$html .= '<p class="review-meta">';
					
					if ( $show_name ) {
						$html .= '<span class="name">';
						if ( strlen( trim( $name ) ) == 0 ) {
							$name = __( 'Anonymous', 'multi-rating-pro' );
						}
						$html .= $before_name . stripslashes( $name ) . $after_name;
						$html .= '</span>';
					}
				
					if ( $show_date ) {
						$html .= ' <span class="date">' . $before_date . mysql2date( get_option( 'date_format' ), $entry_date ) . $after_date . '</span>';
					}
				
					$html .= '</p>';
				}
				
				$html .= '</div>';
			}
			
			// view more
			if ( $show_view_more == true && ! isset( $_GET['view-more'] ) ) {
				$show_view_more = MRP_Utils::get_current_url();
				
				if ( parse_url( $show_view_more, PHP_URL_QUERY ) ) {
					$show_view_more .= '&';
				} else {
					$show_view_more .= '?';
				}
				$show_view_more .= 'view-more=true';
				
				$html .= '<a href="' . $show_view_more . '">' . __( 'View more', 'multi-rating-pro' ) . '</a>';
			}
		}
		
		$html .= '</div>';
	
		return $html;
	
	}
	
	/**
	 * Gets the Rating Result HTML
	 * 
	 * @param $rating_result
	 * @param $params
	 */
	public static function get_rating_result_html( $rating_result, $params = array() ) {
			
		extract( wp_parse_args( $params, array(
				'no_rating_results_text' => null,
				'show_title' => false,
				'show_date' => false,
				'show_rich_snippets' => false ,
				'show_count' => true,
				'date' => null,
				'before_date' => '(',
				'after_date' => ')',
				'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
				'class' => ''
		) ) );
		
		$html = MRP_Rating_Result_View::get_rating_result_type_html( $rating_result, $params );
		
		return $html;
	}
	
	/**
	 * Gets the Rating Item Results HTML
	 * 
	 * @param $rating_result
	 * @param $rating_item
	 * @param $params
	 */
	public static function get_rating_item_results_html( $rating_item_result_rows, $params ) {
		
		extract( wp_parse_args( $params, array(
				'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
				'show_count' => true,
				'title' => '',
				'show_title' => true,
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'no_rating_results_text' => '',
				'count' => null,
				'class' => '',
				'preserve_max_option' => true,
				'show_options' => false
		) ) );
		
		$html = '<div class="rating-item-results ' . $class  . '">';
		
		if ( $show_title && strlen( $title ) > 0 ) {
			if ( $show_count && is_numeric( $count ) ) {
				$title = $title . ' <span class="count">(' . $count . ')</span>';
			}
				
			$html .= $before_title . $title . $after_title;
		}
		
		if ( $count == 0 ) {
			$html .= '<p>' . $no_rating_results_text . '</p>';
		} else {
			
			$style_settings = (array) get_option( MRP_Multi_Rating::STYLE_SETTINGS );
			$star_rating_colour = $style_settings[MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION];
			$font_awesome_version = $style_settings[MRP_Multi_Rating::FONT_AWESOME_VERSION_OPTION];
			$icon_classes = MRP_Utils::get_icon_classes( $font_awesome_version );
		
			$html .= '<table>';
			foreach ( $rating_item_result_rows as $rating_item_result ) {
				$rating_result = $rating_item_result['rating_result'];
				$rating_item = $rating_item_result['rating_item'];
				
				$html .= '<tr class="rating-item-result">';
				
				$html .= '<td>';
				$html .= '<label class="description">' . stripslashes( $rating_item['description'] ) . '</label>';
				$html .= '</td>';
				
				
				$html .= '<td>';
				
				// only percentage result type overrides thumbs up/down count
				if ( $rating_item['type'] == 'thumbs' ) {
					$option_totals = $rating_result['option_totals'];
					
					$thumbs_down = $option_totals[0];
					$thumbs_up = $option_totals[1];
					if ($result_type == 'percentage') {
						$thumbs_down = ( round(doubleval( $thumbs_down ) / $count, 2 ) * 100 ) . '%';
						$thumbs_up = ( round(doubleval( $thumbs_up ) / $count, 2 ) * 100 ) . '%';
					}
					
					$html .= '<i class="' . $icon_classes['thumbs_down_on'] . '"></i> ' . '<span class="total">(' . $thumbs_down . ')</span>';
					$html .= '<i class="' . $icon_classes['thumbs_up_on'] . '"></i> ' . '<span class="total">(' . $thumbs_up . ')</span>';
					
				} else {
					
					// if show options and not a star rating item type (so select and radio only)
					if ( $show_options == true && $rating_item['type'] != 'star_rating' ) {
						$option_value_text_array = preg_split( '/[\r\n,]+/',  $rating_item['option_value_text'], -1, PREG_SPLIT_NO_EMPTY );
							
						$option_value_text_lookup = array();
						foreach ( $option_value_text_array as $current_option_value_text ) {
							$parts = explode( '=', $current_option_value_text );
						
							if ( isset( $parts[0] ) && isset( $parts[1] ) ) {
								$value = intval( $parts[0] );
								$text = $parts[1];
									
								$option_value_text_lookup[$value] = $text;
							}
						}
							
						$option_totals = $rating_result['option_totals'];
						foreach ( $option_totals as $value => $total ) {
							$text = $value;
							if ( isset($option_value_text_lookup[$value] ) ) {
								$text = $option_value_text_lookup[$value];
							}
							
							$html .= '<span class="option">' . $text . '</span>';
							$html .= '<span class="total">&nbsp;(';
							if ( $result_type == 'percentage' ) {
								$html .= ( round($total / $count, 2 ) * 100 ) . '%';
							} else {
								$html .= $total;
							}
							$html .= ')</span>';
						}
					} else { // star ratings
						
						$html .= MRP_Rating_Result_View::get_rating_result_type_html($rating_result, array(
								'result_type' => $result_type,
								'show_count' => false,
								'ignore_count' => true,
								'preserve_max_option' => $preserve_max_option
						) );
					}
				}
				$html .= '</td>';
				
				$html . '</tr>';
			}
			
			$html .= '</table>';
		}
		
		$html .= '</div>';
		
		return $html;
	}

	/**
	 * Helper method for getting the rating result type HTML
	 * 
	 * @param $rating_result
	 * @param $params
	 */
    public static function get_rating_result_type_html( $rating_result, $params = array() ) {
    	
    	extract( wp_parse_args( $params, array(
    			'show_title' => false,
    			'show_date' => false,
    			'show_rich_snippets' => false,
    			'show_count' => true,
    			'date' => null,
    			'before_date' => '(',
    			'after_date' => ')',
    			'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
    			'no_rating_results_text' => '',
    			'ignore_count' => false,
    			'class' => '',
    			'preserve_max_option' => false
    	)));
    	
    	$html = '<span class="rating-result ' . $class . '"';
    	
    	$count = isset( $rating_result['count'] ) ? $rating_result['count'] : 0;
    	
    	if ( ( $count == null || $count == 0 ) && $ignore_count == false ) {
    		$html .= '><span class="no-rating-results-text">' . $no_rating_results_text . '</span>';
    	} else {
    		
			if  ( $show_rich_snippets && $result_type == MRP_Multi_Rating::STAR_RATING_RESULT_TYPE ) {
				$html .= ' itemscope itemtype="http://schema.org/Article"';
			}
			$html .= '>';

			if ( $show_title == true ) {
				$post_id = $rating_result['post_id'];
				$post = get_post( $post_id );
				$html .= '<a href="' . get_permalink( $post_id ) . '">' . $post->post_title . '</a>';
			}
			
	    	if ( $result_type == MRP_Multi_Rating::SCORE_RESULT_TYPE ) {
				$html .= '<span class="score-result">' . $rating_result['adjusted_score_result'] . '/' . $rating_result['total_max_option_value'] . '</span>';
			} else if ( $result_type == MRP_Multi_Rating::PERCENTAGE_RESULT_TYPE ) {
				$html .= '<span class="percentage-result">' . $rating_result['adjusted_percentage_result'] . '%</span>';
			} else { // star rating
				
				// TODO move this to common function
				
				$style_settings = (array) get_option( MRP_Multi_Rating::STYLE_SETTINGS );
				$star_rating_colour = $style_settings[MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION];
				$font_awesome_version = $style_settings[MRP_Multi_Rating::FONT_AWESOME_VERSION_OPTION];
				$icon_classes = MRP_Utils::get_icon_classes( $font_awesome_version );
				
				$html .= '<span class="star-rating" style="color: ' . $star_rating_colour . ' !important;">';
				$index = 0;
				
				if ( $preserve_max_option ) { // keep out of max option value i.e. not default out of 5
					
					for ( $index; $index<$rating_result['max_option_value']; $index++ ) {
						
						$class = $icon_classes['star_full'];
						
						if ( $rating_result['adjusted_score_result'] < $index+1 ) {
					
							$diff = $rating_result['adjusted_score_result'] - $index;
								
							if ( $diff > 0 ) {
								if ( $diff >= 0.3 && $diff <= 0.7 ) {
									$class = $icon_classes['star_half'];
								} else if ( $diff < 0.3 ) {
									$class = $icon_classes['star_empty'];
								} else {
									$class = $icon_classes['star_full'];
								}
							} else {
								$class = $icon_classes['star_empty'];
							}
					
						} else {
							$class = $icon_classes['star_full'];
						}
					
						$html .= '<i class="' . $class . '"></i>';
					}
					
					$html .= '</span>';	
					$html .= '<span class="star-result">' . $rating_result['adjusted_score_result'] . '/' . $rating_result['max_option_value'] . '</span>';
					
				} else { // out of 5
					for ( $index; $index<5; $index++ ) {
						
						$class = $icon_classes['star_full'];
	
						if ( $rating_result['adjusted_star_result'] < $index+1 ) {
								
							$diff = $rating_result['adjusted_star_result'] - $index;
							
							if ( $diff > 0 ) {
								if ( $diff >= 0.3 && $diff <= 0.7 ) {
									$class = $icon_classes['star_half'];
								} else if ( $diff < 0.3 ) {
									$class = $icon_classes['star_empty'];
								} else {
									$class = $icon_classes['star_full'];
								}
							} else {
								$class = $icon_classes['star_empty'];
							}
	
						} else {
							$class = $icon_classes['star_full'];
						}
	
						$html .= '<i class="' . $class . '"></i>';
					}
					
					$html .= '</span>';
					$html .= '<span class="star-result">' . $rating_result['adjusted_star_result'] . '/5</span>';
				}
			}
			
			if ( $show_count && $count != null ) {
				$html .= '<span class="count">(' . $count . ')</span>';
			}
			
			if ( $show_date == true && $date != null ) {
				$html .= '<span class="date">' . $before_date . mysql2date( get_option('date_format'), $date ) . $after_date . '</span>';
			}
			
			if ( is_singular() && $show_rich_snippets == true ) {
				$html .= '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" class="rating-result-summary" style="display: none;">';
				$html .= '<span itemprop="ratingValue">' . $rating_result['adjusted_star_result'] . '</span>/<span itemprop="bestRating">5</span>';
				$html .= '<span itemprop="ratingCount" style="display:none;">' . $count . '</span>';
				$html .= '</span>';
			}
    	}
		
		$html .= '</span>';
    	
    	return $html;
    }
}