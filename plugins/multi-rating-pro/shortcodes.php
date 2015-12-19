<?php 

/**
 * Shortcode to display the rating form
 */
function mrp_display_rating_form( $atts = array() ) {
	
	if ( is_admin() ) {
		return;
	}
	
	// get the post id
	global $post;
	
	$post_id = null;
	if (isset( $post ) ) {
		$post_id = $post->ID;
	}
	
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
	$position_settings = (array) get_option( MRP_Multi_Rating::POSITION_SETTINGS );
	
	// if a rating form is not specified in post meta, use default settings
	$rating_form_id = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_ID_POST_META, true );
	if ( $rating_form_id == '' ) {
		$rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
	}
	
	extract( shortcode_atts( array(
			'post_id' => $post_id,
			'title' => $custom_text_settings[MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'submit_button_text' => $custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION],
			'update_button_text' => $custom_text_settings[MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION],
			'delete_button_text' => $custom_text_settings[MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION],
			'show_name_input' => $position_settings[MRP_Multi_Rating::SHOW_NAME_INPUT_OPTION],
			'show_email_input' => $position_settings[MRP_Multi_Rating::SHOW_EMAIL_INPUT_OPTION],
			'show_comment_textarea' => $position_settings[MRP_Multi_Rating::SHOW_COMMENT_TEXTAREA_OPTION],
			'rating_form_id' => $rating_form_id,
			'class' => ''
	), $atts ) );
	
	if ( $post_id == null ) {
		return; // No post Id available
	}

	if ( is_string( $show_name_input ) ) {
		$show_name_input = $show_name_input == 'true' ? true : false;
	}
	if ( is_string( $show_email_input ) ) {
		$show_email_input = $show_email_input == 'true' ? true : false;
	}
	if ( is_string( $show_comment_textarea ) ) {
		$show_comment_textarea = $show_comment_textarea == 'true' ? true : false;
	}

	return MRP_Multi_Rating_API::display_rating_form( array(
			'rating_form_id' => $rating_form_id,
			'post_id' => $post_id,
			'title' => $title,
			'before_title' => $before_title,
			'after_title' => $after_title,
			'submit_button_text' => $submit_button_text,
			'update_button_text' => $update_button_text,
			'delete_button_text' => $delete_button_text,
			'show_name_input' => $show_name_input,
			'show_email_input' => $show_email_input,
			'show_comment_textarea' => $show_comment_textarea,
			'echo' => false,
			'class' => $class
	) );
}
add_shortcode( 'display_rating_form', 'mrp_display_rating_form' );


/**
 * Shortcode to display the rating result
 */
function mrp_display_rating_result( $atts = array() ) {
	
	if (is_admin() ) {
		return;
	}
	
	// get the post id
	global $post;
	
	$post_id = null;
	if ( isset( $post ) ) {
		$post_id = $post->ID;
	}
	
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
	// if a rating form is not specified in post meta, use default settings
	$rating_form_id = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_ID_POST_META, true );
	if ( $rating_form_id == '') {
		$rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
	}
	
	extract( shortcode_atts( array(
			'post_id' => $post_id,
			'no_rating_results_text' =>  $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'rating_form_id' => $rating_form_id,
			'show_rich_snippets' => false,
			'show_title' => false,
			'show_count' => true,
			'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
			'class' => ''
	), $atts ) );
	
	if ( $post_id == null ) {
		return; // No post Id available
	}
	
	if ( is_string( $show_rich_snippets ) ) {
		$show_rich_snippets = $show_rich_snippets == 'true' ? true : false;
	}
	if ( is_string( $show_title) ) {
		$show_title = $show_title == 'true' ? true : false;
	}
	if ( is_string( $show_count ) ) {
		$show_count = $show_count == 'true' ? true : false;
	}
	
	return MRP_Multi_Rating_API::display_rating_result( array(
			'post_id' => $post_id,
			'rating_form_id' => $rating_form_id,
			'no_rating_results_text' => $no_rating_results_text,
			'show_rich_snippets' => $show_rich_snippets,
			'show_title' => $show_title,
			'show_date' => false,
			'show_count' => $show_count,
			'echo' => false,
			'result_type' => $result_type,
			'class' => $class
	) );
}
add_shortcode( 'display_rating_result', 'mrp_display_rating_result' );


/**
 * Shortcode to display the rating item results
 */
