<?php
/*
  Plugin Name: Custom Post Type - Ingredient
  Description: A plugin that allows you to have "Ingredient" post types.
  Author: CJ Stritzel
  Version: 1.0
 */

function ingredient_custom_init() {
  $labels = array(
    'name'               => 'Ingredients',
    'singular_name'      => 'Ingredient',
    'add_new'            => 'Add New',
    'add_new_item'       => 'Add New Ingredient',
    'edit_item'          => 'Edit Ingredient',
    'new_item'           => 'New Ingredient',
    'all_items'          => 'All Ingredients',
    'view_item'          => 'View Ingredient',
    'search_items'       => 'Search Ingredients',
    'not_found'          => 'No Ingredients found',
    'not_found_in_trash' => 'No Ingredients found in Trash',
    'parent_item_colon'  => '',
    'menu_name'          => 'Ingredients'
  );

  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'rewrite'            => array( 'slug' => 'ingredients' ),
    'capability_type'    => 'post',
    'has_archive'        => true,
    'hierarchical'       => false,
    'menu_position'      => null,
    'taxonomies' => array('category',),
    'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
  );

  register_post_type( 'ingredient', $args );
}
add_action( 'init', 'ingredient_custom_init' );

add_filter("posts_orderby", "my_orderby_filter", 10, 2);

function my_orderby_filter($orderby, &$query){
	global $wpdb;
	//figure out whether you want to change the order
	if (get_query_var("post_type") == "ingredient") {
		return "$wpdb->posts.post_title ASC";
	}
	return $orderby;
}
function super_rewrite() {
	//global $wbdb;
	//add_rewrite_tag('%ingredients%','([^&]+)');
	//echo $wp_query->query_vars['ingredients'];
	//add_rewrite_rule('^/ingredients/page/([^/]*)/?','index.php?s=$matches[1]','top');
}
add_action( 'init', 'super_rewrite' );
?>