<?php
/*
  Plugin Name: Yogi - Shortcodes
  Description: All the shortcodes needed for the Yogi site.
  Author: CJ Stritzel
  Version: 1.0
 */


$GLOBALS['_NOT_IN'] = array(); // Set array to avoid repeats on page.

function yogi_posts_func($atts){
	global $post, $_NOT_IN;
	$i = 0;
	$_['cat']   = (isset($atts['cat']))   ? $atts['cat']                     : '' ;
	$_['class'] = (isset($atts['class'])) ? $atts['class'] . ' ' . $_['cat'] : NULL ;
	$_['n']     = (isset($atts['per_page']))     ? $atts['per_page']         : 5 ;
	$args = array(
		'post_type' => $_['post_type'], 
		'numberposts' => $_['n'], 
		'category_name' => $_['cat'],
		'post__not_in' => $_NOT_IN
	);
	$posts = get_posts( $args );
	//$cat   = get_category_by_slug($_['cat']);
	$d = array(sprintf('<div class="%s">', $_['class']));
	//$d[] = sprintf('<h3><a href="">%s <span>&raquo;</span></a></h3>', $cat->name);
	foreach( $posts as $post ) {
		setup_postdata($post);
		$_NOT_IN[] = $post->ID;
		$d[] = sprintf('<h2><a href="%s" title="%s">%s</a></h2>', get_permalink(), wp_specialchars(get_the_title()), get_the_title());
		$i++;
	}
	$d[] = '</div>';
	return "\n" . implode("\n",$d) . "\n\n";
}

function yogi_fp_func($atts) {
	extract( shortcode_atts( 
		array( 
			'per_page'  => '',
			'class' => ''
		), 
		$atts 
	));
	wp_enqueue_script( 'front-page-facebook-linkify','http://benalman.com/code/javascript/ba-linkify.js');
	wp_enqueue_script( 'front-page-facebook', home_url() . '/js/facebook.js');
	return sprintf('<div id="facebook-posts" class="%s loading"></div>', $atts['class']);
}

function yogi_yoga_poses_func($atts) {
	// First, let's get all the yoga poses in a hash.
	$args = array(
		'post_type' => 'yoga_pose', 
		'numberposts' => 10000
	);
	$poses = get_posts( $args );
	$_ = new stdClass;
	foreach ($poses as $pose) {
		$pose->img = get_featured_image_url($pose->ID);
		$term = current(get_the_terms($pose->ID, 'pose_category')); // Only one per, but it comes in as an array, hence "current."
		if ($term->slug){
			$_->cats[$term->slug] = $term->name;
		}
		if (!isset($_->poses[$term->slug])){
			$_->poses[$term->slug] = array();
		}
		$_->poses[$term->slug][] = $pose;
	}
	// Convert that hash to JSON, and do the display...
	?>
	
	<script>
		(function ($) {
			poses = <?php echo json_encode($_); ?>;
 			$(document).ready(function() {
 				printMenu();
 			});
 			function printMenu() {
 				var listItems = '';
 				$.each(poses.cats, function(k,v){
 					listItems += '<li id="' + k + '" title="See more poses in the \'' + v + '\' category." class="col-sm-1 std-btn"><div>' + v + '</div></li>';
 				});
 				$('.poses_menu').append(listItems);
 				$('.poses_menu li').bind('click',function(){
 					switchPose($(this).attr('id'));
 				});
 				// Set the default.
 				for (var firstKey in poses.cats) break;
				switchPose(firstKey);
 			}
 			function switchPose(p){
 				var i = 0;
 				$('.poses_menu li').removeClass('selected');
 				$('.poses_menu li#' + p).addClass('selected');
 				$('#poses').fadeOut(450, function(){
 					$(this).html('');
 					var nugget = '',
 						indicators = '';
	 				$.each(poses.poses[p], function(k,v){
	 					var first = i === 0 ? 'active' : '';
	 					nugget += '<div class="item ' + first + '"><img src="' + v.img + '" class="left" /><b>' + v.post_title + '</b> ' + v.post_content + '</div>';
	 					indicators += '<li data-target="#posesCarousel" data-slide-to="' + i + '" class="' + first + '"></li>';
	 					i++;
	 				});
	 				$('#posesCarousel .carousel-indicators').html(indicators);
 					$('#poses').append(nugget).fadeIn(450);
 				});
 				$('a[data-slide="prev"]').click(function() {
				  $('#posesCarousel').carousel('prev');
				});

				$('a[data-slide="next"]').click(function() {
				  $('#posesCarousel').carousel('next');
				});
  			}
 		}(jQuery));
	</script>
	<?php return '<hr/>
	<ul class="poses_menu"></ul>
	<hr/>
    <div id="posesCarousel" class="carousel slide" data-ride="carousel" data-interval="false">
        <ol class="carousel-indicators"></ol>
		<div id="poses" class="col-sm-12 carousel-inner"></div>
	    <div class="carousel-arrow-holder">
		    <a class="left carousel-control" href="#posesCarousel" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>
		    <a class="right carousel-control" href="#posesCarousel" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>
		</div>
		<br class="clear" />
	</div>
	<br class="clear" />'; ?>
	<?php 
}

