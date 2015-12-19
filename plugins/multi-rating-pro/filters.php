<?php 

// TODO move into it's own class

/**
 * Filters comment_text() filter to show the rating results along with the comments
 * 
 * @param $comment_text
 * @param $comment
 * @return string
 */
function mrp_comment_text( $comment_text, $comment = null ) {
	
	if ( is_admin() || $comment == null ) {
		return $comment_text;
	}

	$comment_id = $comment->comment_ID;
	$post_id = $comment->comment_post_ID;
	
	$comment_text_multi_rating = get_post_meta( $post_id, MRP_Multi_Rating::COMMENT_TEXT_MULTI_RATING_POST_META, true );
	
	if ( $comment_text_multi_rating != "" ) {
		$comment_text_multi_rating = $comment_text_multi_rating == "true" ? true : false;
	} else {
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$comment_text_multi_rating = $general_settings[ MRP_Multi_Rating::COMMENT_TEXT_MULTI_RATING_OPTION ];
	}

	// only add if approved and is enabled to be displayed in comment_text
	if (/*$comment->comment_approved == '1' &&*/ $comment_text_multi_rating == true) {
		$html = MRP_Multi_Rating_API::get_comment_rating_result( array(
				'comment_id' => $comment_id,
				'echo' => false,
				'class' => 'comment'
		) );
		
		// add html before comment text
		$comment_text = $comment_text . '<div class="rating-item-result">' . $html . '</div>';
	}

	return $comment_text;
}

/**
 * Filters the_content() to perform auto placements of the rating form on a page or post
 *
 * @param $content
 * @return filtered content
 */
function mrp_filter_the_content($content) {

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	
	if ( ! in_the_loop() || is_admin() )
		return $content;
	
	// get the post id
	global $post;
	
	$post_id = null;
	if ( ! isset( $post_id ) && isset( $post ) ) {
		$post_id = $post->ID;
	} else if ( ! isset($post) && ! isset( $post_id ) ) {
		return; // No post id available to display rating form
	}
	
	// check if filter enabled for post type
	$post_types = $general_settings[ MRP_Multi_Rating::POST_TYPES_OPTION ];
	if ( ! isset($post_types ) ) {
		return $content;
	}
	if ( ! is_array( $post_types ) && is_string( $post_types ) ) {
		$post_types = array( $post_types );
	}
	$post_type = get_post_type( $post_id );
	if ( ! in_array( $post_type, $post_types ) ) {
		return $content;
	}
	
	$filter_settings = (array) get_option( MRP_Multi_Rating::FILTER_SETTINGS );
	
	// exclude home page
	if ( is_home() && $filter_settings[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] == true ) {
		return $content;
	}
	
	// exclude archive pages
	if ( is_archive() && $filter_settings[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] == true ) {
		return $content;
	}
	
	// check page url
	$temp_array = preg_split( '/[\r\n,]+/', $filter_settings[MRP_Multi_Rating::FILTERED_PAGE_URLS_OPTION], -1, PREG_SPLIT_NO_EMPTY );
	$filtered_page_urls = array();
	foreach ( $temp_array as $url ) {
		$url = trim( $url, '&#13;&#10;' ); // TODO make constant
		array_push( $filtered_page_urls, $url );
	}
	if ( ! MRP_Utils::check_filter( MRP_Utils::get_current_url(), $filtered_page_urls,	
			$filter_settings[ MRP_Multi_Rating::PAGE_URL_FILTER_TYPE_OPTION ] ) ) {
		return $content;
	}
	
	// if a rating form is not specified in post meta, use default settings
	$rating_form_id = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_ID_POST_META, true );
	if ( $rating_form_id == '' ) {
		$rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
	}
	
	$rating_form_position = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_POSITION_POST_META, true );
	if ( $rating_form_position == MRP_Multi_Rating::DO_NOT_SHOW ) {
		return $content;
	}
	
	$position_settings = (array) get_option( MRP_Multi_Rating::POSITION_SETTINGS );
	
	// use default rating form position
	if ( $rating_form_position == '' ) {
		$rating_form_position = $position_settings[MRP_Multi_Rating::RATING_FORM_POSITION_OPTION ];
	}
	
	// for posts
	if ( ! MRP_Utils::check_filter( $post_id, preg_split('/[,]+/', $filter_settings[MRP_Multi_Rating::FILTERED_POSTS_OPTION], -1, PREG_SPLIT_NO_EMPTY ), 
			$filter_settings[ MRP_Multi_Rating::POST_FILTER_TYPE_OPTION ])) {
		return $content;
	}
			
	// for categories
	$categories = wp_get_post_categories($post_id);
	if ( ! MRP_Utils::check_filter( $categories,preg_split('/[,]+/', $filter_settings[MRP_Multi_Rating::FILTERED_CATEGORIES_OPTION], -1, PREG_SPLIT_NO_EMPTY ), 
			$filter_settings[ MRP_Multi_Rating::CATEGORY_FILTER_TYPE_OPTION ])) {
		return $content;
	}
	
	$rating_form = null;
	if ( $rating_form_position == 'before_content' || $rating_form_position == 'after_content' ) {
		$rating_form = MRP_Multi_Rating_API::display_rating_form( array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'echo' => false,
				'class' => $rating_form_position
		) );
	}
	
	$filtered_content = '';

	if ( $rating_form_position == 'before_content' && $rating_form != null ) {
		$filtered_content .= $rating_form;
	}

	$filtered_content .= $content;

	if ( $rating_form_position == 'after_content' && $rating_form != null ) {
		$filtered_content .= $rating_form;
	}
	
	// only apply filter once.. hopefully, this is the post content...
	if ( in_the_loop() && ( is_single() || is_page() || is_attachment() ) ) {
		remove_filter( 'the_content', 'mrp_filter_the_content' );
	}
	
	return $filtered_content;
}
add_filter( 'the_content', 'mrp_filter_the_content' );