function mrp_display_rating_item_results( $atts = array() ) {
	
	if (is_admin() ) {
		return;
	}
	
	// get the post id
	global $post;
	
	$post_id = null;
	if ( isset( $post ) ) {
		$post_id = $post->ID;
	}
	
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
	// if a rating form is not specified in post meta, use default settings
	$rating_form_id = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_ID_POST_META, true );
	if ( $rating_form_id == '') {
		$rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
	}
	
	extract( shortcode_atts( array(
			'post_id' => $post_id,
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'no_rating_results_text' =>  $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'rating_form_id' => $rating_form_id,
			'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
			'show_count' => true,
			'show_title' => true,
			'title' => $custom_text_settings[MRP_Multi_Rating::RATING_ITEM_RESULTS_TITLE_TEXT_OPTION],
			'class' => '',
			'preserve_max_option' => true,
			'show_options' => false
	), $atts ) );
	
	if ( $post_id == null ) {
		return; // No post Id available
	}
	
	if ( is_string( $show_count ) ) {
		$show_count = $show_count == 'true' ? true : false;
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
	
	return MRP_Multi_Rating_API::display_rating_item_results( array(
			'post_id' => $post_id,
			'before_title' => $before_title,
			'after_title' => $after_title,
			'no_rating_results_text' => $no_rating_results_text,
			'rating_form_id' => $rating_form_id,
			'result_type' => $result_type,
			'show_count' => $show_count,
			'title' => $title,
			'echo' => false,
			'show_title' => $show_title,
			'title' => $title,
			'class' => $class,
			'preserve_max_option' => $preserve_max_option,
			'show_options' => $show_options
	) );
	
}
add_shortcode( 'display_rating_item_results', 'mrp_display_rating_item_results' );


/**
 * Shortcode function for displaying the top rating results
 *
 * @param unknown_type $atts
 * @return string
 */
function mrp_display_top_rating_results( $atts = array() ) {
	
	if ( is_admin() ) {
		return;
	}
	
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
	extract( shortcode_atts( array(
			'title' => $custom_text_settings[MRP_Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'rating_form_id' =>  $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ],
			'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'show_count' => true,
			'show_category_filter' => true,
			'limit' => 10,
			'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
			'show_rank' => true,
			'show_title' => true,
			'class' => '',
			'category_id' => 0, // 0 = All,
	), $atts ) );
	
	if ( is_string( $show_category_filter ) ) {
		$show_category_filter = $show_category_filter == 'true' ? true : false;
	}
	if ( is_string( $show_count ) ) {
		$show_count = $show_count == 'true' ? true : false;
	}
	if ( is_string( $show_title ) ) {
		$show_title = $show_title == 'true' ? true : false;
	}
	
	return MRP_Multi_Rating_API::display_top_rating_results( array(
			'no_rating_results_text' => $no_rating_results_text,
			'show_count' => $show_count,
			'echo' => false,
			'title' => $title,
			'rating_form_id' => $rating_form_id,
			'show_category_filter' => $show_category_filter,
			'limit' => $limit,
			'result_type' => $result_type,
			'show_rank' => $show_rank,
			'show_title' => $show_title,
			'class' => $class,
			'category_id' => $category_id,
	) );
}
add_shortcode( 'display_top_rating_results', 'mrp_display_top_rating_results' );


/**
 * Shortcode function for displaying the top rating results
 *
 * @param unknown_type $atts
 * @return string
 */
