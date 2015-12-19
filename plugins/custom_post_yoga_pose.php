<?php
/*
  Plugin Name: Custom Post Type - Yoga Pose
  Description: A plugin that allows you to have "Product" post types.
  Author: CJ Stritzel
  Version: 1.0
 */

function yoga_pose_custom_init() {
  $labels = array(
    'name'               => 'Yoga Poses',
    'singular_name'      => 'Yoga Pose',
    'add_new'            => 'Add New',
    'add_new_item'       => 'Add New Yoga Pose',
    'edit_item'          => 'Edit Yoga Pose',
    'new_item'           => 'New Yoga Pose',
    'all_items'          => 'All Yoga Poses',
    'view_item'          => 'View Yoga Pose',
    'search_items'       => 'Search Yoga Poses',
    'not_found'          => 'No Yoga Poses found',
    'not_found_in_trash' => 'No Yoga Poses found in Trash',
    'parent_item_colon'  => '',
    'menu_name'          => 'Yoga Poses'
  );

  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'rewrite'            => array( 'slug' => 'yoga_post' ),
    'capability_type'    => 'post',
    'has_archive'        => true,
    'hierarchical'       => false,
    'menu_position'      => null,
    'supports'           => array( 'title', 'editor','thumbnail')
  );

  register_post_type( 'yoga_pose', $args );
}
add_action( 'init', 'yoga_pose_custom_init' );



function yoga_pose_cats() {
	$labels = array(
		'name'                       => _x( 'Pose Categories', 'taxonomy general name' ),
		'singular_name'              => _x( 'Pose Category', 'taxonomy singular name' ),
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
		'hierarchical'          => false,
		'labels'                => $labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'pose_category' ),
	);

	register_taxonomy( 'pose_category', 'yoga_pose', $args );
}
add_action( 'init', 'yoga_pose_cats' );
?>