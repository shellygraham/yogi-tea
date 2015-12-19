<?php
/*
  Plugin Name: Custom Post Type - Tea
  Description: A plugin that allows you to have "Tea" post types.
  Author: CJ Stritzel
  Version: 1.0
 */

function tea_custom_init() {
  $labels = array(
    'name'               => 'Teas',
    'singular_name'      => 'Tea',
    'add_new'            => 'Add New',
    'add_new_item'       => 'Add New Tea',
    'edit_item'          => 'Edit Tea',
    'new_item'           => 'New Tea',
    'all_items'          => 'All Teas',
    'view_item'          => 'View Tea',
    'search_items'       => 'Search Teas',
    'not_found'          => 'No Teas found',
    'not_found_in_trash' => 'No Teas found in Trash',
    'parent_item_colon'  => '',
    'menu_name'          => 'Teas'
  );

  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'rewrite'            => array( 'slug' => 'teas' ),
    'capability_type'    => 'post',
    'has_archive'        => true,
    'hierarchical'       => false,
    'menu_position'      => null,
    'taxonomies'         => array( 'tea_category'),
    'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
  );

  register_post_type( 'tea', $args );
}
add_action( 'init', 'tea_custom_init' );

//add_filter('manage_posts_columns', 'tea_thumbnail_column');
//add_action('manage_posts_custom_column', 'teas_custom_columns', 2, 2);

function tea_thumbnail_column($columns) {
	$new = array();
	foreach($columns as $key => $title) {
		if ($key=='author') // Put the Thumbnail column before the Author column
			$new['thumbnail'] = 'Thumbnail';
			$new[$key] = $title;
	}
	return $new;
}

function teas_custom_columns($column_name, $id){
	if($column_name === 'thumbnail' ){
		//the_post_thumbnail( array(60,60) );
    }
}

function teas_cats() {
	$labels = array(
		'name'                       => _x( 'Tea Categories', 'taxonomy general name' ),
		'singular_name'              => _x( 'Tea Category', 'taxonomy singular name' ),
		'search_items'               => __( 'Search Categories' ),
		'popular_items'              => __( 'Popular Categories' ),
		'all_items'                  => __( 'All Categories' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Category' ),
		'update_item'                => __( 'Update Category' ),
		'add_new_item'               => __( 'Add New Category' ),
		'new_item_name'              => __( 'New Category Name' ),
		'separate_items_with_commas' => __( 'Separate categories with commas' ),
		'add_or_remove_items'        => __( 'Add or remove categories' ),
		'choose_from_most_used'      => __( 'Choose from the most used categories' ),
		'not_found'                  => __( 'No categories found.' ),
		'menu_name'                  => __( 'Categories' ),
	);

	$args = array(
		'hierarchical'          => true,
		'labels'                => $labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'has_archive' => 	true,
		'rewrite'               => array( 'slug' => 'teacats', 'with_front' => true ),
	);

	register_taxonomy( 'tea_category', 'tea', $args );
}
add_action( 'init', 'teas_cats' );



function yogi_tea_taxonomy_orderby_filter($orderby, &$query){
	global $wpdb;
	//figure out whether you want to change the order
	if (get_query_var("taxonomy") == "tea_category") {
		return "$wpdb->posts.post_title ASC";
	}
	return $orderby;
}
add_filter("posts_orderby", "yogi_tea_taxonomy_orderby_filter", 10, 2);
?>