/**
 * Filters the_title() to perform auto placement of the rating results next to the page or post title
 *
 * @param $content
 * @return filtered content
 */
function mrp_filter_the_title( $title ) {
	
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

	if ( ! in_the_loop() || is_admin() ) {
		return $title;
	}

	// get the post id
	global $post;
	
	$post_id = null;
	if ( ! isset( $post_id ) && isset( $post ) ) {
		$post_id = $post->ID;
	} else if ( ! isset($post) && ! isset( $post_id ) ) {
		return; // No post id available to display rating form
	}
	
	// check if filter enabled for post type
	$post_types = $general_settings[ MRP_Multi_Rating::POST_TYPES_OPTION ];
	if ( ! isset( $post_types ) ) {
		return $title;
	}
	if ( ! is_array( $post_types ) && is_string( $post_types ) ) {
		$post_types = array( $post_types );
	}
	$post_type = get_post_type( $post_id );
	if ( ! in_array( $post_type, $post_types ) ) {
		return $title;
	}
	
	$filter_settings = (array) get_option( MRP_Multi_Rating::FILTER_SETTINGS );
	
	// exclude home
	if ( is_home() && $filter_settings[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] == true ) {
		return $title;
	}
	
	// exclude archive pages
	if ( is_archive() && $filter_settings[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] == true ) {
		return $title;
	}
	
	// check page url
	$temp_array = preg_split( '/[\r\n,]+/', $filter_settings[MRP_Multi_Rating::FILTERED_PAGE_URLS_OPTION], -1, PREG_SPLIT_NO_EMPTY );
	$filtered_page_urls = array();
	foreach ( $temp_array as $url ) {
		$url = trim( $url, '&#13;&#10;' );
		array_push( $filtered_page_urls, $url );
	}
	if ( ! MRP_Utils::check_filter( MRP_Utils::get_current_url(), $filtered_page_urls,	
			$filter_settings[ MRP_Multi_Rating::PAGE_URL_FILTER_TYPE_OPTION ] ) ) {
		return $title;
	}
	
	// if a rating form is not specified in post meta, use default settings
	$rating_form_id = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_ID_POST_META, true );
	if ( $rating_form_id == '' ) {
		$rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
	}
	
	$rating_results_position = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_RESULTS_POSITION_POST_META, true );
	if ( $rating_results_position == MRP_Multi_Rating::DO_NOT_SHOW ) {
		return $title;
	}
	
	$position_settings = (array) get_option( MRP_Multi_Rating::POSITION_SETTINGS );
	
	// use default rating results position
	if ( $rating_results_position == '' ) {
		$rating_results_position = $position_settings[MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION ];
	}

	// for posts
	if ( ! MRP_Utils::check_filter( $post_id, preg_split('/[,]+/', $filter_settings[MRP_Multi_Rating::FILTERED_POSTS_OPTION], -1, PREG_SPLIT_NO_EMPTY ),
			$filter_settings[ MRP_Multi_Rating::POST_FILTER_TYPE_OPTION ] ) ) {
		return $title;
	}
		
	// for categories
	$categories = wp_get_post_categories( $post_id );
	if ( ! MRP_Utils::check_filter( $categories, preg_split('/[,]+/', $filter_settings[MRP_Multi_Rating::FILTERED_CATEGORIES_OPTION], -1, PREG_SPLIT_NO_EMPTY ),
			$filter_settings[ MRP_Multi_Rating::CATEGORY_FILTER_TYPE_OPTION ] ) ) {
		return $title;
	}
	
	$rating_result = MRP_Multi_Rating_API::display_rating_result( array(
			'rating_form_id' => $rating_form_id,
			'post_id' => $post_id,
			'echo' => false,
			'show_date' => false,
			'show_rich_snippets' => $position_settings[MRP_Multi_Rating::GENERATE_RICH_SNIPPETS_OPTION],
			'class' => $rating_results_position
	) );

	$filtered_title = '';
	
	if ( $rating_results_position == '' ) {
		remove_filter( 'the_title', 'mrp_filter_the_title' );
		return $title;
	}

	if ( $rating_results_position == 'before_title' && $rating_result != null ) {
		$filtered_title .= $rating_result;
	}

	$filtered_title .= $title;

	if ( $rating_results_position == 'after_title' && $rating_result != null ) {
		$filtered_title .= $rating_result; 
	}
	
	// only apply filter once... hopefully, this is the post title...
	if ( in_the_loop() && ( is_single() || is_page() || is_attachment() ) ) {
		remove_filter( 'the_title', 'mrp_filter_the_title' );
	}

	return $filtered_title;
}
add_filter( 'the_title', 'mrp_filter_the_title' );
?>