function mrp_display_user_rating_results( $atts = array() ) {

	if (is_admin() ) {
		return;
	}

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
	global $wp_roles;
	$current_user = wp_get_current_user();
	$username = $current_user->user_login;

	extract( shortcode_atts( array(
			'title' => $custom_text_settings[MRP_Multi_Rating::USER_RATING_RESULTS_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'username' =>  $username,
			'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'show_category_filter' => true,
			'show_date' => true,
			'show_rank' => true,
			'before_date' => '(',
			'after_date' => ')',
			'category_id' => 0, // 0 = All,
			'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
			'limit' => 10,
			'class' => '',
			'show_count' => true,
			'show_title' => true
	), $atts ) );

	if ( is_string( $show_date ) ) {
		$show_date = $show_date == 'true' ? true : false;
	}
	if ( is_string( $show_category_filter ) ) {
		$show_category_filter = $show_category_filter == 'true' ? true : false;
	}
	if ( is_string( $show_rank ) ) {
		$show_rank = $show_rank == 'true' ? true : false;
	}

	return MRP_Multi_Rating_API::display_user_rating_results( array(
			'no_rating_results_text' => $no_rating_results_text,
			'show_date' => $show_date,
			'echo' => false,
			'title' => $title,
			'show_category_filter' => $show_category_filter,
			'before_title' => $before_title,
			'after_title' => $after_title,
			'username' => $username,
			'before_date' => $before_date,
			'after_date' => $after_date,
			'show_rank' => $show_rank,
			'category_id' => $category_id,
			'result_type' => $result_type,
			'limit' => $limit,
			'class' => $class,
			'show_count' => $show_count,
			'show_title' => $show_title
	) );
}
add_shortcode( 'display_user_rating_results', 'mrp_display_user_rating_results' );


/**
 * Shortcode to display rating result reviews
 * 
 * @param unknown_type $atts
 */
function mrp_display_rating_result_reviews( $params = array() ) {
	
	if (is_admin() ) {
		return;
	}
	
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
	// if a post has been set and a rating form is not specified in post meta, use default settings
	$rating_form_id = '';
	if ( isset( $params['post_id'] ) ) {
		$rating_form_id = get_post_meta( $params['post_id'], MRP_Multi_Rating::RATING_FORM_ID_POST_META, true );
	}
	if ( $rating_form_id == '') {
		$rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
	}
	
	extract( shortcode_atts( array(
			'post_id' => null,
			'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'rating_form_id' =>  $rating_form_id,
			'show_title' => true,
			'show_date' => true,
			'show_count' => true,
			'comments_only' => false,
			'title' => $custom_text_settings[MRP_Multi_Rating::RATING_RESULT_REVIEWS_TITLE_TEXT_OPTION],
			'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'before_name' => '- ',
			'after_name' => '',
			'before_comment' => '"',
			'after_comment' => '"',
			'show_name' => true,
			'show_comment' => true,
			'before_date' => '(',
			'after_date' => ')',
			'rating_item_entry_ids' => '',
			'limit' => 10,
			'show_indv_rating_item_results' => true,
			'all_posts' => false,
			'view_format' => MRP_Multi_Rating::INLINE_VIEW_FORMAT,
			'show_rank' => true,
			'category_id' => 0,
			'show_category_filter' => false,
			'show_view_more' => false,
			'result_type' => MRP_Multi_Rating::STAR_RATING_RESULT_TYPE,
			'class' => ''
	), $params ) );
	
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
	if ( is_string( $all_posts ) ) {
		$all_posts = $all_posts == 'true' ? true : false;
	}
	if ( is_string( $show_rank ) ) {
		$show_rank = $show_rank == 'true' ? true : false;
	}
	if ( is_string( $show_category_filter ) ) {
		$show_category_filter = $show_category_filter == 'true' ? true : false;
	}
	if ( is_string( $show_view_more ) ) {
		$show_view_more = $show_view_more == 'true' ? true : false;
	}
	
	if ( $all_posts == false && $post_id == null ) {
		global $post;
	
		if ( isset( $post ) ) {
			$post_id = $post->ID;
		}
	}

	return MRP_Multi_Rating_API::display_rating_result_reviews( array(
			'post_id' => $post_id,
			'no_rating_results_text' => $no_rating_results_text,
			'rating_form_id' =>  $rating_form_id,
			'show_title' => $show_title,
			'show_date' => $show_date,
			'show_count' => $show_count,
			'comments_only' => $comments_only,
			'echo' => false,
			'title' => $title,
			'result_type' => $result_type,
			'before_title' => $before_title,
			'after_title' => $after_title,
			'before_name' => $before_name,
			'after_name' => $after_name,
			'before_comment' => $before_comment,
			'after_comment' => $after_comment,
			'show_name' => $show_name,
			'show_comment' => $show_comment,
			'before_date' => $before_date,
			'after_date' => $after_date,
			'rating_item_entry_ids' => $rating_item_entry_ids,
			'limit' => $limit,
			'show_indv_rating_item_results' => $show_indv_rating_item_results,
			'all_posts' => $all_posts,
			'view_format' => $view_format,
			'show_rank' => $show_rank,
			'category_id' => $category_id,
			'show_category_filter' => $show_category_filter,
			'show_view_more' => $show_view_more,
			'result_type' => $result_type,
			'class' => $class
	) );
}
add_shortcode( 'display_rating_result_reviews', 'mrp_display_rating_result_reviews' );


function mrp_display_comment_rating_form( $atts = array() ) {
	
	if (is_admin() ) {
		return;
	}
	
	// get the post id
	global $post;
	
	$post_id = null;
	if (isset( $post ) ) {
		$post_id = $post->ID;
	}
	
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
	// FIXME this shortcode might need to be removed...
	// TODO somehow find a way to set the rating form using a shortcode attribute
	/* if a rating form is not specified in post meta, use default settings
	$rating_form_id = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_ID_POST_META, true );
	if ( $rating_form_id == '') {
		$rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
	}*/

	extract( shortcode_atts( array(
			'post_id' => $post_id,
			'title' => $custom_text_settings[MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION],
			'submit_button_text' => $custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION],
			//'rating_form_id' => $rating_form_id,
			'class' => ''
	), $atts ) );
	
	if ( $post_id == null ) {
		return; // No post Id available
	}

	return MRP_Multi_Rating_API::display_comment_rating_form( array(
			'post_id' => $post_id,
			'title' => $title,
			'submit_button_text' => $submit_button_text,
			//'rating_form_id' => $rating_form_id,
			'class' => $class,
			'echo' => false
	) );
}
add_shortcode( 'display_comment_rating_form' , 'mrp_display_comment_rating_form' );

?>