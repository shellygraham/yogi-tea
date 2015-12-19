<?php
/*
Plugin Name: Yogi - Functions
Plugin URI: http://www.nonlefthanded.com/plugins
Description: Putting all the functions here. If they are detached from the theme, we can change themes with little to no fear.
Author: CJ Stritzel
Version: 1.0
Author URI: http://www.nonlefthanded.com/plugins
*/


// Images are appearing in the blog as "src=/wp-content/," this adds the home_url().
function new_root_for_images($content) {
	$content = str_replace('src=/wp-content','src=' . get_home_url() . '/wp-content',$content);
	return $content;
}
add_action('the_content', 'new_root_for_images');


// Does post have an asterisk in it? Add disclaimer to the end of it.
// Disabled this because it added disclaimers to posts we don't want having one. Disclaimers should be set per-post using ACF.
/*function disclaimer_to_post($content) {
	global $post;
	$pos = strpos($post->post_content,'*');
	if ($pos > 0) {
		$content = $content . do_shortcode('[yogi_disclaimer]');
	}
	return $content;
}
add_action('the_content', 'disclaimer_to_post');*/


// Trying to get the "ingredients" rewrite. I.e. url: /ingredients/m/
// Doesn't work yet, still a bit mystified.
function yoursite_init() {
	add_rewrite_rule('^albrag\/([^/]*)\/','index.php?post_type=ingredient&first_letter=$matches[1]', 'bottom');
	//say($matches);
}
add_action('init','yoursite_init');

// Adding an "is $_GET['first_letter'] same as the first letter?" condition to the query.
function get_ingredient_by_first_letter($where) {
	global $wp_query;
	if ($wp_query->query_vars['post_type'] == 'ingredient' && $wp_query->is_post_type_archive == 1) {
		set_query_var('orderby', 'post_title');
		set_query_var('order', 'ASC');
		$first_letter = (isset($_GET['first_letter'])) ? sanitize_text_field(strtoupper($_GET['first_letter'])) : 'A'; // use default value here ''
		$where .= ' AND wp_posts.post_title LIKE "' . $first_letter . '%"';
	}
	return $where;
}
//add_filter( 'posts_where' , 'get_ingredient_by_first_letter' );


// Method to handle comment submission
function ajaxComment($comment_ID, $comment_status) { ?>

<script type="text/javascript">
  jQuery('document').ready(function($){
    // Get the comment form
    var commentform=$('#commentform');
    // Add a Comment Status message
    commentform.prepend('<div id="comment-status" ></div>');
    // Defining the Status message element 
    var statusdiv=$('#comment-status');
    commentform.submit(function(){
      // Serialize and store form data
      var formdata=commentform.serialize();
      //Add a status message
      statusdiv.html('<p>Processing...</p>');
      //Extract action URL from commentform
      var formurl=commentform.attr('action');
      //Post Form with data
      $.ajax({
        type: 'post',
        url: formurl,
        data: formdata,
        error: function(XMLHttpRequest, textStatus, errorThrown){
          statusdiv.html('<p class="ajax-error" >You might have left one of the fields blank, or be posting too quickly</p>');
        },
        success: function(data, textStatus){

            statusdiv.html('<p class="ajax-success" >Thanks for your comment. We appreciate your response.</p>');

        }
      });
      return false;
    });
  });
</script>




<?php
}
// Send all comment submissions through my "ajaxComment" method
add_action('wp_footer', 'ajaxComment');

// Helpers
function say($a, $name = '') {
	$name = (!$name) ? "" : "\n\n" . $name . "\n\n";
	echo '<pre>' . $name;
	print_r($a);
	echo '</pre>';
}
function get_featured_image_url($post_id, $size = false) {
	//return wp_get_attachment_url(get_post_thumbnail_id($post_id));
	return current(wp_get_attachment_image_src(get_post_thumbnail_id($post_id), $size));
}

function featured_image_url($post_id, $size = false) {
	echo get_featured_image_url($post_id, $size);
}

function get_title_no_break($title) {
	return str_replace("<br />"," ",$title);
}

function title_no_break($title) {
	echo get_title_no_break($title);
}

if ( function_exists( 'add_theme_support' ) ) { 
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'email_tea_pairs', 343 ); // 343 pixels wide (and unlimited height)
}

// Classes 
class IngredientsTeas {
    public function __construct() {
    	global $wpdb, $post;
    	$this->ingredient = $post->ID;
    	$this->query      = sprintf('select P.ID, P.post_title from %s as PM, %s as P where P.ID = PM.post_id and PM.meta_key = "ingredients" and PM.meta_value LIKE "%%\"%s\"%%" order by P.post_title asc', $wpdb->postmeta, $wpdb->posts, $this->ingredient);
    	$this->teas       = $wpdb->get_results($this->query);
    	for($i=0;$i<count($this->teas);$i++) {
    		$this->teas[$i]->permalink = get_permalink($this->teas[$i]->ID);
    	}
    }
}
?>
