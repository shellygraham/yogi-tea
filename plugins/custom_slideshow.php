<?php
/*
  Plugin Name: Custom - Slideshow JSON
  Description: A plugin that grabs all slideshow info and turns it to a JSON array.
  Author: CJ Stritzel
  Version: 1.0
 */

function get_slideshow_items() {
	$args = array(
    'meta_query' => array(
        array(
            'key' => 'slideshow_slide',
            'value' => '',
            'compare' => '!='
        ),
        array(
            'key' => 'slideshow_order',
            'value' => '0',
            'compare' => '>'
        )
    ),
    'meta_key' => 'slideshow_order',
    'order_by' => 'meta_value_num',
    'order' => 'ASC',
    'post_type' => 'product',
    'posts_per_page' => 999999
	);
	$posts = get_posts($args);
	$_ = array();
	foreach ($posts as $p) {
		$img = wp_get_attachment_image_src(current(get_post_meta($p->ID, 'slideshow_slide')), 'large');
		$_[] = (object)array(
			'title' => htmlentities($p->post_title),
			'url' => get_permalink($p->ID),
			'slide' => $img[0],
			'order' => current(get_post_meta($p->ID, 'slideshow_order')),
		);
	}
	echo "\n\n<script>\n";
	printf("slides = %s", json_encode($_));
	echo "\n</script>\n\n";
}

// This will have an "is this the home page?" conditional...
add_action('wp_footer', 'get_slideshow_items');
?>