function yogi_disclaimer_func() {
	return '<p class="disclaimer"><strong>*</strong> <small>These statements have not been evaluated by the FDA. This product is not intended to diagnose, treat, cure, or prevent any disease.</small></p>';
}

function yogi_rainforest_alliance_func() {
	$img = '<img class="left size-full" src="/wp-content/uploads/2014/06/rainforest.png" alt="rainforest" />';
	$_ = '<a href="http://www.rainforest-alliance.org/" target="_blank">' . $img . '</a>';
	$_ .= 'Buying products with the Rainforest Alliance Certified&trade; seal of approval safeguards the rights and well-being of workers, conserves natural resources and protects wildlife and the environment.';
	$_ .= '<a href="http://www.rainforest-alliance.org/" target="_blank">www.rainforest-alliance.org</a>';
	return '<p class="rainforest-alliance">' . $_ . '</p>';
}

function yogi_organic_certified_func() {
	return '<p>This is the "ORGANICALLY CERTIFIED" text that gets printed out.</p>';
}

function yogi_ratings_func($atts) {
	global $wpdb, $post;
	if (!isset($atts['id'])) { $atts['id'] = $post->ID; }
	$q = sprintf('select count(*) as count,AVG(MRIEV.value) as avg from wp_mrp_rating_item_entry as MRIE, wp_mrp_rating_item_entry_value as MRIEV where MRIE.post_id = %s and MRIE.rating_item_entry_id = MRIEV.rating_item_entry_id', $atts['id']);
	$_ = $wpdb->get_results($q);
	$_[0]->round = round($_[0]->avg);
	$count = $_[0]->round;
	$stars = array();
	for ($i = 0; $i < 5; $i++) {
		if ($count >= 1) {
			$stars[] = '<i class="fa fa-star mrp-star-full"></i>';
		} else {
			if ($count > 0.75) $stars[] = '<i class="fa fa-star mrp-star-full"></i>';
			elseif ($count > 0.25) $stars[] = '<i class="fa fa-star mrp-star-half"></i>';
			else $stars[] = '<i class="fa fa-star mrp-star-empty"></i>';
		}
		--$count;
	}
	$stars = implode($stars);

	return sprintf('<label class="description">Rating</label><span class="rating-result"><span class="star-rating" style="color: #ffd700 !important;">%s</span><span class="star-result">%s/5</span></span>', $stars, round($_[0]->avg, 1));
}


add_shortcode('yogi_posts','yogi_posts_func');
add_shortcode('yogi_fb','yogi_fp_func');
add_shortcode('yogi_yoga_poses','yogi_yoga_poses_func');
add_shortcode('yogi_disclaimer','yogi_disclaimer_func');
add_shortcode('yogi_rainforest_alliance','yogi_rainforest_alliance_func');
add_shortcode('yogi_organic_certified','yogi_organic_certified_func');
add_shortcode('yogi_ratings', 'yogi_ratings_func');

// add_shortcode('yogi_inspirations', 'yogi_inspirations_func'); self-contained in "yogi_inspirations.php" plugin.
?>