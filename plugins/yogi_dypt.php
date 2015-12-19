<?php
/*
  Plugin Name: Yogi - Discover Your Perfect Tea
  Description: All the programming needed for the Yogi "Discover Your Perfect Tea" page.
  Author: Pop Art
  Version: 1.0
 */

function yogi_dypt_func() {
	return yogi_dypt_json() . yogi_dypt_html();
}

function yogi_dypt_json() {
	global $wpdb;
	
	// Set up the empty one...
	$_ = (object)array(
		'criteria' => (object)array(
			'Flavors' => (object)array(),
			'Purpose' => (object)array()
		),
		'teas' => new stdClass()
	);

	foreach (get_posts("post_type=product&posts_per_page=10000&orderby=title&order=ASC") as $p) {
		$meta_values = get_post_meta($p->ID);
		$flavors  = unserialize($meta_values['flavors'][0]);
		$purposes = unserialize($meta_values['purpose'][0]);
		$_->teas->{$p->ID} = (object)array(
			'title'       => $p->post_title,
			'tagline'     => $meta_values['claim'][0],
			'blurb'       => $p->post_excerpt,
			'slug'        => $p->post_name,
			'buy_url'     => $meta_values['buy_now_url'][0],
			'product_url' => get_permalink($p->ID),
			'thumbnail'   => current(wp_get_attachment_image_src( get_post_thumbnail_id( $p->ID ) ) ),
			'fullsize'    => current(wp_get_attachment_image_src( get_post_thumbnail_id( $p->ID ), 'large' ) )
		);
		foreach ($flavors as $flavor) {
			$_->criteria->Flavors->{trim($flavor)}[] = $p->ID;
		}
		foreach ($purposes as $purpose) {
			$_->criteria->Purpose->{trim($purpose)}[] = $p->ID;
		}
	}
	// say($_); die;
	return sprintf('<script>dypt=%s;</script>', json_encode($_));
}

function yogi_dypt_html() {
	$txt = '
	<div class="dypt-top">
		<div class="dypt-head">
			<h1>Discover Your Perfect Tea</h1>
			<h3 class="caption">Over 100 exotic botanicals in 60<br>deliciously purposeful blends.</h3>
		</div>
	</div>
	<div class="dypt-selection">
		<div class="dypt-menu" id="Flavors"><a class="main-control">Select Flavor<img src="http://54.201.89.77/wp-content/uploads/2014/08/arrow-2.png"></a></div>
		<div class="dypt-menu" id="Purpose"><a class="main-control">Select Purpose<img src="http://54.201.89.77/wp-content/uploads/2014/08/arrow-2.png"></a></div>	
	</div>
	<div id="primary" class="dypt">
		<div id="dypt-count" class="clearfix" style="display:none;"></div>
    	<!-- "productwall," "start," and "content" added by js. -->
		<div id = "dypt-results"><img id="dypt-main" /></div>
		<div class="dypt-start">The same things that make Yogi teas delicious,<br>make them work.</div>
	</div>
	<script type="text/javascript" src="'.site_url().'/wp-content/themes/livewire/js/yogi_dypt.js" charset="utf-8"></script>

';
	echo $txt;

}

add_shortcode('yogi_dypt', 'yogi_dypt_func');

?>