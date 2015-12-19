<?php
/*
  Plugin Name: Yogi - Inspirations
  Description: All the programming needed for the Yogi Inspirations.
  Author: Pop Art
  Version: 1.0
 */

function yogi_inspirations_func() {
	// Make JSON array/object of all the "inspirations" (NOTE: as of 6-7-2014, there's weird characters in there. [ID:1067])
	return yogi_inspirations_json() . yogi_inspirations_scripts() . yogi_inspirations_html();
	
	// Fonts, js, whatever else to make the main functionality work
	
	
	
	// Deeplinking, changing the OG properties, etc. Can that be done via jQuery? Hmmmmm...
	
	// Sharing via FB
	
	// Sharing via email
	
	// Adding an Inspiration
	// http://codex.wordpress.org/Function_Reference/wp_new_comment 
	
}

function yogi_inspirations_html() {
	return '
	
	<div id="inspirations-holder">
		<div class="options"></div>
		<div class="prev"></div>
		<div id="yogi-quotes"><div class="quote"></div></div>
		<div class="next"></div>
		<div class="share">
			<div class="share-box">
				<span>Share the Inspiration</span>
				<a class="fa fa-facebook" href="#" target="_blank"></a>
				<a class="fa fa-twitter" href="#" target="_blank"></a>
				<a class="fa fa-pinterest" href="#" target="_blank"></a>
				<a class="fa fa-envelope" id="inspirations_email" data-toggle="modal" data-target="#send-inspiration"></a>
			</div>
			<a class="add special-btn" id="inspirations_add" data-toggle="modal" data-target="#add-inspiration">Add Your Own Inspiration</a>
		</div>
	</div>

	';
}
function yogi_inspirations_scripts() {
	return ('
	<script src="'.site_url().'/wp-content/themes/livewire/js/yogi_inspirations.js"></script>
	<script src="'.site_url().'/wp-content/themes/livewire/js/greensock/plugins/CSSPlugin.js"></script>
	<script src="'.site_url().'/wp-content/themes/livewire/js/greensock/easing/EasePack.js"></script>
	<script src="'.site_url().'/wp-content/themes/livewire/js/greensock/TweenLite.js"></script>
	<script src="'.site_url().'/wp-content/themes/livewire/js/greensock/utils/SplitText.js"></script>
      ');
}

function yogi_inspirations_json() {
	// "Inspirations" are comments on post #450
	// "Types" are hard-coded here (for convienience) and are stored in the $wpdb->comments table as "comment_author_url."
	//  Note that "types" are not used from the array, they are taken from the comments themselves.

	global $wpdb;
	
	$types = array('Personal-Growth','Community','Kindness','Wisdom','Joy','Perserverance','Inner-Strength');
	$q = "	SELECT * 
			FROM  `$wpdb->comments` 
			WHERE comment_author_url IN ('" . implode("','", $types) . "')
			AND comment_post_ID = 450
			AND comment_approved = 1
			order by comment_date DESC
	";

	$_ = new stdClass();
	$_->classes = array();
	$_->comments = new stdClass();
	foreach ($wpdb->get_results($q) as $k => $v) {
		$_->classes[] = $v->comment_author_url;
		if (!isset($_->comments->{$v->comment_author_url})) { $_->comments->{$v->comment_author_url} = array(); }

		$_->comments->{$v->comment_author_url}[] = (object)array(
			'comment_ID'           => $v->comment_ID,
			'comment_author'       => $v->comment_author,
			'comment_author_email' => $v->comment_author_email,
			'comment_date'         => $v->comment_date,
			'comment_content'      => $v->comment_content
		);

	}
	sort($_->classes);
	$_->classes = array_values(array_unique($_->classes));
	//say($_); die;
	return sprintf('<script>inspirations = %s</script>',json_encode($_));
}

add_shortcode('yogi_inspirations', 'yogi_inspirations_func');

?>