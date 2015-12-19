<?php
/*
  Plugin Name: Custom Post Type - Promos
  Description: A plugin that allows you to have "Promo" post types.
  Author: CJ Stritzel
  Version: 1.0
 */

function promo_custom_init() {
  $labels = array(
    'name'               => 'Promos',
    'singular_name'      => 'Promo',
    'add_new'            => 'Add New',
    'add_new_item'       => 'Add New Promo',
    'edit_item'          => 'Edit Promo',
    'new_item'           => 'New Promo',
    'all_items'          => 'All Promos',
    'view_item'          => 'View Promo',
    'search_items'       => 'Search Promos',
    'not_found'          => 'No Promos found',
    'not_found_in_trash' => 'No Promos found in Trash',
    'parent_item_colon'  => '',
    'menu_name'          => 'Promos'
  );

  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'rewrite'            => array( 'slug' => 'promo' ),
    'capability_type'    => 'post',
    'has_archive'        => true,
    'hierarchical'       => false,
    'menu_position'      => null,
    'supports'           => array( 'title', 'editor', 'thumbnail','page-attributes' )
  );

  register_post_type( 'promo', $args );
}
add_action( 'init', 'promo_custom_init' );


// Now hook up the .js script for the footer.
function get_footer_items() {
    $args = array( 'posts_per_page' => 3, 'post_type'=> 'promo', 'orderby' => 'menu_order', 'order' => 'ASC');
	$promos = get_posts( $args );
	$_ = array();
	foreach($promos as $p) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id($p->ID), 'large');
		$_[] = (object)array(
			'title' => $p->post_title,
			'url'   => get_field('url',$p->ID),
			'img'   => (object)array(
				'src'    => $img[0],
				'width' => $img[1],
				'height'  => $img[2]
			)
		); 
	}
	?>
	
	<script>
		(function ($) {
			<?php printf("\n\t\t\ttouts = %s\n", json_encode($_)) ?>
			$('footer p').append('<br class="clear" />');
			for(i=0;i<touts.length;i++){
				img = '<div class="col-sm-4"><a href="' + touts[i].url + '" title="' + touts[i].title + '"><img src="' + touts[i].img.src + '" /></a></div>';
				$('footer p').append(img);
			}
			
		}(jQuery));	
	</script>
	
	<?php

}
add_action('wp_footer', 'get_footer_items');
?>