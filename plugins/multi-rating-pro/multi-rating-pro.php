<?php 
/*
Plugin Name: Multi Rating Pro
Plugin URI: http://danielpowney.com/downloads/multi-rating-pro/
Description: Advanced version of WordPress.org Multi Rating plugin. 
Version: 1.4
Author: Daniel Powney
Author URI: http://danielpowney.com
License: GPL2
Text Domain: multi-rating-pro
*/

if ( ! defined('EDD_STORE_URL' ) ) {
	define( 'EDD_STORE_URL', 'http://danielpowney.com' );
}
if ( ! defined( 'MRP_PLUGIN_NAME' ) ) {
	define( 'MRP_PLUGIN_NAME', 'Multi Rating Pro' );
}

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'shortcodes.php';
require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'widgets.php';
require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'utils.php';
require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'multi-rating-api.php';
require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'rating-form-view.php';
require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'rating-result-view.php';
require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'filters.php';

if ( is_admin() ) {
	require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'update-check.php';
	require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'rating-item-table.php';
	require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'rating-form-table.php';
	require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'rating-item-entry-table.php';
	require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'rating-item-entry-value-table.php';
	require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'post-rating-results-table.php';
}

/**
 * MRP_Multi_Rating plugin class
 */
class MRP_Multi_Rating {

	// constants
	const
	VERSION = '1.4',
	ID = 'mrp_',
	
	// tables
	RATING_SUBJECT_TBL_NAME 					= 'mrp_rating_subject',
	RATING_ITEM_TBL_NAME 						= 'mrp_rating_item',
	RATING_ITEM_ENTRY_TBL_NAME					= 'mrp_rating_item_entry',
	RATING_ITEM_ENTRY_VALUE_TBL_NAME 			= 'mrp_rating_item_entry_value',
	RATING_FORM_TBL_NAME						= 'mrp_rating_form',
	
	// settings
	CUSTOM_TEXT_SETTINGS 						= 'mrp_custom_text_settings',
	STYLE_SETTINGS 								= 'mrp_style_settings',
	POSITION_SETTINGS 							= 'mrp_position_settings',
	GENERAL_SETTINGS 							= 'mrp_general_settings',
	FILTER_SETTINGS								= 'mrp_filter_settings',
	
	// options
	CUSTOM_CSS_OPTION 							= 'mrp_custom_css',
	RATING_RESULTS_POSITION_OPTION				= 'mrp_rating_results_position',
	RATING_FORM_POSITION_OPTION 				= 'mrp_rating_form',
	COMMENT_FORM_MULTI_RATING_OPTION 			= 'mrp_comment_form_multi_rating',
	COMMENT_TEXT_MULTI_RATING_OPTION 			= 'mrp_comment_text_multi_rating',
	CHAR_ENCODING_OPTION 						= 'mrp_char_encoding',
	RATING_FORM_TITLE_TEXT_OPTION 				= 'mrp_rating_form_title_text',
	TOP_RATING_RESULTS_TITLE_TEXT_OPTION 		= 'mrp_top_rating_results_title_text',
	USER_RATING_RESULTS_TITLE_TEXT_OPTION 		= 'mrp_user_rating_results_title_text',
	RATING_ITEM_RESULTS_TITLE_TEXT_OPTION		= 'mrp_rating_item_results_title_text',
	IP_ADDRESS_DATE_VALIDATION_OPTION			= 'mrp_ip_address_date_validation',
	POST_TYPES_OPTION							= 'mrp_post_types',
	SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION		= 'mrp_submit_rating_form_button_text',
	UPDATE_RATING_FORM_BUTTON_TEXT_OPTION		= 'mrp_update_rating_form_button_text',
	DELETE_RATING_FORM_BUTTON_TEXT_OPTION		= 'mrp_delete_rating_form_button_text',
	SUBMIT_RATING_SUCCESS_MESSAGE_OPTION 		= 'mrp_save_rating_success_message',
	UPDATE_RATING_SUCCESS_MESSAGE_OPTION		= 'mrp_update_rating_success_message',
	DELETE_RATING_SUCCESS_MESSAGE_OPTION		= 'mrp_delete_rating_success_message',
	DATE_VALIDATION_FAIL_MESSAGE_OPTION			= 'mrp_date_validation_fail_message',
	NO_RATING_RESULTS_TEXT_OPTION				= 'mrp_no_rating_results_text',
	SHOW_NAME_INPUT_OPTION						= 'mrp_show_name_input',
	SHOW_EMAIL_INPUT_OPTION						= 'mrp_show_email_input',
	SHOW_COMMENT_TEXTAREA_OPTION				= 'mrp_show_comment_textarea',
	VERSION_OPTION								= 'mrp_version_option',
	SHOW_RATING_RESULT_AFTER_SUBMIT_OPTION		= 'mrp_show_rating_form_after_submit',
	IP_ADDRESS_DATE_VALIDATION_DAYS_DURATION_OPTION	= 'mrp_ip_address_date_validation_days_duration',
	FILTERED_POSTS_OPTION						= 'filtered_posts',
	FILTERED_PAGE_URLS_OPTION					= 'filtered_page_urls',
	FILTERED_CATEGORIES_OPTION					= 'filtered_categories',
	POST_FILTER_TYPE_OPTION						= 'post_filter_type',
	FILTER_EXCLUDE_HOME_PAGE					= 'filter_exclude_home_page',
	FILTER_EXCLUDE_ARCHIVE_PAGES				= 'filter_exclude_archive_pages',
	PAGE_URL_FILTER_TYPE_OPTION					= 'page_url_filter_type',
	CATEGORY_FILTER_TYPE_OPTION					= 'category_filter_type',
	DEFAULT_RATING_FORM_OPTION					= 'default_rating_form',
	STAR_RATING_COLOUR_OPTION					= 'mrp_star_rating_colour',
	STAR_RATING_HOVER_COLOUR_OPTION				= 'mrp_star_rating_hover_colour',
	ALREADY_SUBMITTED_RATING_FORM_MESSAGE_OPTION	= 'username_submit_once_validation_fail_message',
	ALLOW_ANONYMOUS_RATINGS_OPTION				= 'allow_anonymous_ratings',
	ALLOW_ANONYMOUS_RATINGS_FAILURE_MESSAGE_OPTION = 'allow_anonymous_ratings_failure_message_option',
	SHOW_USER_RATINGFORM_ENTRIES_OPTION			= 'show_user_rating_form_entries',
	RATING_RESULT_REVIEWS_TITLE_TEXT_OPTION		= 'rating_result_reviews_title_text',
	GENERATE_RICH_SNIPPETS_OPTION				= 'generate_rich_snippets',
	DO_ACTIVATION_REDIRECT_OPTION				= 'mrp_do_activiation_redirect',
	INCLUDE_FONT_AWESOME_OPTION					= 'mrp_include_font_awesome',
	FONT_AWESOME_VERSION_OPTION					= 'mrp_font_awesome_version',
	
	// pages
	SETTINGS_PAGE_SLUG							= 'mrp_settings',
	RATING_ITEMS_PAGE_SLUG						= 'mrp_rating_items',
	RATING_RESULTS_PAGE_SLUG					= 'mrp_rating_results',
	ADD_NEW_RATING_ITEM_PAGE_SLUG				= 'mrp_add_new_rating_item',
	RATING_FORMS_PAGE_SLUG						= 'mrp_rating_forms',
	ADD_NEW_RATING_FORM_PAGE_SLUG				= 'mrp_add_new_rating_form',
	REPORTS_PAGE_SLUG							= 'mrp_reports',
	ABOUT_PAGE_SLUG								= 'mrp_about',
	
	// tabs
	RATING_RESULTS_TAB							= 'mrp_rating_results',
	RATING_RESULT_DETAILS_TAB					= 'mrp_rating_result_details',
	GENERAL_SETTINGS_TAB						= 'general_settings',
	POSITION_SETTINGS_TAB						= 'position_settings',
	CUSTOM_TEXT_SETTINGS_TAB					= 'custom_text_settings',
	STYLE_SETTINGS_TAB							= 'style_settings',
	DATABASE_SETTINGS_TAB						= 'database_settings',
	FILTER_SETTINGS_TAB							= 'filter_settings',
	POST_RATING_RESULTS_TAB						= 'post_summary',	
	LICENSE_SETTINGS_TAB						= 'license',
	
	// values
	TEXTAREA_NEWLINE 							= '&#13;&#10;',
	WHITELIST_VALUE								= 'whitelist',
	BLACKLIST_VALUE								= 'blacklist',
	SCORE_RESULT_TYPE							= 'score',
	STAR_RATING_RESULT_TYPE						= 'star_rating',
	PERCENTAGE_RESULT_TYPE						= 'percentage',
	DO_NOT_SHOW									= 'do_not_show',
	TABLE_VIEW_FORMAT							= 'table',
	INLINE_VIEW_FORMAT							= 'inline',
	SELECT_ELEMENT								= 'select',
	
	// post meta box
	RATING_FORM_ID_POST_META					= 'mrp_rating_form_id',
	RATING_FORM_POSITION_POST_META				= 'mrp_rating_form_position',
	RATING_RESULTS_POSITION_POST_META			= 'mrp_rating_results_position',
	ALLOW_ANONYMOUS_POST_META					= 'mrp_allow_anonymous',
	COMMENT_FORM_MULTI_RATING_POST_META			= 'mrp_comment_form_multi_rating',
	COMMENT_TEXT_MULTI_RATING_POST_META			= 'mrp_comment_text_multi_rating';
	
	public $custom_text_settings = array();
	public $style_settings = array();
	public $position_settings = array();
	public $general_settings = array();
	public $filter_settings = array();
	
	/**
	 * Checks for plugin updates in the WordPress admin
	 */
	function plugin_updater() {
	
		// retrieve our license key from the DB
		$license_key = trim( get_option( 'mrp_license_key' ) );
	
		// setup the updater
		$mrp_updater = new EDD_SL_Plugin_Updater( EDD_STORE_URL, __FILE__, array(
				'version' 	=> '1.4', 				// current version number
				'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
				'item_name' => MRP_PLUGIN_NAME, 	// name of this plugin
				'author' 	=> 'Daniel Powney'  	// author of this plugin
		) );
	
	}
	
	/**
	 * Activates the plugin
	 */
	public static function activate_plugin() {
		
		global $wpdb;
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		// subjects can be a post type
		$sql_create_rating_subject_tbl = 'CREATE TABLE ' . $wpdb->prefix.MRP_Multi_Rating::RATING_SUBJECT_TBL_NAME . ' (
				rating_id bigint(20) NOT NULL AUTO_INCREMENT,
				post_type varchar(20) NOT NULL,
				PRIMARY KEY  (rating_id)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta( $sql_create_rating_subject_tbl );
		
		$sql_create_rating_form_tbl = 'CREATE TABLE ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME . ' (
				rating_form_id bigint(20) NOT NULL AUTO_INCREMENT,
				name varchar(255) NOT NULL,
				rating_items varchar(255),
				PRIMARY KEY  (rating_form_id)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta( $sql_create_rating_form_tbl );
		
		// subjects are rated by multiple rating items
		$sql_create_rating_item_tbl = 'CREATE TABLE ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME . ' (
				rating_item_id bigint(20) NOT NULL AUTO_INCREMENT,
				rating_id bigint(20) NOT NULL,
				description varchar(255) NOT NULL,
				default_option_value int(11),
				max_option_value int(11),
				active tinyint(1) DEFAULT 1,
				weight double precision DEFAULT 1.0,
				option_value_text varchar(1000),
				include_zero tinyint(1) DEFAULT 1,
				type varchar(20) NOT NULL DEFAULT "select",
				PRIMARY KEY  (rating_item_id)
				) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta( $sql_create_rating_item_tbl );
		
		// rating item entries and results are saved
		$sql_create_rating_item_entry_tbl = 'CREATE TABLE ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' (
				rating_item_entry_id bigint(20) NOT NULL AUTO_INCREMENT,
				post_id bigint(20) NOT NULL,
				rating_form_id bigint(20) NOT NULL,
				entry_date datetime NOT NULL,
				ip_address varchar(100),
				name varchar(100),
				email varchar(255),
				comment varchar(1020),
				username varchar(50),
				comment_id bigint(20),
				PRIMARY KEY  (rating_item_entry_id)
				) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta( $sql_create_rating_item_entry_tbl );

		$sql_create_rating_item_entry_value_tbl = 'CREATE TABLE ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' (
				rating_item_entry_value_id bigint(20) NOT NULL AUTO_INCREMENT,
				rating_item_entry_id bigint(20) NOT NULL,
				rating_item_id bigint(20) NOT NULL,
				value int(11) NOT NULL,
				PRIMARY KEY  (rating_item_entry_value_id)
				) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta( $sql_create_rating_item_entry_value_tbl );
	}
	
	/**
	 * Uninstalls the plugin
	 */
	public static function uninstall_plugin() {
		
		// delete options
		delete_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		delete_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
		delete_option( MRP_Multi_Rating::POSITION_SETTINGS );
		delete_option( MRP_Multi_Rating::STYLE_SETTINGS );
		delete_option( MRP_Multi_Rating::FILTER_SETTINGS );
		
		// drop tables
		global $wpdb;
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . MRP_Multi_Rating::RATING_SUBJECT_TBL_NAME );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME );
	}
	
	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	function __construct() {
		
		// TODO move admin_inits and inits into separate function
		
		// TODO move license logic to license.php file
		add_action( 'admin_init', array(&$this, 'plugin_updater' ) );
		
		add_action( 'admin_init', array(&$this,'activate_license' ) );
		add_action( 'admin_init', array(&$this,'deactivate_license' ) );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		
		if( is_admin() ) {
			add_action( 'admin_menu', array($this, 'add_admin_menus') );
			add_action( 'admin_init', array($this, 'do_admin_actions') );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		}
		
		// TODO move all settings to separate settings.php file
		add_action('init', array( &$this, 'load_settings' ) );
		
		add_action( 'init', array( &$this, 'load_textdomain' ) );
		
		add_action( 'admin_init', array( &$this, 'register_custom_text_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_style_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_general_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_position_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_filter_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_license_settings' ) );
		
		add_action( 'admin_init', array( $this, 'redirect_about_page' ) );
		
		add_action('wp_head', array($this, 'add_custom_css'));
		
		add_action('init', array( &$this, 'comments_init' ) );
		
		// TODO move meta box to separate meta-box.php file
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post_meta' ) );

		$this->add_ajax_actions();
	}
	
	/**
	 * Redirects to about page on activation
	 */
	function redirect_about_page() {
		if ( get_option( MRP_MULTI_RATING::DO_ACTIVATION_REDIRECT_OPTION, false ) ) {
			delete_option( MRP_MULTI_RATING::DO_ACTIVATION_REDIRECT_OPTION );
			wp_redirect( 'admin.php?page=' . MRP_MULTI_RATING::ABOUT_PAGE_SLUG );
		}
	}
	
	/**
	 * Loads plugin text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'multi-rating-pro', false, dirname( plugin_basename( __FILE__) ) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR );
	}
	
	function comments_init() {
		
		// TODO move comments into separate comments.php file
		if ( is_user_logged_in() ) {
			add_action( 'comment_form_logged_in_after', array($this, 'comment_form_logged_in_after' ) );
		} else {
			add_filter( 'comment_form_default_fields', array( $this, 'comment_form_default_fields' ) );
		}
		
		add_action( 'wp_insert_comment', array( $this, 'comment_inserted' ), 10, 2 );
		add_filter( 'comment_text', 'mrp_comment_text', 10, 2 );
	}
	
	/**
	 * Returns whether multi rating is to be integrated with WP comment form
	 * 
	 * @return if WP comment form integration is enabled
	 */
	function is_comment_form_integration_enabled( $post_id ) {
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		
		// TODO move the  common checks to utils i.e. post type check, page url, posts, categories etc...
		
		// check if filter enabled for post type
		$post_types = $general_settings[ MRP_Multi_Rating::POST_TYPES_OPTION ];
		if ( ! isset( $post_types ) ) {
			return false;
		}
		if ( ! is_array( $post_types ) && is_string( $post_types ) ) {
			$post_types = array( $post_types );
		}
		$post_type = get_post_type( $post_id );
		if ( ! in_array( $post_type, $post_types ) ) {
			return false;
		}
		
		$filter_settings = (array) get_option( MRP_Multi_Rating::FILTER_SETTINGS );
		
		// check page url
		$temp_array = preg_split( '/[\r\n,]+/', $filter_settings[MRP_Multi_Rating::FILTERED_PAGE_URLS_OPTION], -1, PREG_SPLIT_NO_EMPTY );
		$filtered_page_urls = array();
		foreach ( $temp_array as $url ) {
			$url = trim( $url, '&#13;&#10;' );
			array_push( $filtered_page_urls, $url );
		}
		if ( ! MRP_Utils::check_filter( MRP_Utils::get_current_url(), $filtered_page_urls, $filter_settings[ MRP_Multi_Rating::PAGE_URL_FILTER_TYPE_OPTION ] ) ) {
			return false;
		}
		
		// for posts
		if ( ! MRP_Utils::check_filter( $post_id, preg_split('/[,]+/', $filter_settings[MRP_Multi_Rating::FILTERED_POSTS_OPTION], -1, PREG_SPLIT_NO_EMPTY ),
				$filter_settings[ MRP_Multi_Rating::POST_FILTER_TYPE_OPTION ])) {
			return false;
		}
		
		// for categories
		$categories = wp_get_post_categories( $post_id );
		if ( ! MRP_Utils::check_filter($categories,preg_split( '/[,]+/', $filter_settings[MRP_Multi_Rating::FILTERED_CATEGORIES_OPTION], -1, PREG_SPLIT_NO_EMPTY ),
				$filter_settings[ MRP_Multi_Rating::CATEGORY_FILTER_TYPE_OPTION ] ) ) {
			return false;
		}
		
		$comment_form_multi_rating = get_post_meta( $post_id, MRP_Multi_Rating::COMMENT_FORM_MULTI_RATING_POST_META, true );
		
		if ( $comment_form_multi_rating != '' ) {
			$comment_form_multi_rating = $comment_form_multi_rating == "true" ? true : false;
		} else {
			$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
			$comment_form_multi_rating = $general_settings[ MRP_Multi_Rating::COMMENT_FORM_MULTI_RATING_OPTION ];
		}
		
		if ( $comment_form_multi_rating != true ) {
			return false;
		}
		
		return true;
	}
	
	/** 
	 * Add rating items to the comment form when a user is logged in
	 * 
	 * @return unknown|string
	 */
	function comment_form_logged_in_after() {

		// get the post id
		global $post;
		
		$post_id = null;
		if ( ! isset( $post_id ) && isset( $post ) ) {
			$post_id = $post->ID;
		} else if ( ! isset( $post) && !isset( $post_id ) ) {
			return; // No post id available
		}
		
		if ( ! $this->is_comment_form_integration_enabled( $post_id ) ) {
			return;
		}
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		
		// if a rating form is not specified in post meta, use default settings
		$rating_form_id = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_ID_POST_META, true );
		if ( $rating_form_id == '' ) {
			$rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
		}
		
		$rating_items = MRP_Multi_Rating_API::get_rating_items( array(
				'rating_form_id' => $rating_form_id
		) );
		
		MRP_Rating_Form_View::$sequence++;
		
		$html = '';
		foreach ( $rating_items as $rating_item ) {
			$html .= MRP_Rating_Form_View::get_rating_item_field( $rating_item, $rating_form_id, $post_id, MRP_Rating_Form_View::$sequence );
		}
		
		// hidden field to identify the rating form
		$html .= '<input type="hidden" name="rating-form-id" value=' . $rating_form_id . '" />';
		$html .= '<input type="hidden" name="sequence" value="' . MRP_Rating_Form_View::$sequence . '" />';
		
		echo $html;
	}
	
	/**
	 * Adds the rating items to the comments form when a user is not logged in
	 * 
	 * @param $fields
	 * @return string
	 */
	function comment_form_default_fields( $fields ) {
		
		// get the post id
		global $post;
		
		$post_id = null;
		if ( ! isset( $post_id ) && isset( $post ) ) {
			$post_id = $post->ID;
		} else if ( ! isset($post) && ! isset( $post_id ) ) {
			return $fields; // No post id available
		}
		
		if ( ! $this->is_comment_form_integration_enabled( $post_id ) ) {
			return $fields;
		}
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

		// if a rating form is not specified in post meta, use default settings
		$rating_form_id = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_ID_POST_META, true );
		if ( $rating_form_id == '' ) {
			$rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
		}
		
		$rating_items = MRP_Multi_Rating_API::get_rating_items( array(
				'rating_form_id' => $rating_form_id
		) );

		MRP_Rating_Form_View::$sequence++;
		
		foreach ( $rating_items as $rating_item ) {
			$html = MRP_Rating_Form_View::get_rating_item_field( $rating_item, $rating_form_id, $post_id, MRP_Rating_Form_View::$sequence );
			$fields[$rating_item['rating_item_id']] = $html;
		}
		
		// hidden field to identify the rating form
		$fields['rating_form_id'] = '<input type="hidden" name="rating-form-id" value=' . $rating_form_id . '" />';
		$fields['sequence'] = '<input type="hidden" name="sequence" value="' . MRP_Rating_Form_View::$sequence . '" />';
		
		return $fields;
	}
	
	/**
	 * Save rating after comment has been inserted
	 * 
	 * @param $comment_id
	 * @param $comment_object
	 */
	function comment_inserted( $comment_id, $comment_object ) {
		
		if ( ! isset( $_POST['rating-form-id'] ) || ! isset( $_POST['comment_post_ID'] ) ) {
			return;
		}
		
		$post_id = $_POST['comment_post_ID'];
		$rating_form_id = $_POST['rating-form-id'];
		$sequence = $_POST['sequence'];

		$rating_items = MRP_Multi_Rating_API::get_rating_items( array(
				'rating_form_id' => $rating_form_id
		) );

		$ip_address = MRP_Utils::get_ip_address();	
		$entry_date_mysql = current_time('mysql');
		
		// get username
		global $wp_roles;
		$current_user = wp_get_current_user();
		$username = $current_user->user_login;
		
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'entry_date' => $entry_date_mysql,
				'ip_address' => $ip_address,
				'username' => $username,
				'comment_id' => $comment_id
		), array('%d', '%d', '%s', '%s', '%s', '%d') );
		
		$rating_item_entry_id = $wpdb->insert_id;
		
		foreach ( $rating_items as $rating_item ) {
			
			$rating_item_id = $rating_item['rating_item_id'];
			
			if ( isset( $_POST['rating-item-' . $rating_item_id . '-' . $sequence] ) ) {
				$rating_item_value = $_POST['rating-item-' . $rating_item_id . '-' . $sequence];
				
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
						'rating_item_entry_id' => $rating_item_entry_id,
						'rating_item_id' => $rating_item_id,
						'value' => $rating_item_value
				), array('%d', '%d', '%d') );
			}
		}
	}
	
	/**
	 * Adds the meta box container
	 */
	public function add_meta_box( $post_type ) {
		
		$post_types = $this->general_settings[MRP_Multi_Rating::POST_TYPES_OPTION];
		
		if ( ! is_array( $post_types ) && is_string( $post_types ) ) {
			$post_types = array( $post_types );
		}
		if ( $post_types != null && in_array( $post_type, $post_types ) ) {
			add_meta_box( 'mrp_meta_box', __('Multi Rating', 'multi-rating-pro' ), array( $this, 'display_meta_box_content' ), $post_type, 'side', 'high' );
		}
	}
	
	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_post_meta( $post_id ) {
			
		if ( ! isset( $_POST['mrp_meta_box_nonce_action'] ) ) {
			return $post_id;
		}

		if ( ! wp_verify_nonce( $_POST['mrp_meta_box_nonce_action'], 'mrp_meta_box_nonce' ) ) {
			return $post_id;
		}
	
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
	
		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
	
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		$rating_form_id = $_POST['rating-form-id'];
		$rating_form_position = $_POST['rating-form-position'];
		$rating_results_position = $_POST['rating-results-position'];
		
		$allow_anonymous = '';
		if ( $_POST['allow-anonymous'] == 'true') {
			$allow_anonymous = var_export(true, true);
		} else if ( $_POST['allow-anonymous'] == 'false' ) {
			$allow_anonymous = var_export( false, true );
		}
		
		$comment_form_multi_rating = '';
		if ( $_POST['comment-form-multi-rating'] == 'true' ) {
			$comment_form_multi_rating = var_export( true, true );
		} else if ( $_POST['comment-form-multi-rating'] == 'false' ) {
			$comment_form_multi_rating = var_export( false, true );
		}
		
		$comment_text_multi_rating = '';
		if ( $_POST['comment-text-multi-rating'] == 'true' ) {
			$comment_text_multi_rating = var_export( true, true );
		} else if ( $_POST['comment-text-multi-rating'] == 'false' ) {
			$comment_text_multi_rating = var_export( false, true );
		}	
		
		// Update the meta field.
		update_post_meta( $post_id, MRP_Multi_Rating::RATING_FORM_ID_POST_META, $rating_form_id );
		update_post_meta( $post_id, MRP_Multi_Rating::RATING_FORM_POSITION_POST_META, $rating_form_position );
		update_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POSITION_POST_META, $rating_results_position );
		update_post_meta( $post_id, MRP_Multi_Rating::ALLOW_ANONYMOUS_POST_META, $allow_anonymous );
		update_post_meta( $post_id, MRP_Multi_Rating::COMMENT_FORM_MULTI_RATING_POST_META, $comment_form_multi_rating );
		update_post_meta( $post_id, MRP_Multi_Rating::COMMENT_TEXT_MULTI_RATING_POST_META, $comment_text_multi_rating );
		
	}
	
	
	/**
	 * Displays the meta box content
	 *
	 * @param WP_Post $post The post object.
	 */
	public function display_meta_box_content( $post ) {
	
		wp_nonce_field( 'mrp_meta_box_nonce', 'mrp_meta_box_nonce_action' );
		
		$rating_form_id = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_ID_POST_META, true );
		$rating_form_position = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_POSITION_POST_META, true );
		$rating_results_position = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_RESULTS_POSITION_POST_META, true );
		$allow_anonymous = get_post_meta( $post->ID, MRP_Multi_Rating::ALLOW_ANONYMOUS_POST_META, true );
		$comment_form_multi_rating = get_post_meta( $post->ID, MRP_Multi_Rating::COMMENT_FORM_MULTI_RATING_POST_META, true );
		$comment_text_multi_rating = get_post_meta( $post->ID, MRP_Multi_Rating::COMMENT_TEXT_MULTI_RATING_POST_META, true );
		
		if ( $allow_anonymous != '' ) {
			$allow_anonymous = $allow_anonymous == 'true' ? 'true' : 'false';
		}
		if ( $comment_form_multi_rating != '' ) {
			$comment_form_multi_rating = $comment_form_multi_rating == 'true' ? 'true' : 'false';
		}
		if ( $comment_text_multi_rating != '' ) {
			$comment_text_multi_rating = $comment_text_multi_rating == 'true' ? 'true' : 'false';
		}
		?>
		
		<p>
			<label for="rating-form-id"><?php _e('Rating Form', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="rating-form-id" id="rating-form-id">
				<option value=""><?php _e( 'Use default settings', 'multi-rating-pro'); ?></option>
				<?php
				global $wpdb;
				$query = 'SELECT name, rating_form_id FROM ' . $wpdb->prefix .  MRP_Multi_Rating::RATING_FORM_TBL_NAME;
				$rows = $wpdb->get_results( $query, ARRAY_A );
					
				foreach ( $rows as $row ) {
					$selected = '';
					if ( intval($row['rating_form_id']) == intval( $rating_form_id ) ) {
						$selected = ' selected="selected"';
					}
				
					echo '<option value="' . $row['rating_form_id'] . '"' . $selected . '>' . $row['name'] . '</option>';
				}
				?>
			</select>
		</p>
		<p>
			<label for="rating-form-position"><?php _e( 'Rating form position', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="rating-form-position">
				<option value="<?php echo MRP_Multi_Rating::DO_NOT_SHOW; ?>" <?php selected('do_not_show', $rating_form_position, true );?>><?php _e( 'Do not show', 'multi-rating-pro' ); ?></option>
				<option value="" <?php selected('', $rating_form_position, true );?>><?php _e( 'Use default settings', 'multi-rating-pro' ); ?></option>
				<option value="before_content" <?php selected('before_content', $rating_form_position, true );?>><?php _e( 'Before content', 'multi-rating-pro' ); ?></option>
				<option value="after_content" <?php selected('after_content', $rating_form_position, true );?>><?php _e( 'After content', 'multi-rating-pro' ); ?></option>
			</select>
		</p>
		<p>
			<label for="rating-results-position"><?php _e( 'Rating result position', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="rating-results-position">
				<option value="<?php echo MRP_Multi_Rating::DO_NOT_SHOW; ?>" <?php selected('do_not_show', $rating_results_position, true );?>><?php _e( 'Do not show', 'multi-rating-pro' ); ?></option>
				<option value="" <?php selected('', $rating_results_position, true );?>><?php _e( 'Use default settings', 'multi-rating-pro' ); ?></option>
				<option value="before_title" <?php selected('before_title', $rating_results_position, true );?>><?php _e( 'Before title', 'multi-rating-pro' ); ?></option>
				<option value="after_title" <?php selected('after_title', $rating_results_position, true );?>><?php _e( 'After title', 'multi-rating-pro' ); ?></option>
			</select>
		</p>
		<p>
			<label for="allow-anonymous"><?php _e( 'Allow anonymous ratings', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="allow-anonymous">
				<option value="" <?php selected('', $allow_anonymous, true );?>><?php _e( 'Use default settings', 'multi-rating-pro' ); ?></option>
				<option value="true" <?php selected('true', $allow_anonymous, true );?>><?php _e( 'Yes', 'multi-rating-pro' ); ?></option>
				<option value="false" <?php selected('false', $allow_anonymous, true );?>><?php _e( 'No', 'multi-rating-pro' ); ?></option>
			</select>
		</p>
		<p>
			<label for="comment-form-multi-rating"><?php _e( 'Enable ratings to be added to the WP comment form', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="comment-form-multi-rating">
				<option value="" <?php selected('', $comment_form_multi_rating, true );?>><?php _e( 'Use default settings', 'multi-rating-pro' ); ?></option>
				<option value="true" <?php selected('true', $comment_form_multi_rating, true );?>><?php _e( 'Yes', 'multi-rating-pro' ); ?></option>
				<option value="false" <?php selected('false', $comment_form_multi_rating, true );?>><?php _e( 'No', 'multi-rating-pro' ); ?></option>
			</select>
		</p>
		<p>
			<label for="comment-text-multi-rating"><?php _e( 'Enable ratings to be displayed with WP comments', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="comment-text-multi-rating">
				<option value="" <?php selected('', $comment_text_multi_rating, true );?>><?php _e( 'Use default settings', 'multi-rating-pro' ); ?></option>
				<option value="true" <?php selected('true', $comment_text_multi_rating, true );?>><?php _e( 'Yes', 'multi-rating-pro' ); ?></option>
				<option value="false" <?php selected('false', $comment_text_multi_rating, true );?>><?php _e( 'No', 'multi-rating-pro' ); ?></option>
			</select>
		</p>
		
		<?php
	}
	
	/**
	 * Retrieve settings from DB and sets default options if not set
	 */
	function load_settings() {
		$this->style_settings = (array) get_option( MRP_Multi_Rating::STYLE_SETTINGS );
		$this->custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
		$this->position_settings = (array) get_option( MRP_Multi_Rating::POSITION_SETTINGS );
		$this->general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$this->filter_settings = (array) get_option( MRP_Multi_Rating::FILTER_SETTINGS );
		
		$default_css =  '';
		
		// Merge with defaults
		$this->style_settings = array_merge( array(
				MRP_Multi_Rating::CUSTOM_CSS_OPTION => $default_css,
				MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION => '#ffd700',
				MRP_Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION => '#ffba00',
				MRP_Multi_Rating::INCLUDE_FONT_AWESOME_OPTION => true,
				MRP_Multi_Rating::FONT_AWESOME_VERSION_OPTION => '4.0.3'
		), $this->style_settings );
		
		$this->position_settings = array_merge( array(
				MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION => '',
				MRP_Multi_Rating::RATING_FORM_POSITION_OPTION => '',
				MRP_Multi_Rating::SHOW_NAME_INPUT_OPTION => false,
				MRP_Multi_Rating::SHOW_EMAIL_INPUT_OPTION => false,
				MRP_Multi_Rating::SHOW_COMMENT_TEXTAREA_OPTION => false,
				MRP_Multi_Rating::GENERATE_RICH_SNIPPETS_OPTION => true
		), $this->position_settings );
		
		$this->custom_text_settings = array_merge( array(
				MRP_Multi_Rating::CHAR_ENCODING_OPTION => '',
				MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION 			=> __( 'Please rate this', 'multi-rating-pro' ),
				MRP_Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION 		=> __( 'Top Rating Results', 'multi-rating-pro' ),
				MRP_Multi_Rating::USER_RATING_RESULTS_TITLE_TEXT_OPTION 	=> __( 'User Rating Results', 'multi-rating-pro' ),
				MRP_Multi_Rating::RATING_RESULT_REVIEWS_TITLE_TEXT_OPTION 	=> __( 'Rating Result Reviews', 'multi-rating-pro' ),
				MRP_Multi_Rating::RATING_ITEM_RESULTS_TITLE_TEXT_OPTION 	=> __( 'Rating Item Results', 'multi-rating-pro' ),
				MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION 	=> __( 'Submit Rating', 'multi-rating-pro' ),
				MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION 	=> __( 'Update Rating', 'multi-rating-pro' ),
				MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION	 	=> __( 'Delete Rating', 'multi-rating-pro' ),
				MRP_Multi_Rating::SUBMIT_RATING_SUCCESS_MESSAGE_OPTION 		=> __( 'Rating form has been successfully submitted. ', 'multi-rating-pro' ),
				MRP_Multi_Rating::UPDATE_RATING_SUCCESS_MESSAGE_OPTION 		=> __( 'Rating form has been successfully updated. ', 'multi-rating-pro' ),
				MRP_Multi_Rating::DELETE_RATING_SUCCESS_MESSAGE_OPTION 		=> __( 'Rating form has been successfully deleted. ', 'multi-rating-pro' ),
				MRP_Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION 		=> __( 'You cannot submit a rating form for the same post multiple times.', 'multi-rating-pro' ),
				MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION 			=> __( 'No rating results yet', 'multi-rating-pro' ),
				MRP_Multi_Rating::ALREADY_SUBMITTED_RATING_FORM_MESSAGE_OPTION => __( 'You have already submitted this rating form.', 'multi-rating-pro' ),
				MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_FAILURE_MESSAGE_OPTION => __( 'You must be logged in to submit a rating form.', 'multi-rating-pro' )	
			), $this->custom_text_settings );
		
		// check default rating form is set
		$default_rating_form_id = null;
		global $wpdb;
		if ( isset( $this->general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION] ) ) {
			$default_rating_form_id = $this->general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
			
			$sql_select_default_rating_form = 'SELECT COUNT(rating_form_id) FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME 
					. ' WHERE rating_form_id = "' . $default_rating_form_id . '"';
			$count_rating_forms = $wpdb->get_var( $sql_select_default_rating_form );
			
			if ( $count_rating_forms == 0 ) {
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME, array( 'name' => __( 'Default Rating Form', 'multi-rating-pro' ), 'rating_items' => '') );
				$default_rating_form_id = $wpdb->insert_id;
			}
		} else {
			$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME, array( 'name' => __( 'Default Rating Form', 'multi-rating-pro' ), 'rating_items' => '') );
			$default_rating_form_id = $wpdb->insert_id;
		}
		
		$this->general_settings = array_merge( array(
				MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION => true,
				MRP_Multi_Rating::POST_TYPES_OPTION => 'post',
				MRP_Multi_Rating::SHOW_RATING_RESULT_AFTER_SUBMIT_OPTION => true,
				MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_DAYS_DURATION_OPTION => 1,
				MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION => $default_rating_form_id,
				MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION => true,
				MRP_Multi_Rating::COMMENT_FORM_MULTI_RATING_OPTION => false,
				MRP_Multi_Rating::COMMENT_TEXT_MULTI_RATING_OPTION => false
		), $this->general_settings );
		
		$this->filter_settings = array_merge( array(
				MRP_Multi_Rating::FILTERED_POSTS_OPTION => '',
				MRP_Multi_Rating::FILTERED_CATEGORIES_OPTION => '',
				MRP_Multi_Rating::FILTERED_PAGE_URLS_OPTION => '',
				MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES => true,
				MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE => true,
				MRP_Multi_Rating::POST_FILTER_TYPE_OPTION => MRP_Multi_Rating::BLACKLIST_VALUE,
				MRP_Multi_Rating::CATEGORY_FILTER_TYPE_OPTION => MRP_Multi_Rating::BLACKLIST_VALUE,
				MRP_Multi_Rating::PAGE_URL_FILTER_TYPE_OPTION => MRP_Multi_Rating::BLACKLIST_VALUE,
		), $this->filter_settings );
		
		update_option( MRP_Multi_Rating::STYLE_SETTINGS, $this->style_settings);
		update_option( MRP_Multi_Rating::POSITION_SETTINGS, $this->position_settings);
		update_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, $this->custom_text_settings);
		update_option( MRP_Multi_Rating::GENERAL_SETTINGS, $this->general_settings);
		update_option( MRP_Multi_Rating::FILTER_SETTINGS, $this->filter_settings);
	}
	
	/**
	 * License settings
	 */
	function register_license_settings() {
		register_setting('mrp_license', 'mrp_license_key', array(&$this, 'sanitize_license') );
	}
	
	/**
	 * General settings
	 */
	function register_general_settings() {
		register_setting( MRP_Multi_Rating::GENERAL_SETTINGS, MRP_Multi_Rating::GENERAL_SETTINGS, array( &$this, 'sanitize_general_settings' ) );
	
		add_settings_section( 'section_general', __( 'General', 'multi-rating-pro' ), array( &$this, 'section_general_desc' ), MRP_Multi_Rating::GENERAL_SETTINGS );
		add_settings_section( 'section_comments', __( 'Comments', 'multi-rating-pro' ), array( &$this, 'section_comments_desc' ), MRP_Multi_Rating::GENERAL_SETTINGS );
		add_settings_section( 'section_validation', __( 'Validation', 'multi-rating-pro' ), array( &$this, 'section_validation_desc' ), MRP_Multi_Rating::GENERAL_SETTINGS );
	
		add_settings_field( MRP_Multi_Rating::POST_TYPES_OPTION, __( 'Post Types', 'multi-rating-pro' ), array( &$this, 'field_post_types' ), MRP_Multi_Rating::GENERAL_SETTINGS, 'section_general' );
		add_settings_field( MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION, __( 'Default Rating Form', 'multi-rating-pro' ), array( &$this, 'field_default_rating_form' ), MRP_Multi_Rating::GENERAL_SETTINGS, 'section_general' );
		add_settings_field( MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION, __( 'Allow anonymous ratings', 'multi-rating-pro' ), array( &$this, 'field_allow_anonymous_ratings' ), MRP_Multi_Rating::GENERAL_SETTINGS, 'section_general' );
		
		add_settings_field( MRP_Multi_Rating::COMMENT_FORM_MULTI_RATING_OPTION, __( 'Enable ratings to be added to the WP comment form', 'multi-rating-pro' ), array( &$this, 'field_comment_form_multi_rating' ), MRP_Multi_Rating::GENERAL_SETTINGS, 'section_comments' );
		add_settings_field( MRP_Multi_Rating::COMMENT_TEXT_MULTI_RATING_OPTION, __( 'Enable ratings to be displayed with WP comments', 'multi-rating-pro' ), array( &$this, 'field_comment_text_multi_rating' ), MRP_Multi_Rating::GENERAL_SETTINGS, 'section_comments' );
		
		add_settings_field( MRP_Multi_Rating::SHOW_RATING_RESULT_AFTER_SUBMIT_OPTION, __( 'Show rating result after rating form submit', 'multi-rating-pro' ), array( &$this, 'field_show_rating_result_after_submit' ), MRP_Multi_Rating::GENERAL_SETTINGS, 'section_general' );
		add_settings_field( MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION, __( 'Rating form IP address & date validation', 'multi-rating-pro' ), array( &$this, 'field_ip_address_date_validation' ), MRP_Multi_Rating::GENERAL_SETTINGS, 'section_validation' );
		
		// TODO remove this
		add_settings_field( MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_DAYS_DURATION_OPTION, __( 'Rating form IP address & date validation days duration', 'multi-rating-pro' ), array( &$this, 'field_ip_address_date_validation_days_duration' ), MRP_Multi_Rating::GENERAL_SETTINGS, 'section_validation' );
	}
	
	/**
	 * General section description
	 */
	function section_general_desc() {
		echo '<p>' . __( 'General settings.', 'multi-rating-pro' ) . '</p>';
	}
	/**
	 * Comments section description
	 */
	function section_comments_desc() {
		echo '<p>' . __( 'Settings to integrate with WordPress comments system.', 'multi-rating-pro' ) . '</p>';
	}
	/**
	 * Validation section description
	 */
	function section_validation_desc() {
		echo '<p>' . __( 'Additional rating form validation checks.', 'multi-rating-pro' ) . '</p>';
	}
	/**
	 * IP address & date validation check
	 */
	function field_ip_address_date_validation() {
		?>
		<input type="checkbox" name="<?php echo MRP_Multi_Rating::GENERAL_SETTINGS;?>[<?php echo MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION; ?>]" value="true" <?php checked(true, $this->general_settings[MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION], true); ?> />
		<p class="description"><?php _e( 'Restrict the same IP address from submitting the same rating form for the same post multiple times.', 'multi-rating-pro' ); ?></p>
		<?php 
	}
	/**
	 * Allow anonymous ratings
	 */
	function field_allow_anonymous_ratings() {
		?>
		<input type="checkbox" name="<?php echo MRP_Multi_Rating::GENERAL_SETTINGS;?>[<?php echo MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION; ?>]" value="true" <?php checked(true, $this->general_settings[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION], true); ?> />
		<p class="description"><?php _e( 'Allow anonymous users to submit ratings. If this is not checked, only logged in users will be allowed to submit ratings.', 'multi-rating-pro' ); ?></p>
		<?php 
	}
	/**
	 * WP comments rating form integration
	 */
	function field_comment_form_multi_rating() {
		?>
		<input type="checkbox" name="<?php echo MRP_Multi_Rating::GENERAL_SETTINGS;?>[<?php echo MRP_Multi_Rating::COMMENT_FORM_MULTI_RATING_OPTION; ?>]" value="true" <?php checked(true, $this->general_settings[MRP_Multi_Rating::COMMENT_FORM_MULTI_RATING_OPTION], true); ?> />
		<p class="description"><?php printf( __('Enable ratings to be added to the WP comment form. See %s and %s.', 'multi-rating-pro' ), ' <a href="http://codex.wordpress.org/Plugin_API/Filter_Reference/comment_form_default_fields">comment_form_default_fields()</a>', '<a href="http://codex.wordpress.org/Function_Reference/comment_form">comment_form()</a>' ); ?></p>
		<?php 
	}
	/**
	 * WP comments list integration
	 */
	function field_comment_text_multi_rating() {
		?>
		<input type="checkbox" name="<?php echo MRP_Multi_Rating::GENERAL_SETTINGS;?>[<?php echo MRP_Multi_Rating::COMMENT_TEXT_MULTI_RATING_OPTION; ?>]" value="true" <?php checked(true, $this->general_settings[MRP_Multi_Rating::COMMENT_TEXT_MULTI_RATING_OPTION], true); ?> />
		<p class="description"><?php printf( __( 'Enable ratings to be displayed with WP comments. See %s.', 'multi-rating-pro' ), '<a href="http://codex.wordpress.org/Function_Reference/comment_text">comment_text()</a>' ); ?></p>
		<?php 
	}
	/**
	 * TODO remove
	 */
	function field_ip_address_date_validation_days_duration() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::GENERAL_SETTINGS; ?>[<?php echo MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_DAYS_DURATION_OPTION; ?>]" value="<?php echo $this->general_settings[MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_DAYS_DURATION_OPTION]; ?>" />
		<p class="description"><?php _e( 'Set the duration of days for the IP address & date validation check.', 'multi-rating-pro' ); ?></p>
		<?php	
	}
	/**
	 * Default rating form
	 */
	function field_default_rating_form() {
		$rating_form_id = $this->general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
		?>
		<select name="<?php echo MRP_Multi_Rating::GENERAL_SETTINGS; ?>[<?php echo MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION; ?>]">
			<?php 
			global $wpdb;
			
			$query = 'SELECT name, rating_form_id FROM ' . $wpdb->prefix .  MRP_Multi_Rating::RATING_FORM_TBL_NAME;
			$rows = $wpdb->get_results( $query, ARRAY_A );
			
			foreach ( $rows as $row ) {
				$selected = '';
				
				if ( intval( $row['rating_form_id'] ) == intval( $rating_form_id ) ) {
					$selected = ' selected="selected"';
				}
				
				echo '<option value="' . $row['rating_form_id'] . '"' . $selected . '>' . $row['name'] . '</option>';
			}
			?>
		</select>
		<p class="description">Enter a Rating Form Id for the default rating form to use.</p>
		<?php
	}
	/**
	 * Post types
	 */
	function field_post_types() {
		
		$post_types = get_post_types( '', 'names' );
		$post_types_checked = $this->general_settings[MRP_Multi_Rating::POST_TYPES_OPTION];
	
		foreach ( $post_types as $post_type ) {
			
			echo '<input type="checkbox" name="' . MRP_Multi_Rating::GENERAL_SETTINGS . '[' . MRP_Multi_Rating::POST_TYPES_OPTION . '][]" value="' . $post_type . '"';
			
			if ( is_array( $post_types_checked ) ) {
				if ( in_array( $post_type, $post_types_checked ) ) {
					echo 'checked="checked"';
				}
			} else {
				checked( $post_type, $post_types_checked, true );
			}
			
			echo ' />&nbsp;<label class="checkbox-label">' . $post_type . '</label>';
		}
	
		?>
		<p class="description"><?php _e( 'Select the post types to be enabled.', 'multi-rating-pro' ); ?></p>
		<?php 
	}
	/**
	 * Show rating result after submit
	 */
	function field_show_rating_result_after_submit() {
		?>
		<input type="checkbox" name="<?php echo MRP_Multi_Rating::GENERAL_SETTINGS;?>[<?php echo MRP_Multi_Rating::SHOW_RATING_RESULT_AFTER_SUBMIT_OPTION; ?>]" value="true" <?php checked( true, $this->general_settings[MRP_Multi_Rating::SHOW_RATING_RESULT_AFTER_SUBMIT_OPTION], true ); ?> />
		<p class="description"><?php _e( 'Show rating results after the rating form has been submitted (e.g. You rating result was 4/5).', 'multi-rating-pro' ); ?></p>
		<?php 
	}
	/**
	 * Sanitize the general settings
	 * @param $input
	 * @return $input
	 */
	function sanitize_general_settings($input) {
		
		if ( isset( $input[MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] )
				&& $input[MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] = false;
		}
		
		// name
		if ( isset( $input[MRP_Multi_Rating::SHOW_RATING_RESULT_AFTER_SUBMIT_OPTION] ) 
				&& $input[MRP_Multi_Rating::SHOW_RATING_RESULT_AFTER_SUBMIT_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::SHOW_RATING_RESULT_AFTER_SUBMIT_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::SHOW_RATING_RESULT_AFTER_SUBMIT_OPTION] = false;
		}
		
		// allow anonymous users
		if ( isset( $input[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION] ) 
				&& $input[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION] = false;
		}
		
		// comment form multi rating
		if ( isset( $input[MRP_Multi_Rating::COMMENT_FORM_MULTI_RATING_OPTION] ) 
				&& $input[MRP_Multi_Rating::COMMENT_FORM_MULTI_RATING_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::COMMENT_FORM_MULTI_RATING_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::COMMENT_FORM_MULTI_RATING_OPTION] = false;
		}
		
		// comment list multi ratign
		if ( isset( $input[MRP_Multi_Rating::COMMENT_TEXT_MULTI_RATING_OPTION] )
				 && $input[MRP_Multi_Rating::COMMENT_TEXT_MULTI_RATING_OPTION] == 'true') {
			$input[MRP_Multi_Rating::COMMENT_TEXT_MULTI_RATING_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::COMMENT_TEXT_MULTI_RATING_OPTION] = false;
		}
		
		// IP address date validation days duration
		if ( strlen( $input[MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_DAYS_DURATION_OPTION]) > 0 
				&& is_numeric( $input[MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_DAYS_DURATION_OPTION] ) ) {
			$input[MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_DAYS_DURATION_OPTION] = intval( $input[MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_DAYS_DURATION_OPTION] );
		} else {
			$input[MRP_Multi_Rating::$input[MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_DAYS_DURATION_OPTION]] = 1;
		}
		
		return $input;
	}
	
	/**
	 * Filter settings
	 */
	function register_filter_settings() {
		register_setting( MRP_Multi_Rating::FILTER_SETTINGS, MRP_Multi_Rating::FILTER_SETTINGS, array( &$this, 'sanitize_filter_settings' ) );
	
		add_settings_section( 'section_filter', __( 'Filter Settings', 'multi-rating-pro' ), array( &$this, 'section_filter_desc' ), MRP_Multi_Rating::FILTER_SETTINGS );
	
		add_settings_field( MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE, __( 'Exclude home page', 'multi-rating-pro' ), array( &$this, 'field_filter_exclude_home_page' ), MRP_Multi_Rating::FILTER_SETTINGS, 'section_filter' );
		add_settings_field( MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES, __( 'Exclude archive pages', 'multi-rating-pro' ), array( &$this, 'field_filter_exclude_archive_pages' ), MRP_Multi_Rating::FILTER_SETTINGS, 'section_filter' );
		add_settings_field( MRP_Multi_Rating::POST_FILTER_TYPE_OPTION, __( 'Filter type for posts', 'multi-rating-pro' ), array( &$this, 'field_post_filter_type' ), MRP_Multi_Rating::FILTER_SETTINGS, 'section_filter' );
		add_settings_field( MRP_Multi_Rating::FILTERED_POSTS_OPTION, __( 'Filter Post Id\'s', 'multi-rating-pro' ), array( &$this, 'field_filtered_posts' ), MRP_Multi_Rating::FILTER_SETTINGS, 'section_filter' );
		add_settings_field( MRP_Multi_Rating::CATEGORY_FILTER_TYPE_OPTION, __( 'Filter type for categories', 'multi-rating-pro' ), array( &$this, 'field_category_filter_type' ), MRP_Multi_Rating::FILTER_SETTINGS, 'section_filter' );
		add_settings_field( MRP_Multi_Rating::FILTERED_CATEGORIES_OPTION, __( 'Filter Category Id\'s', 'multi-rating-pro' ), array( &$this, 'field_filtered_categories' ), MRP_Multi_Rating::FILTER_SETTINGS, 'section_filter' );
		add_settings_field( MRP_Multi_Rating::PAGE_URL_FILTER_TYPE_OPTION, __( 'Filter type for pages', 'multi-rating-pro' ), array( &$this, 'field_page_url_filter_type' ), MRP_Multi_Rating::FILTER_SETTINGS, 'section_filter' );
		add_settings_field( MRP_Multi_Rating::FILTERED_PAGE_URLS_OPTION, __( 'Filter Page URL\'s', 'multi-rating-pro' ), array( &$this, 'field_filtered_page_urls' ), MRP_Multi_Rating::FILTER_SETTINGS, 'section_filter' );
	}
	/**
	 * Filter section description
	 */
	function section_filter_desc() {
		
		echo '<p>' . sprintf( __('Add specific filters to restrict when %s and %s WP filters are applied in order to automaticlaly display the rating result and rating form in set positions on every page or post.', 'multi-rating-pro' ), 
				'<a href="https://codex.wordpress.org/Function_Reference/the_title">the_title</a>', 
				'<a href="https://codex.wordpress.org/Function_Reference/the_content">the_content</a>' ) . '</p>';
	}
	/**
	 * Post filter type
	 */
	public function field_post_filter_type() {
		?>
		<input type="radio" name="<?php echo MRP_Multi_Rating::FILTER_SETTINGS; ?>[<?php echo MRP_Multi_Rating::POST_FILTER_TYPE_OPTION; ?>]" value="whitelist" <?php checked(MRP_Multi_Rating::WHITELIST_VALUE, $this->filter_settings[MRP_Multi_Rating::POST_FILTER_TYPE_OPTION], true); ?> />
		<label for="filterType"><?php _e( 'Whitelist', 'multi-rating-pro' ); ?></label><br />
		<input type="radio" name="<?php echo MRP_Multi_Rating::FILTER_SETTINGS; ?>[<?php echo MRP_Multi_Rating::POST_FILTER_TYPE_OPTION; ?>]" value="blacklist"  <?php checked(MRP_Multi_Rating::BLACKLIST_VALUE, $this->filter_settings[MRP_Multi_Rating::POST_FILTER_TYPE_OPTION], true); ?>/>
		<label for="filterType"><?php _e( 'Blacklist', 'multi-rating-pro' ); ?></label>
		<p class="description"><?php _e( 'Set a filter type to either include (whitelist) or exclude (blacklist).', 'multi-rating-pro' ); ?></p>
		<?php
	}
	/**
	 * Filtered posts
	 */
	public function field_filtered_posts() {
		
		$option_value = $this->filter_settings[MRP_Multi_Rating::FILTERED_POSTS_OPTION];
		?>
		<textarea  name="<?php echo MRP_Multi_Rating::FILTER_SETTINGS; ?>[<?php echo MRP_Multi_Rating::FILTERED_POSTS_OPTION; ?>]" rows="2" cols="100"><?php echo $option_value; ?></textarea>
		<p class="description"><?php _e( 'Comma separated list of Post Id\'s.', 'multi-rating-pro' ); ?></p>
		<?php 
	}
	/**
	 * Category filter type
	 */
	public function field_category_filter_type() {
		?>
		<input type="radio" name="<?php echo MRP_Multi_Rating::FILTER_SETTINGS; ?>[<?php echo MRP_Multi_Rating::CATEGORY_FILTER_TYPE_OPTION; ?>]" value="whitelist" <?php checked(MRP_Multi_Rating::WHITELIST_VALUE, $this->filter_settings[MRP_Multi_Rating::CATEGORY_FILTER_TYPE_OPTION], true); ?> />
		<label for="filterType"><?php _e( 'Whitelist', 'multi-rating-pro' ); ?></label><br />
		<input type="radio" name="<?php echo MRP_Multi_Rating::FILTER_SETTINGS; ?>[<?php echo MRP_Multi_Rating::CATEGORY_FILTER_TYPE_OPTION; ?>]" value="blacklist"  <?php checked(MRP_Multi_Rating::BLACKLIST_VALUE, $this->filter_settings[MRP_Multi_Rating::CATEGORY_FILTER_TYPE_OPTION], true ); ?>/>
		<label for="filterType"><?php _e( 'Blacklist', 'multi-rating-pro' ); ?></label>
		<p class="description"><?php _e( 'Set a filter type to either include (whitelist) or exclude (blacklist).', 'multi-rating-pro' ); ?></p>
		<?php
	}
	/**
	 * Filtered categories
	 */
	public function field_filtered_categories() {
		$option_value = $this->filter_settings[MRP_Multi_Rating::FILTERED_CATEGORIES_OPTION];
		?>
		<textarea  name="<?php echo MRP_Multi_Rating::FILTER_SETTINGS; ?>[<?php echo MRP_Multi_Rating::FILTERED_CATEGORIES_OPTION; ?>]" rows="2" cols="100"><?php echo $option_value; ?></textarea>
		<p class="description"><?php _e( 'Comma separated list of Category Id\s.', 'multi-rating-pro' ); ?></p>
		<?php 
	}
	/**
	 * Page URL filter type
	 */
	public function field_page_url_filter_type() {
		?>
		<input type="radio" name="<?php echo MRP_Multi_Rating::FILTER_SETTINGS; ?>[<?php echo MRP_Multi_Rating::PAGE_URL_FILTER_TYPE_OPTION; ?>]" value="whitelist" <?php checked( MRP_Multi_Rating::WHITELIST_VALUE, $this->filter_settings[MRP_Multi_Rating::PAGE_URL_FILTER_TYPE_OPTION], true ); ?> />
		<label for="filterType"><?php _e( 'Whitelist', 'multi-rating-pro' ); ?></label><br />
		<input type="radio" name="<?php echo MRP_Multi_Rating::FILTER_SETTINGS; ?>[<?php echo MRP_Multi_Rating::PAGE_URL_FILTER_TYPE_OPTION; ?>]" value="blacklist"  <?php checked( MRP_Multi_Rating::BLACKLIST_VALUE, $this->filter_settings[MRP_Multi_Rating::PAGE_URL_FILTER_TYPE_OPTION], true ); ?>/>
		<label for="filterType"><?php _e( 'Blacklist', 'multi-rating-pro' ); ?></label>
		<p class="description"><?php _e( 'Set a filter type to either include (whitelist) or exclude (blacklist).', 'multi-rating-pro' ); ?></p>
		<?php
	}
	/**
	 * Filtered page URL's
	 */
	public function field_filtered_page_urls() {
		$option_value = $this->filter_settings[MRP_Multi_Rating::FILTERED_PAGE_URLS_OPTION];
		?>
		<textarea  name="<?php echo MRP_Multi_Rating::FILTER_SETTINGS; ?>[<?php echo MRP_Multi_Rating::FILTERED_PAGE_URLS_OPTION; ?>]" rows="2" cols="100"><?php echo $option_value; ?></textarea>
		<p class="description"><?php _e( 'Each page URL must be on a newline.', 'multi-rating-pro' ); ?></p>
		<?php 
	}
	/**
	 * Filter exclude archive pages
	 */
	function field_filter_exclude_archive_pages() {
		?>
		<input type="checkbox" name="<?php echo MRP_Multi_Rating::FILTER_SETTINGS;?>[<?php echo MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES; ?>]" value="true" <?php checked(true, $this->filter_settings[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES], true); ?> />
		<p class="description"><?php printf( __( 'An archive page includes a Category, Tag, Author or a Date based pages. See %s function.', 'multi-rating-pro' ), 
				'<a href="https://codex.wordpress.org/Function_Reference/is_archive">is_archive()</a>' ); ?></p>
		<?php 
	}
	function field_filter_exclude_home_page() {
		?>
		<input type="checkbox" name="<?php echo MRP_Multi_Rating::FILTER_SETTINGS;?>[<?php echo MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE ?>]" value="true" <?php checked(true, $this->filter_settings[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE], true); ?> />
		<p class="description"><?php printf( __( 'See %s function.', 'multi-rating-pro' ), '<a href="https://codex.wordpress.org/Function_Reference/is_home">is_home()</a>' ); ?></p>
		<?php 
	}
	/** 
	 * Sanitize Filter settings
	 */
	function sanitize_filter_settings($input) {
		
		global $wpdb;
		
		// posts
		$all_post_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts" );
		$filtered_posts = preg_split( '/[,]+/', $input[MRP_Multi_Rating::FILTERED_POSTS_OPTION], -1, PREG_SPLIT_NO_EMPTY );
		if ( ! is_array( $all_post_ids ) ) {
			add_settings_error( 'general', 'no_posts_to_flter', __('No posts to filter.', 'multi-rating-pro' ), 'error' );
		} else {
			foreach ( $filtered_posts as $post_id ) {
				
				if ( ! is_numeric($post_id) || ! in_array( $post_id, $all_post_ids ) ) {
					add_settings_error('general', 'invalid_post_id', sprintf( __('Invalid Post Id %s.', 'multi-rating-pro'), $post_id ), 'error');
				}
			}
		}
		
		// categories
		$all_category_ids = get_all_category_ids();
		$filtered_categories = preg_split('/[,]+/', $input[MRP_Multi_Rating::FILTERED_CATEGORIES_OPTION], -1, PREG_SPLIT_NO_EMPTY );
		if ( ! is_array( $all_category_ids ) ) {
			add_settings_error( 'general', 'no_categories_to_filter', __('No categories to filter.', 'multi-rating-pro' ), 'error');
		} else {
			foreach ( $filtered_categories as $category_id ) {
				
				if ( ! is_numeric($category_id) || !in_array($category_id, $all_category_ids)) {
					add_settings_error( 'general', 'invalid_category_id', springf( __( 'Invalid Category Id %s.', 'multi-rating-pro' ), $category_id ), 'error' );
				}
			}
		}
		
		// page URL's
		$url_filters_list = preg_split( '/[\r\n,]+/', $input[MRP_Multi_Rating::FILTERED_PAGE_URLS_OPTION], -1, PREG_SPLIT_NO_EMPTY );
		
		$new_url_filters_list = '';
		foreach ( $url_filters_list as $url ) {
			$url = MRP_Utils::normalize_url( $url );
			$new_url_filters_list .= $url . '&#13;&#10;';
		}
		$input[MRP_Multi_Rating::FILTERED_PAGE_URLS_OPTION] = $new_url_filters_list;
		
		
		// exclude home page
		if ( isset( $input[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] ) && $input[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] == 'true') {
			$input[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] = true;
		} else {
			$input[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] = false;
		}
		
		// exclude archive pages
		if ( isset( $input[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] ) && $input[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] == 'true') {
			$input[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] = true;
		} else {
			$input[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] = false;
		}
		
		return $input;
	}
	
	/**
	 * Position settings
	 */
	function register_position_settings() {
		register_setting( MRP_Multi_Rating::POSITION_SETTINGS, MRP_Multi_Rating::POSITION_SETTINGS, array( &$this, 'sanitize_position_settings' ) );
	
		add_settings_section( 'section_position', __( 'Position Settings', 'multi-rating-pro' ), array( &$this, 'section_position_desc' ), MRP_Multi_Rating::POSITION_SETTINGS );
		add_settings_section( 'section_show_fields', __( 'Show Additional Fields', 'multi-rating-pro' ), array( &$this, 'section_show_fields_desc' ), MRP_Multi_Rating::POSITION_SETTINGS );
	
		add_settings_field( MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION, __( 'Rating results position', 'multi-rating-pro' ), array( &$this, 'field_rating_results_position' ), MRP_Multi_Rating::POSITION_SETTINGS, 'section_position' );
		add_settings_field( MRP_Multi_Rating::RATING_FORM_POSITION_OPTION, __( 'Rating form position', 'multi-rating-pro' ), array( &$this, 'field_rating_form_position' ), MRP_Multi_Rating::POSITION_SETTINGS, 'section_position' );
		
		add_settings_field( MRP_Multi_Rating::SHOW_NAME_INPUT_OPTION, __( 'Show name input', 'multi-rating-pro' ), array( &$this, 'field_show_name_input' ), MRP_Multi_Rating::POSITION_SETTINGS, 'section_show_fields' );
		add_settings_field( MRP_Multi_Rating::SHOW_EMAIL_INPUT_OPTION, __( 'Show email input', 'multi-rating-pro' ), array( &$this, 'field_show_email_input' ), MRP_Multi_Rating::POSITION_SETTINGS, 'section_show_fields' );
		add_settings_field( MRP_Multi_Rating::SHOW_COMMENT_TEXTAREA_OPTION, __( 'Show comment textarea', 'multi-rating-pro' ), array( &$this, 'field_show_comment_textarea' ), MRP_Multi_Rating::POSITION_SETTINGS, 'section_show_fields' );
		add_settings_field( MRP_Multi_Rating::GENERATE_RICH_SNIPPETS_OPTION, __( 'Generate rich snippets', 'multi-rating-pro' ), array( &$this, 'field_generate_rich_snippets' ), MRP_Multi_Rating::POSITION_SETTINGS, 'section_position' );
	}
	/**
	 * Position section description
	 */
	function section_position_desc() {
		?>
		<p><?php _e( 'These settings allow you to automatically place the rating form and rating results on every post or page in default positions. You can override these settings for a particular page or post using the Multi Rating meta box in the edit post page. You can also choose to turn off rich snippets markup.', 'multi-rating-pro' ); ?></p>
		<?php
	}
	/**
	 * Show fields section description
	 */
	function section_show_fields_desc() {
		?>
		<p><?php _e( 'Showing a name input, email input and comment textarea can be added to the rating form.', 'multi-rating-pro' ); ?></p>
		<?php
	}
	/**
	 * Generate rich snippets
	 */
	function field_generate_rich_snippets() {
		?>
		<input type="checkbox" name="<?php echo MRP_Multi_Rating::POSITION_SETTINGS;?>[<?php echo MRP_Multi_Rating::GENERATE_RICH_SNIPPETS_OPTION; ?>]" value="true" <?php checked(true, $this->position_settings[MRP_Multi_Rating::GENERATE_RICH_SNIPPETS_OPTION], true); ?> />
		<p class="description"><?php _e( 'Genrate schema.org rich snippets markup (i.e. aggregate 5 star rating that is returned in search engine results).', 'multi-rating-pro' ); ?></p>
		<?php 
	}
	/**
	 * Rating results position
	 */
	function field_rating_results_position() {
		?>
		<select name="<?php echo MRP_Multi_Rating::POSITION_SETTINGS; ?>[<?php echo MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION; ?>]">
			<option value="" <?php selected('', $this->position_settings[MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION], true); ?>><?php _e( 'None', 'multi-rating-pro' ); ?></option>
			<option value="before_title" <?php selected('before_title', $this->position_settings[MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION], true); ?>><?php _e( 'Before title', 'multi-rating-pro' ); ?></option>
			<option value="after_title" <?php selected('after_title', $this->position_settings[MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION], true); ?>><?php _e( 'After title', 'multi-rating-pro' ); ?></option>
		</select>
		<p class="description"><?php _e( 'Choose to automatically display the rating result before or after the post title for all enabled post types.', 'multi-rating-pro' ); ?></p>
		<?php
	}
	/**
	 * Rating form position
	 */
	function field_rating_form_position() {
		?>
		<select name="<?php echo MRP_Multi_Rating::POSITION_SETTINGS; ?>[<?php echo MRP_Multi_Rating::RATING_FORM_POSITION_OPTION; ?>]">
			<option value="" <?php selected('', $this->position_settings[MRP_Multi_Rating::RATING_FORM_POSITION_OPTION], true); ?>><?php _e( 'None', 'multi-rating-pro' ); ?></option>
			<option value="before_content" <?php selected('before_content', $this->position_settings[MRP_Multi_Rating::RATING_FORM_POSITION_OPTION], true); ?>><?php _e( 'Before content', 'multi-rating-pro' ); ?></option>
			<option value="after_content" <?php selected('after_content', $this->position_settings[MRP_Multi_Rating::RATING_FORM_POSITION_OPTION], true); ?>><?php _e( 'After content', 'multi-rating-pro' ); ?></option>
		</select>
		<p class="description"><?php _e( 'Choose to automatically display the rating form before or after the post content for all enabled post types.', 'multi-rating-pro' ); ?></p>
		<?php
	}
	/**
	 * Show name input
	 */
	function field_show_name_input() {
		?>
		<input type="checkbox" name="<?php echo MRP_Multi_Rating::POSITION_SETTINGS;?>[<?php echo MRP_Multi_Rating::SHOW_NAME_INPUT_OPTION; ?>]" value="true" <?php checked(true, $this->position_settings[MRP_Multi_Rating::SHOW_NAME_INPUT_OPTION], true); ?> />
		<p class="description"><?php _e( 'Add a name input on the rating form.', 'multi-rating-pro' ); ?></p>
		<?php 
	}
	/**
	 * Show email input
	 */
	function field_show_email_input() {
		?>
		<input type="checkbox" name="<?php echo MRP_Multi_Rating::POSITION_SETTINGS;?>[<?php echo MRP_Multi_Rating::SHOW_EMAIL_INPUT_OPTION; ?>]" value="true" <?php checked(true, $this->position_settings[MRP_Multi_Rating::SHOW_EMAIL_INPUT_OPTION], true); ?> />
		<p class="description"><?php _e( 'Add an e-mail address input on the rating form.', 'multi-rating-pro' ); ?></p>
		<?php 
	}
	function field_show_comment_textarea() {
		?>
		<input type="checkbox" name="<?php echo MRP_Multi_Rating::POSITION_SETTINGS;?>[<?php echo MRP_Multi_Rating::SHOW_COMMENT_TEXTAREA_OPTION; ?>]" value="true" <?php checked(true, $this->position_settings[MRP_Multi_Rating::SHOW_COMMENT_TEXTAREA_OPTION], true); ?> />
		<p class="description"><?php _e( 'Add a comments textarea on the rating form.', 'multi-rating-pro' ); ?></p>
		<?php 
	}
	/**
	 * Sanitize position settings
	 */
	function sanitize_position_settings($input) {
		
		// name
		if ( isset( $input[MRP_Multi_Rating::SHOW_NAME_INPUT_OPTION] ) && $input[MRP_Multi_Rating::SHOW_NAME_INPUT_OPTION] == 'true') {
			$input[MRP_Multi_Rating::SHOW_NAME_INPUT_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::SHOW_NAME_INPUT_OPTION] = false;
		}
		
		// email
		if ( isset( $input[MRP_Multi_Rating::SHOW_EMAIL_INPUT_OPTION] ) && $input[MRP_Multi_Rating::SHOW_EMAIL_INPUT_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::SHOW_EMAIL_INPUT_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::SHOW_EMAIL_INPUT_OPTION] = false;
		}
		
		// comment
		if ( isset( $input[MRP_Multi_Rating::SHOW_COMMENT_TEXTAREA_OPTION] ) && $input[MRP_Multi_Rating::SHOW_COMMENT_TEXTAREA_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::SHOW_COMMENT_TEXTAREA_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::SHOW_COMMENT_TEXTAREA_OPTION] = false;
		}
		
		if ( isset( $input[MRP_Multi_Rating::GENERATE_RICH_SNIPPETS_OPTION] ) && $input[MRP_Multi_Rating::GENERATE_RICH_SNIPPETS_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::GENERATE_RICH_SNIPPETS_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::GENERATE_RICH_SNIPPETS_OPTION] = false;
		}
		
		return $input;
	}
	
	
	/**
	 * Style settings
	 */
	function register_style_settings() {
		
		register_setting( MRP_Multi_Rating::STYLE_SETTINGS, MRP_Multi_Rating::STYLE_SETTINGS, array( &$this, 'sanitize_style_settings' ) );
	
		add_settings_section( 'section_style', __( 'Style Settings', 'multi-rating-pro' ), array( &$this, 'section_style_desc' ), MRP_Multi_Rating::STYLE_SETTINGS );

		add_settings_field( MRP_Multi_Rating::CUSTOM_CSS_OPTION, __( 'Custom CSS', 'multi-rating-pro' ), array( &$this, 'field_custom_css' ), MRP_Multi_Rating::STYLE_SETTINGS, 'section_style' );
		
		add_settings_field( MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION, __( 'Star rating color', 'multi-rating-pro' ), array( &$this, 'field_star_rating_colour' ), MRP_Multi_Rating::STYLE_SETTINGS, 'section_style' );
		add_settings_field( MRP_Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION, __( 'Star rating on hover color', 'multi-rating-pro' ), array( &$this, 'field_star_rating_hover_colour' ), MRP_Multi_Rating::STYLE_SETTINGS, 'section_style' );
		
		add_settings_field( MRP_Multi_Rating::INCLUDE_FONT_AWESOME_OPTION, __( 'Include loading Font Awesome', 'multi-rating-pro' ), array( &$this, 'field_include_font_awesome' ), MRP_Multi_Rating::STYLE_SETTINGS, 'section_style' );
		add_settings_field( MRP_Multi_Rating::FONT_AWESOME_VERSION_OPTION, __( 'Font Awesome version', 'multi-rating-pro' ), array( &$this, 'field_font_awesome_version' ), MRP_Multi_Rating::STYLE_SETTINGS, 'section_style' );
	}
	
	/**
	 * Style section description
	 */
	function section_style_desc() {
	}
	
	/**
	 * Include plugin loading Font Awesome CSS
	 */
	function field_include_font_awesome() {
		?>
		<input type="checkbox" name="<?php echo MRP_Multi_Rating::STYLE_SETTINGS; ?>[<?php echo MRP_Multi_Rating::INCLUDE_FONT_AWESOME_OPTION; ?>]" value="true" <?php checked(true, $this->style_settings[MRP_Multi_Rating::INCLUDE_FONT_AWESOME_OPTION], true); ?> />
		<p class="description"><?php _e( 'Do you want the plugin to include loading of the Font Awesome CSS?', 'multi-rating-pro' ); ?></p>
		<?php
	}
		
	/**
	 * Which version of Font Awesome to use
	 */
	function field_font_awesome_version() {
		?>
		<select name="<?php echo MRP_Multi_Rating::STYLE_SETTINGS; ?>[<?php echo MRP_Multi_Rating::FONT_AWESOME_VERSION_OPTION; ?>]">
			<option value="4.1.0" <?php selected( '4.1.0', $this->style_settings[MRP_Multi_Rating::FONT_AWESOME_VERSION_OPTION], true); ?>>4.1.0</option>
			<option value="4.0.3" <?php selected( '4.0.3', $this->style_settings[MRP_Multi_Rating::FONT_AWESOME_VERSION_OPTION], true); ?>>4.0.3</option>
			<option value="3.2.1" <?php selected( '3.2.1', $this->style_settings[MRP_Multi_Rating::FONT_AWESOME_VERSION_OPTION], true); ?>>3.2.1</option>
		</select>
		<?php
	}
	
	/**
	 * Custom CSS
	 */
	function field_custom_css() {
		?>
		<textarea cols="50" rows="10" class="large-text" name="<?php echo MRP_Multi_Rating::STYLE_SETTINGS; ?>[<?php echo MRP_Multi_Rating::CUSTOM_CSS_OPTION; ?>]"><?php echo stripslashes( $this->style_settings[MRP_Multi_Rating::CUSTOM_CSS_OPTION] ); ?></textarea>
		<?php 
	}
	/**
	 * Star rating colour
	 */
	function field_star_rating_colour() {	
		$star_rating_colour = $this->style_settings[MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION];
		?>
   	 	<input type="text" class="color-picker" id="star-rating-colour" name="<?php echo MRP_Multi_Rating::STYLE_SETTINGS; ?>[<?php echo MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION; ?>]; ?>" value="<?php echo $star_rating_colour; ?>" />
		<?php 
	}
	/**
	 * Star rating on hover colour
	 */
	function field_star_rating_hover_colour() {
		$star_rating_hover_colour = $this->style_settings[MRP_Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION];
		?>
	  	 <input type="text" class="color-picker" id="star-rating-hover-colour" name="<?php echo MRP_Multi_Rating::STYLE_SETTINGS; ?>[<?php echo MRP_Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION; ?>]; ?>" value="<?php echo $star_rating_hover_colour; ?>" />
		<?php 
	}
	/**
	 * Sanitize style settings
	 */
	function sanitize_style_settings( $input ) {
		
		$input[MRP_Multi_Rating::CUSTOM_CSS_OPTION] = addslashes( $input[MRP_Multi_Rating::CUSTOM_CSS_OPTION] );
		
		if ( isset( $input[MRP_Multi_Rating::INCLUDE_FONT_AWESOME_OPTION] ) && $input[MRP_Multi_Rating::INCLUDE_FONT_AWESOME_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::INCLUDE_FONT_AWESOME_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::INCLUDE_FONT_AWESOME_OPTION] = false;
		}
		
		return $input;
	}

	/**
	 * Custom Text settings
	 */
	function register_custom_text_settings() {
		register_setting( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, array( &$this, 'sanitize_custom_text_settings' ) );
	
		add_settings_section( 'section_custom_text', __( 'Custom Text Settings', 'multi-rating-pro' ), array( &$this, 'section_custom_text_desc' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
		add_settings_field( MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION, __( 'Rating form title', 'multi-rating-pro' ), array( &$this, 'field_rating_form_title_text' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( MRP_Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION, __( 'Top Rating Results title', 'multi-rating-pro' ), array( &$this, 'field_top_rating_results_title_text' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( MRP_Multi_Rating::USER_RATING_RESULTS_TITLE_TEXT_OPTION, __( 'User Rating Results title', 'multi-rating-pro' ), array( &$this, 'field_user_rating_results_title_text' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( MRP_Multi_Rating::RATING_ITEM_RESULTS_TITLE_TEXT_OPTION, __( 'Rating Item Results title', 'multi-rating-pro' ), array( &$this, 'field_rating_item_results_title_text' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		
		add_settings_field( MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION, __( 'Submit rating form button text', 'multi-rating-pro' ), array( &$this, 'field_submit_rating_form_button_text' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION, __( 'Update rating form button text', 'multi-rating-pro' ), array( &$this, 'field_update_rating_form_button_text' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION, __( 'Delete rating form button text', 'multi-rating-pro' ), array( &$this, 'field_delete_rating_form_button_text' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		
		add_settings_field( MRP_Multi_Rating::SUBMIT_RATING_SUCCESS_MESSAGE_OPTION, __( 'Submit rating success message', 'multi-rating-pro' ), array( &$this, 'field_submit_rating_success_message' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( MRP_Multi_Rating::UPDATE_RATING_SUCCESS_MESSAGE_OPTION, __( 'Update rating success message', 'multi-rating-pro' ), array( &$this, 'field_update_rating_success_message' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( MRP_Multi_Rating::DELETE_RATING_SUCCESS_MESSAGE_OPTION, __( 'Delete rating success message', 'multi-rating-pro' ), array( &$this, 'field_delete_rating_success_message' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		
		add_settings_field( MRP_Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION, __( 'Date validation failure message', 'multi-rating-pro' ), array( &$this, 'field_date_validation_fail_message' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION, __( 'No rating results text', 'multi-rating-pro' ), array( &$this, 'field_no_rating_results_text' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( MRP_Multi_Rating::ALREADY_SUBMITTED_RATING_FORM_MESSAGE_OPTION, __( 'Already submitted rating form message', 'multi-rating-pro' ), array( &$this, 'field_already_submitted_rating_form_message' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_FAILURE_MESSAGE_OPTION, __( 'Allow anonymous ratings failuire message', 'multi-rating-pro' ), array( &$this, 'field_allow_anonymous_ratings_failure_message' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( MRP_Multi_Rating::CHAR_ENCODING_OPTION, __( 'Character encoding', 'multi-rating-pro' ), array( &$this, 'field_char_encoding' ), MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		
	}
	/**
	 * Custom text section description
	 */
	function section_custom_text_desc() {
		echo '<p class="description">' . __( 'Modify the default text and messages.', 'multi-rating-pro' ) . '</p>';
	}
	/**
	 * Allow anonymous ratings failure message
	 */
	function field_allow_anonymous_ratings_failure_message() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_FAILURE_MESSAGE_OPTION; ?>]" class="large-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_FAILURE_MESSAGE_OPTION]; ?>" />
		<?php
	}
	/**
	 * Already submitted rating form message
	 */
	function field_already_submitted_rating_form_message() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::ALREADY_SUBMITTED_RATING_FORM_MESSAGE_OPTION; ?>]" class="large-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::ALREADY_SUBMITTED_RATING_FORM_MESSAGE_OPTION]; ?>" />
		<?php
	}
	/**
	 * Submit rating form button text
	 */
	function field_submit_rating_form_button_text() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION]; ?>" />
		<?php
	}
	/**
	 * Update rating form button text
	 */
	function field_update_rating_form_button_text() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION]; ?>" />
		<?php
	}
	/**
	 * Delete rating form button text
	 */
	function field_delete_rating_form_button_text() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION]; ?>" />
		<?php
	}	
	/**
	 * Submit rating success message
	 */
	public function field_submit_rating_success_message() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::SUBMIT_RATING_SUCCESS_MESSAGE_OPTION; ?>]" class="large-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_SUCCESS_MESSAGE_OPTION]; ?>" />
		<?php
	}
	/**
	 * Update rating success message
	 */
	public function field_update_rating_success_message() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::UPDATE_RATING_SUCCESS_MESSAGE_OPTION; ?>]" class="large-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::UPDATE_RATING_SUCCESS_MESSAGE_OPTION]; ?>" />
		<?php
	}
	/**
	 * Delete rating success message
	 */
	public function field_delete_rating_success_message() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::DELETE_RATING_SUCCESS_MESSAGE_OPTION; ?>]" class="large-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::DELETE_RATING_SUCCESS_MESSAGE_OPTION]; ?>" />
		<?php
	}
	/**
	 * Date validation failure message
	 */
	public function field_date_validation_fail_message() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION; ?>]" class="large-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION]; ?>" />
		<?php
	}
	/**
	 * Rating for title
	 */
	function field_rating_form_title_text() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION]; ?>" />
		<?php
	}
	/**
	 * Rating result reviews title
	 */
	function field_rating_result_reviews_title_text() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::RATING_RESULT_REVIEWS_TITLE_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::RATING_RESULT_REVIEWS_TITLE_TEXT_OPTION]; ?>" />
		<?php
	}
	/**
	 * Top rating results title
	 */
	function field_top_rating_results_title_text() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION]; ?>" />
		<?php
	}
	/**
	 * User rating results title
	 */
	function field_user_rating_results_title_text() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::USER_RATING_RESULTS_TITLE_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::USER_RATING_RESULTS_TITLE_TEXT_OPTION]; ?>" />
		<?php
	}
	/**
	 * Rating item results title
	 */
	function field_rating_item_results_title_text() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::RATING_ITEM_RESULTS_TITLE_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::RATING_ITEM_RESULTS_TITLE_TEXT_OPTION]; ?>" />
		<?php
	}	
	/**
	 * No rating results text
	 */
	function field_no_rating_results_text() {
		?>
		<input type="text" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION]; ?>" />
		<?php
	}		
	/**
	 * Character encoding
	 */
	function field_char_encoding() {
		?>	
		<select name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo MRP_Multi_Rating::CHAR_ENCODING_OPTION; ?>]">
		<option value="" <?php selected('', $this->custom_text_settings[MRP_Multi_Rating::CHAR_ENCODING_OPTION], true); ?>><?php _e( 'Keep current charset (Recommended)', 'multi-rating-pro' ); ?></option>
	        <option value="utf8_general_ci" <?php selected('utf8_general_ci', $this->custom_text_settings[MRP_Multi_Rating::CHAR_ENCODING_OPTION], true); ?>><?php _e( 'UTF-8 (try this first)', 'multi-rating-pro' ); ?></option>
	        <option value="latin1_swedish_ci" <?php selected('latin1_swedish_ci', $this->custom_text_settings[MRP_Multi_Rating::CHAR_ENCODING_OPTION], true); ?>><?php _e( 'latin1_swedish_ci', 'multi-rating-pro' ); ?></option>
		</select>
		<?php
	}
	/**
	 * Sanitize custom text settings
	 */
	function sanitize_custom_text_settings( $input ) {
		
		global $wpdb;
		
		$character_encoding = $input[MRP_Multi_Rating::CHAR_ENCODING_OPTION];
	
		$old_character_set = $this->general_settings[MRP_Multi_Rating::CHAR_ENCODING_OPTION];
		
		if ( $character_encoding != $old_character_set ) {
			
			$tables = array( $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_TBL_NAME );
			
			foreach ($tables as $table) {
				$rows = $wpdb->get_results( "DESCRIBE {$table}" );
				foreach ($rows as $row) {
					$name = $row->Field;
					$type = $row->Type;
					
					if ( preg_match( "/^varchar\((\d+)\)$/i", $type, $mat ) || ! strcasecmp( $type, "CHAR" )
							|| ! strcasecmp( $type, "TEXT" ) || ! strcasecmp( $type, "MEDIUMTEXT") ) {
						
						$wpdb->query( 'ALTER TABLE ' . $table .' CHANGE ' . $name . ' ' . $name . ' ' . $type . ' COLLATE ' . $character_encoding );
					}
				}
			}
		}
	
		return $input;
	}

	/**
	 * Admin menus
	 */
	public function add_admin_menus() {
		add_menu_page( __( 'Multi Rating Pro', 'multi-rating-pro' ), __( 'Multi Rating Pro', 'multi-rating-pro' ), 'manage_options', MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, array( &$this, 'rating_results_page' ), '', null );
	
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, '', '', 'manage_options', MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, array( &$this, 'rating_results_page' ) );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Rating Results', 'multi-rating-pro' ), __( 'Rating Results', 'multi-rating-pro' ), 'manage_options', MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, array( &$this, 'rating_results_page' ) );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Rating Items', 'multi-rating-pro' ), __( 'Rating Items', 'multi-rating-pro' ), 'manage_options', MRP_Multi_Rating::RATING_ITEMS_PAGE_SLUG, array( &$this, 'rating_items_page' ) );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Add New Rating Item', 'multi-rating-pro' ), __( 'Add New Rating Item', 'multi-rating-pro' ),'manage_options', MRP_Multi_Rating::ADD_NEW_RATING_ITEM_PAGE_SLUG, array( &$this, 'add_new_rating_item_page' ) );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Rating Forms', 'multi-rating-pro' ), __( 'Rating Forms', 'multi-rating-pro' ),'manage_options', MRP_Multi_Rating::RATING_FORMS_PAGE_SLUG, array( &$this, 'rating_forms_page' ) );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Add New Rating Form', 'multi-rating-pro' ), __( 'Add New Rating Form', 'multi-rating-pro' ),  'manage_options', MRP_Multi_Rating::ADD_NEW_RATING_FORM_PAGE_SLUG, array( &$this, 'add_new_rating_form_page' ) );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Settings', 'multi-rating-pro' ), __( 'Settings', 'multi-rating-pro' ), 'manage_options', MRP_Multi_Rating::SETTINGS_PAGE_SLUG, array( &$this, 'settings_page' ) );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Reports', 'multi-rating-pro' ), __( 'Reports', 'multi-rating-pro' ), 'manage_options', MRP_Multi_Rating::REPORTS_PAGE_SLUG, array( &$this, 'reports_page' ) );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'About', 'multi-rating-pro' ), __( 'About', 'multi-rating-pro' ), 'manage_options', MRP_Multi_Rating::ABOUT_PAGE_SLUG, array( &$this, 'about_page' ) );
	}
	
	/**
	 * Reports page
	 */
	public function reports_page() {
		?>
		<div class="wrap">
			<h2 class="nav-tab-wrapper">
				<?php
				$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'reports';
				$page = MRP_Multi_Rating::REPORTS_PAGE_SLUG;
				
				$tabs = array (
						'reports' => __( 'Reports', 'multi-rating-pro' ),
						'export-import' => __( 'Export / Import', 'multi-rating-pro' )
				);
				
				foreach ( $tabs as $tab_key => $tab_caption ) {
					$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
					echo '<a class="nav-tab ' . $active . '" href="?page=' . $page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
				} 
				?>
			</h2>

			
			<?php 
			if ( $current_tab == 'export-import' ) {?>	
				<div class="metabox-holder">
					<div class="postbox">
						<h3><span><?php _e( 'Export Rating Results', 'multi-rating-pro' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Export Rating Results to a CSV file.', 'multi-rating-pro' ); ?></p>
							
							<form method="post" id="export-rating-results-form">
								<p>
									<input type="text" name="username" id="username" class="" autocomplete="off" placeholder="Username">

									<input type="text" class="date-picker" autocomplete="off" name="from-date" placeholder="From - dd/MM/yyyy" id="from-date">
									<input type="text" class="date-picker" autocomplete="off" name="to-date" placeholder="To - dd/MM/yyyy" id="to-date">

									<select name="post-id" id="post-id">
										<option value=""><?php _e( 'All posts / pages', 'multi-rating-pro' ); ?></option>
										<?php	
										global $wpdb;
										$query = 'SELECT DISTINCT post_id FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME;
										
										$rows = $wpdb->get_results( $query, ARRAY_A );
					
										foreach ( $rows as $row ) {
											$post = get_post( $row['post_id'] );
											?>
											<option value="<?php echo $post->ID; ?>">
												<?php echo get_the_title( $post->ID ); ?>
											</option>
										<?php } ?>
									</select>

									<select id="rating-form-id" name="rating-form-id">
									
										<option value=""><?php _e( 'All rating forms', 'multi-rating-pro' ); ?></option>
										<?php 
										$query = 'SELECT name, rating_form_id FROM ' . $wpdb->prefix .  MRP_Multi_Rating::RATING_FORM_TBL_NAME;
										$rows = $wpdb->get_results( $query, ARRAY_A );
										
										foreach ( $rows as $row ) {
											$selected = '';
											if ( intval( $row['rating_form_id'] ) == intval( $rating_form_id ) ) {
												$selected = ' selected="selected"';
											}
											
											echo '<option value="' . $row['rating_form_id'] . '"' . $selected . '>' . $row['name'] . '</option>';
										} ?>
									</select>
									
									<input type="checkbox" name="comments-only" id="comments-only" value="true" />
									<label for="comments-only"><?php _e( 'Comments only', 'multi-rating-pro' ); ?></label>
								</p>
								
								<p>
									<input type="hidden" name="export-rating-results" id="export-rating-results" value="false" />
									<?php 
									submit_button( __( 'Export', 'multi-rating-pro' ), 'secondary', 'export-btn', false, null );
									?>
								</p>
							</form>
						</div><!-- .inside -->
					</div>
				</div>
			<?php } else { ?>
				<h3><?php _e( 'Number of entries per day', 'multi-rating-pro' ); ?></h3>
				<?php 
				
				global $wpdb;
				
				// Time graph
				$query = 'SELECT DISTINCT DATE(entry_date ) AS day, count(*) as count FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' GROUP BY day ORDER BY entry_date DESC';
				$rows = $wpdb->get_results($query);
					
				$time_data = array();
				foreach ($rows as $row) {
					$day = $row->day;
					$count = $row->count;
					// TODO if a day has no data, then make it 0 visitors.
					// Otherwise, it is not plotted on the graph as 0.
			
					array_push($time_data, array((strtotime($day) * 1000), intval($count)));
				}
				?>
				<div class="flot-container">
					<div class="report-wrapper" style="height: 300px;">
						<div id="entry-count-placeholder" class="report-placeholder"></div>
					</div>
				</div>
				<div class="flot-container">
					<div class="report-wrapper" style="height: 100px;">
						<div id="entry-count-overview-placeholder" class="report-placeholder"></div>
					</div>
				</div>
										
				<script type="text/javascript">
					// Time graph
					jQuery(document).ready(function() {
						// add markers for weekends on grid
						function weekendAreas(axes) {
							var markings = [];
							var d = new Date(axes.xaxis.min);
							// go to the first Saturday
							d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
							d.setUTCSeconds(0);
							d.setUTCMinutes(0);
							d.setUTCHours(0);
							var i = d.getTime();
							// when we don't set yaxis, the rectangle automatically
							// extends to infinity upwards and downwards
							do {
								markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
								i += 7 * 24 * 60 * 60 * 1000;
							} while (i < axes.xaxis.max);
							return markings;
						}
	
						var options = {
							xaxis: {
								mode: "time",
								tickLength: 5
							},
							selection: {
								mode: "x"
							},
							grid: {
								markings: weekendAreas,
								hoverable : true,
								show: true,
								aboveData: false,
								color: '#BBB',
								backgroundColor: '#f9f9f9',
								borderColor: '#ccc',
								borderWidth: 2,
							},
							series : {
								lines: {
									show: true,
									lineWidth: 1
								},
								points: { show: true }
							}
						};
						
						var plot = jQuery.plot("#entry-count-placeholder", [<?php echo json_encode($time_data); ?>], options);
						
						var overview = jQuery.plot("#entry-count-overview-placeholder", [<?php echo json_encode($time_data); ?>], {
							series: {
								lines: {
									show: true,
									lineWidth: 1
								},
								shadowSize: 0
							},
							xaxis: {
								ticks: [],
								mode: "time"
							},
							yaxis: {
								ticks: [],
								min: 0,
								autoscaleMargin: 0.1
							},
							selection: {
								mode: "x"
							},
							grid: {
								markings: weekendAreas,
								hoverable : true,
								show: true,
								aboveData: false,
								color: '#BBB',
								backgroundColor: '#f9f9f9',
								borderColor: '#ccc',
								borderWidth: 2,
								
							},
						});

						function flot_tooltip(x, y, contents) {
							jQuery('<div id="flot-tooltip">' + contents + '</div>').css( {
								position: 'absolute',
								display: 'none',
								top: y + 5,
								left: x + 5,
								border: '1px solid #fdd',
								padding: '2px',
								'background-color': '#fee',
								opacity: 0.80
							}).appendTo("body").fadeIn(200);
						}
							
						jQuery("#entry-count-placeholder").bind("plotselected", function (event, ranges) {
							// do the zooming
									
							plot = jQuery.plot("#entry-count-placeholder", [<?php echo json_encode($time_data); ?>], jQuery.extend(true, {}, options, {
								xaxis: {
									min: ranges.xaxis.from,
									max: ranges.xaxis.to
								}
							}));
									
							// don't fire event on the overview to prevent eternal loop
							overview.setSelection(ranges, true);
						});
												
						jQuery("#entry-count-overview-placeholder").bind("plotselected", function (event, ranges) {
							plot.setSelection(ranges);
						});

						jQuery("#entry-count-placeholder").bind("plothover", function (event, pos, item) {
							if (item) {
						   		jQuery("#flot-tooltip").remove();
								var x = item.datapoint[0].toFixed(2),
								y = item.datapoint[1].toFixed(2);

								flot_tooltip( item.pageX - 30, item.pageY - 20, item.datapoint[1] );
						    } else {
						    	jQuery("#flot-tooltip").remove();
						    }
						});
					});
				</script>
			</div>
			<?php 
		}
	}
	
	/**
	 * Rating forms page
	 */
	public function rating_forms_page() {
		?>
		<div class="wrap">
			<h2><?php _e('Rating Forms', 'multi-rating-pro' ); ?><a class="add-new-h2" href="admin.php?page=<?php echo MRP_Multi_Rating::ADD_NEW_RATING_FORM_PAGE_SLUG; ?>"><?php _e('Add New', 'multi-rating-pro' ); ?></a></h2>
			
			<form method="post" id="rating-form-table-form">
				<?php 
				$rating_form_table = new MRP_Rating_Form_Table();
				$rating_form_table->prepare_items();
				$rating_form_table->display();
				?>
			</form>
		</div>
		<?php 
	}
	
	/**
	 * Performs custom admin actions on init depending on HTTP request parameters (e.g. export rating results)
	 */
	public function do_admin_actions() {
		
		// if downloading the rating results csv export
		if ( isset( $_POST['export-rating-results'] ) 
				&& $_POST['export-rating-results'] === "true" ) {
			
			$file_name = 'rating-results-' . date( 'YmdHis' ) . '.csv';
			
			$username = isset( $_POST['username'] ) ? $_POST['username'] : null;
			$from_date = isset( $_POST['from-date'] ) ? $_POST['from-date'] : null;
			$to_date = isset( $_POST['to-date'] ) ? $_POST['to-date'] : null;
			$post_id = isset( $_POST['post-id'] ) ? $_POST['post-id'] : null;
			$rating_form_id = isset( $_POST['rating-form-id'] ) ? $_POST['rating-form-id'] : null;
			$comments_only = isset( $_POST['comments-only'] ) ? true : false;
			
			$filters = array();
			if ( $username != null && strlen( $username ) > 0 ) {
				$filters['username'] = $username;
			}
			if ( $rating_form_id != null && strlen( $rating_form_id ) > 0 ) {
				$filters['rating_form_id'] = $rating_form_id;
			}
			if ( $post_id != null && strlen( $post_id ) > 0 ) {
				$filters['post_id'] = $post_id;
			}
			if ( $comments_only == true) {
				$filters['comments_only'] = true;
			}
			if ( $from_date != null && strlen( $from_date ) > 0 ) {
				list( $year, $month, $day ) = explode( '/', $from_date ); // default yyyy/mm/dd format
				
				if ( checkdate( $month , $day , $year )) {
					$filters['from_date'] = $from_date;
				}
			}
			if ( $to_date != null && strlen($to_date) > 0 ) {
				list( $year, $month, $day ) = explode( '/', $to_date );// default yyyy/mm/dd format
				
				if ( checkdate( $month , $day , $year )) {
					$filters['to_date'] = $to_date;
				}
			}
			
			if ( MRP_Multi_Rating_API::generate_rating_results_csv_file( $file_name, $filters ) ) {
					
				header('Content-type: text/csv');
				header('Content-Disposition: attachment; filename="' . $file_name . '"');
				readfile($file_name);
				
				// delete file
				unlink($file_name);
			}
			
			die();
		}
	}
	
	/**
	 * Shows the settings page
	 *
	 * @since 0.1
	 */
	public function settings_page() {
		?>
		<div class="wrap">
			<h2 class="nav-tab-wrapper">
				<?php
				$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : MRP_Multi_Rating::GENERAL_SETTINGS_TAB;
				$page = MRP_Multi_Rating::SETTINGS_PAGE_SLUG;
				$tabs = array (MRP_Multi_Rating::GENERAL_SETTINGS_TAB => __('General', 'multi-rating-pro' ),
						MRP_Multi_Rating::POSITION_SETTINGS_TAB => __('Auto Placement', 'multi-rating-pro' ), 
						MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB => __( 'Custom Text', 'multi-rating-pro' ),
						MRP_Multi_Rating::STYLE_SETTINGS_TAB => __( 'Style', 'multi-rating-pro' ), 
						MRP_Multi_Rating::DATABASE_SETTINGS_TAB => __( 'Database', 'multi-rating-pro' ), 
						MRP_Multi_Rating::FILTER_SETTINGS_TAB => __( 'Filters', 'multi-rating-pro' ),
						MRP_Multi_Rating::LICENSE_SETTINGS_TAB => __( 'License', 'multi-rating-pro' )
				);
				
				foreach ( $tabs as $tab_key => $tab_caption ) {
					$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
					echo '<a class="nav-tab ' . $active . '" href="?page=' . $page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
				} 
				?>
			</h2>
			
			<?php 
			if ( isset( $_GET['updated'] ) && isset( $_GET['page'] ) ) {
				add_settings_error( 'general', 'settings_updated', __('Settings saved.', 'multi-rating-pro' ), 'updated' );
			}
				
			settings_errors();
				
			if ( isset( $_POST['clear-database'] ) && $_POST['clear-database'] === "true" ) {
				
				global $wpdb;
				
				try {
					$rows = $wpdb->get_results( 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME.' WHERE 1' );
					$rows = $wpdb->get_results( 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME.' WHERE 1' );
					echo '<div class="updated"><p>' . __( 'Database cleared successfully.', 'multi-rating-pro' ) . '</p></div>';
				} catch ( Exception $e ) {
					echo '<div class="error"><p>' . springf( __('An error has occured. %s', 'multi-rating-pro' ), $e->getMessage() ) . '</p></div>';
				}
			}
			
			if ($current_tab == MRP_Multi_Rating::GENERAL_SETTINGS_TAB) {
				?>
				<form method="post" name="<?php echo MRP_Multi_Rating::GENERAL_SETTINGS; ?>" action="options.php">
					<?php
					wp_nonce_field( 'update-options' );
					settings_fields( MRP_Multi_Rating::GENERAL_SETTINGS );
					do_settings_sections( MRP_Multi_Rating::GENERAL_SETTINGS );
					submit_button(null, 'primary', 'submit', true, null);
					?>
				</form>
				<?php
			} else if ($current_tab == MRP_Multi_Rating::POSITION_SETTINGS_TAB) {
				?>
				<form method="post" name="<?php echo MRP_Multi_Rating::POSITION_SETTINGS; ?>" action="options.php">
					<?php
					wp_nonce_field( 'update-options' );
					settings_fields( MRP_Multi_Rating::POSITION_SETTINGS );
					do_settings_sections( MRP_Multi_Rating::POSITION_SETTINGS );
					submit_button(null, 'primary', 'submit', true, null);
					?>
				</form>
				<?php
			} else if ($current_tab == MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB) {
				?>
				<form method="post" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>" action="options.php">
					<?php
					wp_nonce_field( 'update-options' );
					settings_fields( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
					do_settings_sections( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
					submit_button(null, 'primary', 'submit', true, null);
					?>
				</form>
				<?php
			} else if ($current_tab == MRP_Multi_Rating::STYLE_SETTINGS_TAB) {
				?>
				<form method="post" name="<?php echo MRP_Multi_Rating::STYLE_SETTINGS; ?>" action="options.php">
					<?php
					wp_nonce_field( 'update-options' );
					settings_fields( MRP_Multi_Rating::STYLE_SETTINGS );
					do_settings_sections( MRP_Multi_Rating::STYLE_SETTINGS );
					submit_button(null, 'primary', 'submit', true, null);
					?>
				</form>
				<?php
			} else if ($current_tab == MRP_Multi_Rating::FILTER_SETTINGS_TAB) {
				?>
				<form method="post" name="<?php echo MRP_Multi_Rating::FILTER_SETTINGS; ?>" action="options.php">
					<?php
					wp_nonce_field( 'update-options' );
					settings_fields( MRP_Multi_Rating::FILTER_SETTINGS );
					do_settings_sections( MRP_Multi_Rating::FILTER_SETTINGS );
					submit_button(null, 'primary', 'submit', true, null);
					?>
				</form>
				<?php
			} else if ($current_tab == MRP_Multi_Rating::DATABASE_SETTINGS_TAB) {
				?>
				<form method="post" id="database-settings-form">
					<h3>Database Settings</h3>
					<input type="hidden" name="clear-database" id="clear-database" value="false" />
					<p class="description">Clear all rating results from the database.</p>
					<?php 
					submit_button( $text = __('Clear Database', 'multi-rating-pro' ), $type = 'delete', $name = 'clear-database-btn', $wrap = false, $other_attributes = null );
					?>

				</form>
				<?php
			} else if ($current_tab = MRP_Multi_Rating::LICENSE_SETTINGS_TAB) {
				$license = get_option( 'mrp_license_key' );
				$status	= get_option( 'mrp_license_status' );
				?>

				<form method="post" action="options.php">
			
					<?php settings_fields('mrp_license'); ?>
			
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row" valign="top">
									<?php _e('License Key'); ?>
								</th>
								<td>
									<input id="mrp_license_key" name="mrp_license_key" type="text" class="regular-text"	value="<?php esc_attr_e( $license ); ?>" />
									<label class="description" for="mrp_license_key"><?php _e('Enter your license key. This is used to provide automatic updates of the WordPress plugin.', 'multi-rating-pro' ); ?></label>
								</td>
							</tr>
							
							<?php if( $license ) { ?>
								<tr valign="top">
									<th scope="row" valign="top">
										<?php _e('Activate License'); ?>
									</th>
									<td>
										<?php if( $status !== false && $status == 'valid' ) { ?>
											<span style="color: green;"><?php _e( 'Active', 'multi-rating-pro' ); ?> </span>
											<?php wp_nonce_field( 'mrp_nonce', 'mrp_nonce' ); ?>
											<input type="submit" class="button-secondary" name="mrp_license_deactivate" value="<?php _e('Deactivate License', 'multi-rating-pro' ); ?>" />
										<?php } else { 
											wp_nonce_field( 'mrp_nonce', 'mrp_nonce' ); ?> 
											<input type="submit" class="button-secondary" name="mrp_license_activate" value="<?php _e('Activate License', 'multi-rating-pro' ); ?>" />
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					
					<?php submit_button(); ?>
			
				</form>
			<?php } ?>
		</div>
		<?php 
	}
	
	/**
	 * Sanitizes the license option
	 */
	function sanitize_license( $new ) {
		$old = get_option( 'mrp_license_key' );
		if( $old && $old != $new ) {
			delete_option( 'mrp_license_status' ); // new license has been entered, so must reactivate
		}
		
		return $new;
	}
	
	/**
	 * Activates the license key
	 */
	function activate_license() {
	
		// listen for our activate button to be clicked
		if ( isset( $_POST['mrp_license_activate'] ) ) {
	
			// run a quick security check
			if( ! check_admin_referer( 'mrp_nonce', 'mrp_nonce' ) )
				return; // get out if we didn't click the Activate button
	
			// retrieve the license from the database
			$license = trim( get_option( 'mrp_license_key' ) );
	
	
			// data to send in our API request
			$api_params = array(
					'edd_action'=> 'activate_license',
					'license' 	=> $license,
					'item_name' => urlencode( MRP_PLUGIN_NAME ) // the name of our product in EDD
			);
	
			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, EDD_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
	
			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;
	
			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	
			// $license_data->license will be either "active" or "inactive"
	
			update_option( 'mrp_license_status', $license_data->license );
	
		}
	}
	
	
	/**
	 *  Deactivates the license key. This will descrease the site count
	 */
	function deactivate_license() {
	
		// listen for our activate button to be clicked
		if( isset( $_POST['mrp_license_deactivate'] ) ) {
	
			// run a quick security check
			if( ! check_admin_referer( 'mrp_nonce', 'mrp_nonce' ) ) {
				return; // get out if we didn't click the Activate button
			}
			
			// retrieve the license from the database
			$license = trim( get_option( 'mrp_license_key' ) );
	
	
			// data to send in our API request
			$api_params = array(
					'edd_action'=> 'deactivate_license',
					'license' 	=> $license,
					'item_name' => urlencode( MRP_PLUGIN_NAME ) // the name of our product in EDD
			);
	
			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, EDD_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
	
			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}
	
			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	
			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' ) {
				delete_option( 'mrp_license_status' );
			}
	
		}
	}
	
	/**
	 * Shows the rating results page
	 *
	 * @since 0.1
	 */
	public function rating_results_page() {
		?>
		<div class="wrap">
			<h2 class="nav-tab-wrapper">
				<?php
				$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : MRP_Multi_Rating::POST_RATING_RESULTS_TAB;
				$page = MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG;
				$tabs = array (MRP_Multi_Rating::POST_RATING_RESULTS_TAB => __( 'Rating Results', 'multi-rating-pro' ), 
						MRP_Multi_Rating::RATING_RESULTS_TAB => __( 'Entries', 'multi-rating-pro' ), 
						MRP_Multi_Rating::RATING_RESULT_DETAILS_TAB => __( 'Entry Values', 'multi-rating-pro' )
				);
				
				foreach ( $tabs as $tab_key => $tab_caption ) {
					$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
					echo '<a class="nav-tab ' . $active . '" href="?page=' . $page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
				} 
				?>
			</h2>
			<?php 
			
			if ($current_tab == MRP_Multi_Rating::POST_RATING_RESULTS_TAB) {
				?>
				<form method="post" id="post-rating-results-table-form">
					<?php 
					$post_summary_table = new MRP_Post_Rating_Results_Table();
					$post_summary_table->prepare_items();
					$post_summary_table->display();
					?>
				</form>
				<?php 
			} else if ($current_tab == MRP_Multi_Rating::RATING_RESULTS_TAB) {
				?>
				<form method="post" id="rating-item-entry-table-form" action="?page=<?php echo MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG; ?>&tab=<?php echo MRP_Multi_Rating::RATING_RESULTS_TAB; ?>">
					<?php 
					$rating_item_entry_table = new MRP_Rating_Item_Entry_Table();
					$rating_item_entry_table->prepare_items();
					$rating_item_entry_table->display();
					?>
				</form>
				<?php 
			} else if ($current_tab == MRP_Multi_Rating::RATING_RESULT_DETAILS_TAB) {
				?>
				<form method="post" id="rating-item-entry-value-table-form">
					<?php 
					$rating_item_entry_value_table = new MRP_Rating_Item_Entry_Value_Table();
					$rating_item_entry_value_table->prepare_items();
					$rating_item_entry_value_table->display();
					?>
				</form>
				<?php
			}
			?>
		</div>
		<?php 
	}
		
	/**
	 * Shows the rating items page
	 *
	 * @since 0.1
	 */
	public function rating_items_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Rating Items', 'multi-rating-pro' ); ?><a class="add-new-h2" href="admin.php?page=<?php echo MRP_Multi_Rating::ADD_NEW_RATING_ITEM_PAGE_SLUG; ?>"><?php _e( 'Add New', 'multi-rating-pro' ); ?></a></h2>
			<form method="post" id="rating-item-table-form">
				<?php 
				$rating_item_table = new MRP_Rating_Item_Table();
				$rating_item_table->prepare_items();
				$rating_item_table->display();
				?>
			</form>
		</div>
		<?php 
	}
	
	/**
	 * Adds a new ratin item and performs validation checks
	 */
	public function add_new_rating_item() {
		
		$error_message = '';
		$success_message = '';
			
		if ( isset( $_POST['desciption'] ) && isset( $_POST['max-option-value'] ) 
				&& isset( $_POST['default-option-value'] ) && isset( $_POST['option_value_text'] )) {

			$description = $_POST['desciption'];
			if ( strlen(trim( $description ) ) == 0 ) {
				$error_message .= __( 'Description cannot be empty. ', 'multi-rating-pro' );
			}
			
			$type = $_POST['type'];
			if ( strlen( trim( $type ) ) == 0 ) {
				$type = MRP_Multi_Rating::SELECT_ELEMENT; 
			}
			
			// TODO if type is thumbs them set max option value to 1

			if ( is_numeric( $_POST['max-option-value'] ) == false ) {
				$error_message .= __( 'Max option value cannot be empty and must be a whole number. ', 'multi-rating-pro' );
			}

			if ( is_numeric( $_POST['default-option-value'] ) == false ) {
				$error_message .= __( 'Default option value cannot be empty and must be a whole number. ', 'multi-rating-pro' );
			}
			
			$max_option_value = intval( $_POST['max-option-value'] );
			$default_option_value = intval( $_POST['default-option-value'] );
			$weight = doubleval( $_POST['weight'] );
			
			if ( $default_option_value > $max_option_value ) {
				$error_message .= __( 'Default option value cannot be greater than the max option value. ', 'multi-rating-pro' );
			}
			
			if ( $type == 'thumbs' ) {
				// set max option value and default option value for thumbs up/down if required
				$max_option_value = 1;
				if ( $default_option_value > 1 ) {
					$default_option_value = 1;
				}
			}
			
			$option_value_text = $_POST['option_value_text'];
			$error_message .= MRP_Utils::validate_option_value_text( $option_value_text, $max_option_value );

			$include_zero = false;
			if ( isset( $_POST['include-zero'] ) && $_POST['include-zero'] == 'true' ) {
				$include_zero = true;
			}

			if ( strlen( $error_message ) == 0 ) {
				global $wpdb;
					
				$results = $wpdb->insert(  $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
						'description' => $description,
						'max_option_value' => $max_option_value,
						'default_option_value' => $default_option_value,
						'weight' => $weight,
						'option_value_text' => $_POST['option_value_text'],
						'type' => $type,
						'include_zero' => $include_zero
				) );
					
				if ( isset( $_POST['add-to-default'] ) && $_POST['add-to-default'] == 'on' ) {
					$rating_item_id = $wpdb->insert_id;
					
					// get rating form id from settings
					$default_rating_form_id = $this->general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
					
					$rating_form_query = 'SELECT rating_form_id, rating_items FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME . ' WHERE rating_form_id = "' . $default_rating_form_id . '"';
					$rating_form = $wpdb->get_row( $rating_form_query, ARRAY_A, 0 );
					
					// add item to existing rating items from rating form
					$rating_items = $rating_item_id . ', ' . $rating_form[MRP_Rating_Form_Table::RATING_ITEMS_COLUMN];
					
					$result = $wpdb->query( 'UPDATE '.$wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME.' SET ' . MRP_Rating_Form_Table::RATING_ITEMS_COLUMN . ' = "' . $rating_items . '" WHERE ' . MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN .' = "' . $rating_form[MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN] . '"' );
					
					if ($result === FALSE) {
						$error_message .= __( 'An error occured adding rating item to default rating form. ', 'multi-rating-pro' );
					}
				}
				
				$success_message .= __( 'Rating item added successfully.', 'multi-rating-pro' );
			}
		} else {
			$error_message .= __( 'An error occured. Rating item could not be added.', 'multi-rating-pro' );
		}
			
		if ( strlen( $error_message ) > 0) {
			echo '<div class="error"><p>' . $error_message . '</p></div>';
		}
		
		if ( strlen( $success_message ) > 0) {
			echo '<div class="updated"><p>' . $success_message . '</p></div>';
		}
	}
	
	/**
	 * Add new rating item page
	 */
	public function add_new_rating_item_page() {
		
		if ( isset( $_POST['form-submitted'] ) && $_POST['form-submitted'] === "true" ) {
			$this->add_new_rating_item();
		}
		
		?>
		<div class="wrap">
			<h2><?php _e(' Add New Rating Item', 'multi-rating-pro' ); ?></h2>
		
			<form method="post" id="add-new-rating-item-form">
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><?php _e( 'Description', 'multi-rating-pro' ); ?></th>
							<td>
								<textarea id="desciption" name="desciption" type="text" maxlength="255" cols="100" placeholder="<?php _e( 'Enter description.', 'multi-rating-pro' ); ?>"></textarea>	
								<p class="description"><?php _e( 'Enter a rating item description.', 'multi-rating-pro' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Type', 'multi-rating-pro' ); ?></th>
							<td>
								<select name="type" id="type">
									<option value="select"><?php _e( 'Select', 'multi-rating-pro' ); ?></option>
									<option value="radio"><?php _e( 'Radio', 'multi-rating-pro' ); ?></option>
									<option value="star_rating"><?php _e( 'Star Rating', 'multi-rating-pro' ); ?></option>
									<option value="thumbs"><?php _e( 'Thumbs', 'multi-rating-pro' ); ?></option>
								</select>
								<p class="type"><?php _e( 'Do you want to use a select drop-down list, radio buttons star rating icons or thumbs up/down icons from Font Awesome?', 'multi-rating-pro' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Max Option Value', 'multi-rating-pro' ); ?></th>
							<td>
								<input id="max-option-value" name="max-option-value" type="text" value="5" placeholder="<?php _e( 'Enter max option value', 'multi-rating-pro' ); ?>" />
								<p class="description"><?php _e( 'If the max option value is set to 5, then the rating item options would be 0, 1, 2, 3, 4 and 5.', 'multi-rating-pro' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Default Option Value', 'multi-rating-pro' ); ?></th>
							<td>
								<input id="default-option-value" name="default-option-value" type="text" value="5" placeholder="<?php _e( 'Enter default option value', 'multi-rating-pro' ); ?>" />
								<p class="description"><?php _e( 'This is used to default the selected option value.', 'multi-rating-pro' ); ?></p>	
							</td>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Option Value Text', 'multi-rating-pro' ); ?></th>
							<td>
								<textarea id="option_value_text" name="option_value_text" cols="30" rows="10" value="" placeholder="<?php _e( 'Enter option value text.', 'multi-rating-pro' ); ?>" ></textarea>
								<p class="description"><?php _e( 'Add text descriptions to be displayed for select and radio options. Each option value and text decription must be on a newline. Format = <code>value=description</code>. If a text description is not provided for an option value, the value will be displayed. e.g. <br />0=Needs Improvement<br />1=Good<br />2=Very good', 'multi-rating-pro' ); ?></p>	
							</td>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Weight', 'multi-rating-pro' ); ?></th>
							<td>
								<input id="weight" name="weight" type="text" value="1" placeholder="<?php _e( 'Enter weight', 'multi-rating-pro' ); ?>"/>
								<!-- TODO <input id="weight" name="weight"  type="range" name="points" min="0.25" max="10" step="0.25" value="1"> -->
								<p class="description"><?php _e( 'All rating items are rated equally by default. Modifying the weight of a rating item will adjust the rating results accordingly. Decimal values can be used.', 'multi-rating-pro' ); ?></p>	
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Include Zero', 'multi-rating-pro' ); ?></th>
							<td>
								<input type="checkbox" name="include-zero" checked="checked" value="true" />
								<p class="description"><?php _e( 'Do you want 0 to be included as an option value? For star ratings, this means allowing empty.', 'multi-rating-pro' ); ?></p>	
							</td>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Add to default rating form', 'multi-rating-pro' ); ?></th>
							<td>
								<input id="add-to-default" name="add-to-default" type="checkbox" checked="checked" />	
							</td>
						</tr>
					</tbody>
				</table>
				
				<input id="add-new-rating-item-btn" class="button button-primary" value="<?php _e( 'Add New Rating Item', 'multi-rating-pro' ); ?>" type="submit" />
				<input type="hidden" id="form-submitted" name="form-submitted" value="false" />
			</form>
		</div>
		<?php
	}
	
	/**
	 * Add new rating form
	 */
	public function add_new_rating_form() {
		
		$error_message = '';
		$success_message = '';
			
		if ( isset( $_POST['name'] ) && isset( $_POST['rating_items'] ) ) {
		 
			$name = $_POST['name'];
			if ( strlen(trim( $name ) ) == 0 ) {
				$error_message .= __( 'Name cannot be empty. ', 'multi-rating-pro' );
			}
		
			$rating_items = $_POST['rating_items'];
			$error_message .= MRP_Utils::validate_rating_items_text( $rating_items );
		
			if ( strlen( $error_message ) == 0 ) {
				global $wpdb;
					
				$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME, array(
						'name' => $name,
						'rating_items' => $rating_items
				) );
					
				$success_message .= __( 'Rating form added successfully.', 'multi-rating-pro' );
			}
		} else {
			$error_message .= __( 'An error occured. Rating form could not be added.', 'multi-rating-pro' );
		}
			
		if ( strlen( $error_message ) > 0) {
			echo '<div class="error"><p>' . $error_message . '</p></div>';
		}
		
		if ( strlen( $success_message ) > 0) {
			echo '<div class="updated"><p>' . $success_message . '</p></div>';
		}
		
	}
	
	/**
	 * Add new rating form page
	 */
	public function add_new_rating_form_page() {
	
		if ( isset( $_POST['form-submitted'] ) && $_POST['form-submitted'] === "true" ) {
			$this->add_new_rating_form();
		}
	
		?>
			<div class="wrap">
				<h2><?php _e( 'Add New Rating Form', 'multi-rating-pro' ); ?></h2>
			
				<form method="post" id="add-new-rating-form-form">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><?php _e( 'Name', 'multi-rating-pro' ); ?></th>
								<td>
									<input type="text" class="regular-text" id="name" name="name" value=""/>	
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Rating items', 'multi-rating-pro' ); ?></th>
								<td>
									<input type="text" id="rating_items" name="rating_items" class="regular-text" value="" >
									<p class="description"><?php _e( 'Enter a comma separated list of Rating Item Id\'s e.g. 1,2,3 etc... This can be done later.', 'multi-rating-pro' ); ?></p>	
								</td>
								</td>
							</tr>
						</tbody>
					</table>
					
					<input id="add-new-rating-form-btn" class="button button-primary" value="<?php _e( 'Add New Rating Form', 'multi-rating-pro' ); ?>" type="submit" />
					<input type="hidden" id="form-submitted" name="form-submitted" value="false" />
				</form>
			</div>
			<?php
		}

	/**
	 * Javascript and CSS used by the plugin
	 *
	 * @since 0.1
	 */
	public function admin_assets(){
		
		wp_enqueue_script( 'jquery' );
		
		$config_array = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( MRP_Multi_Rating::ID.'-nonce' )
		);

		wp_enqueue_script( 'mrp_-admin-script', plugins_url( 'js' . DIRECTORY_SEPARATOR . 'admin.js', __FILE__), array('jquery'), MRP_Multi_Rating::VERSION, true );
		wp_localize_script( 'mrp_-admin-script', 'mrp_admin_data', $config_array );

		wp_enqueue_script( 'mrp_-frontend-script', plugins_url( 'js' . DIRECTORY_SEPARATOR . 'frontend.js', __FILE__), array('jquery'), MRP_Multi_Rating::VERSION, true );
		wp_localize_script( 'mrp_-frontend-script', 'mrp_frontend_data', $config_array );
		
		// Add simple table CSS for rating form
		wp_enqueue_style( 'mrp_-frontend-style', plugins_url( 'css' . DIRECTORY_SEPARATOR . 'frontend.css', __FILE__ ) );
		wp_enqueue_style( 'mrp_-admin-style', plugins_url( 'css' . DIRECTORY_SEPARATOR . 'admin.css', __FILE__ ) );
		
		// flot
		wp_enqueue_script( 'flot', plugins_url( 'js' . DIRECTORY_SEPARATOR . 'flot' . DIRECTORY_SEPARATOR . 'jquery.flot.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'flot-categories', plugins_url( 'js' . DIRECTORY_SEPARATOR . 'flot' . DIRECTORY_SEPARATOR . 'jquery.flot.categories.js', __FILE__ ), array( 'jquery', 'flot' ) );
		wp_enqueue_script( 'flot-time', plugins_url( 'js' . DIRECTORY_SEPARATOR . 'flot' . DIRECTORY_SEPARATOR . 'jquery.flot.time.js', __FILE__ ), array( 'jquery', 'flot' ) );
		wp_enqueue_script( 'flot-selection', plugins_url( 'js' . DIRECTORY_SEPARATOR . 'flot' . DIRECTORY_SEPARATOR . 'jquery.flot.selection.js', __FILE__ ), array( 'jquery', 'flot', 'flot-time' ) );
		
		// color picker
		wp_enqueue_style( 'wp-color-picker' );          
    	wp_enqueue_script( 'wp-color-picker' );
		
    	// date picker
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
		
	}

	/**
	 * Javascript and CSS used by the plugin
	 *
	 * @since 0.1
	 */
	public function assets(){
		
		wp_enqueue_script( 'jquery' );
		
		// Add simple table CSS for rating form
		wp_enqueue_style( 'mrp_-frontend-style', plugins_url( 'css' . DIRECTORY_SEPARATOR . 'frontend.css', __FILE__ ) );
		
		// Allow support for other versions of Font Awesome
		$include_font_awesome = $this->style_settings[MRP_Multi_Rating::INCLUDE_FONT_AWESOME_OPTION];
		$font_awesome_version = $this->style_settings[MRP_Multi_Rating::FONT_AWESOME_VERSION_OPTION];
		
		$icon_classes = MRP_Utils::get_icon_classes( $font_awesome_version );
		
		if ( $include_font_awesome ) {
			if ( $font_awesome_version == '4.0.3' ) {
				wp_enqueue_style( 'fontawesome', 'http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );
			} else if ( $font_awesome_version == '3.2.1' ) {
				wp_enqueue_style( 'fontawesome', 'http://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css' );
			} else if ( $font_awesome_version == '4.1.0' ) {
				wp_enqueue_style( 'fontawesome', 'http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css' );
			}
		}
		
		$config_array = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( MRP_Multi_Rating::ID.'-nonce' ),
				'icon_classes' => json_encode( $icon_classes )
		);
		
		wp_enqueue_script( 'mrp_-frontend-script', plugins_url( 'js' . DIRECTORY_SEPARATOR . 'frontend.js', __FILE__), array( 'jquery' ), MRP_Multi_Rating::VERSION, true );
		wp_localize_script( 'mrp_-frontend-script', 'mrp_frontend_data', $config_array );
	}
	
	
	/**
	 * Register AJAX actions
	 */
	public function add_ajax_actions() {
		add_action( 'wp_ajax_save_rating', array( $this, 'save_rating' ) );
		add_action( 'wp_ajax_nopriv_save_rating', array( $this, 'save_rating' ) );
		
		add_action( 'wp_ajax_delete_rating', array( $this, 'delete_rating' ) );
		add_action( 'wp_ajax_nopriv_delete_rating', array( $this, 'delete_rating' ) );
		
		add_action( 'wp_ajax_nopriv_save_rating_item_table_column', array( 'MRP_Rating_Item_Table', 'save_rating_item_table_column' ) );
		add_action( 'wp_ajax_save_rating_item_table_column', array( 'MRP_Rating_Item_Table', 'save_rating_item_table_column' ) );
		
		add_action( 'wp_ajax_nopriv_save_rating_form_table_column', array( 'MRP_Rating_Form_Table', 'save_rating_form_table_column' ) );
		add_action( 'wp_ajax_save_rating_form_table_column', array( 'MRP_Rating_Form_Table', 'save_rating_form_table_column' ) );
	}
	
	/**
	 * Deletes a rating form entry
	 */
	public function delete_rating() {
		
		$ajax_nonce = $_POST['nonce'];
		if ( wp_verify_nonce( $ajax_nonce, self::ID.'-nonce' ) ) {
			global $wpdb;
			
			// check parameters are OK to be passed to  query
			if ( ! isset( $_POST['postId'] ) || ( isset( $_POST['postId'] ) && !is_numeric( $_POST['postId'] ) )
					|| ! isset( $_POST['ratingFormId'] ) || ( isset($_POST['ratingFormId']) && ! is_numeric($_POST['ratingFormId'] ) )
					|| ! isset( $_POST['ratingItemEntryId'] ) || ( isset($_POST['ratingItemEntryId']) && ! is_numeric($_POST['ratingItemEntryId'] ) ) ) {
				echo __( 'An error has occured.', 'multi-rating-pro' );
				die();
			}
			
			$post_id = $_POST['postId'];
			$rating_form_id = $_POST['ratingFormId'];
			$rating_item_entry_id = $_POST['ratingItemEntryId'];
			
			// get username
			global $wp_roles;
			$current_user = wp_get_current_user();
			$username = $current_user->user_login;
			
			// check if user belongs to rating item entry id
			if ( strlen( $username ) == 0 || ( strlen( $username ) > 0
					 && ! MRP_Multi_Rating_API::has_user_already_submitted_rating_form( $rating_form_id, $post_id, $username ) ) ) {
				echo __('You cannot delete a rating you did not submit or the rating may already be deleted.', 'multi-rating-pro' );
				die();
			}
			
			$rows = $wpdb->get_results( 'DELETE FROM ' . $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME.' WHERE rating_item_entry_id = "' . $rating_item_entry_id . '"' );
			$rows = $wpdb->get_results( 'DELETE FROM ' . $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' WHERE rating_item_entry_id = "' . $rating_item_entry_id . '"' );
			
			$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
			echo $custom_text_settings[ MRP_Multi_Rating::DELETE_RATING_SUCCESS_MESSAGE_OPTION];
		}
		
		die();
	}
	
	/**
	 * Saves a rating form entry. If rating item entry is present then we're updating an existing rating.
	 */
	public function save_rating() {
		
		// TODO return status, message and optional field in AJAX response instead of just a message
		
		$ajax_nonce = $_POST['nonce'];
		if ( wp_verify_nonce($ajax_nonce, self::ID.'-nonce' ) ) {
			global $wpdb;
			
			$rating_items = $_POST['ratingItems'];
			$post_id = $_POST['postId'];
			$rating_form_id = $_POST['ratingFormId'];			
			$ip_address = MRP_Utils::get_ip_address();
			$entry_date_mysql = current_time( 'mysql' );
			
			$name = isset($_POST['name']) ? $_POST['name'] : '';
			$email = isset($_POST['email']) ?  $_POST['email'] : '';
			$comment = isset($_POST['comment']) ? $_POST['comment'] : '';
			$rating_item_entry_id = isset( $_POST['ratingItemEntryId'] ) ? $_POST['ratingItemEntryId'] : null;
			
			if ( strlen( $name ) > 100 ) {
				echo __( 'Name cannot be greater than 100 characters.', 'multi-rating-pro' );
				die();
			}
			if ( strlen( $email ) > 255 ) {
				echo __( 'E-mail cannot be greater than 255 characters.', 'multi-rating-pro' );
				die();
			}
			if ( strlen( $email ) > 0 && ! is_email( $email ) ) {
				echo __( 'E-mail is invalid.', 'multi-rating-pro' );
				die();
			}
			if ( strlen( $comment ) > 1020 ) {
				echo __( 'Comments cannot be greater than 1020 characters.', 'multi-rating-pro' );
				die();
			}
			
			$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
			
			// get username
			global $wp_roles;
			$current_user = wp_get_current_user();
			$username = $current_user->user_login;

			if ( $rating_item_entry_id == null ) { // submit rating
				
				// if allow anonymous ratings not specified in post meta, use default settings
				$allow_anonymous_ratings = get_post_meta( $post_id, MRP_Multi_Rating::ALLOW_ANONYMOUS_POST_META, true );
				
				if ( $allow_anonymous_ratings === "" ) { // note ("" == false) = true
					$allow_anonymous_ratings = $this->general_settings[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION];
				} else {
					$allow_anonymous_ratings = $allow_anonymous_ratings == "true" ? true : false;
				}
				
				if ( $allow_anonymous_ratings == false && strlen( trim( $username ) ) == 0 ) {
					echo $custom_text_settings[ MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_FAILURE_MESSAGE_OPTION ];
					die();
				}
				
				// check ip address date/time validation option
				$ip_address_datetime_validation = $this->general_settings[MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION];
				if ($ip_address_datetime_validation == true) {
					
					// check IP address has not submitted a rating for the post ID within a duration of time
					// TODO remove duration option
					$ip_address_datetime_validation_days_duration = $this->general_settings[MRP_Multi_Rating::IP_ADDRESS_DATE_VALIDATION_DAYS_DURATION_OPTION];
					$previous_day_date = strtotime( $entry_date_mysql ) - ( $ip_address_datetime_validation_days_duration * 1 * 24 * 60 * 60 );
					$previous_day_date_mysql = date( 'Y-m-d H:i:s', $previous_day_date );
					
					$ip_address_check_query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE ip_address = "' . $ip_address . '" AND post_id ='
							. $post_id . ' AND rating_form_id = "' . $rating_form_id . '" AND entry_date >= "' . $previous_day_date_mysql . '"';
					$rows = $wpdb->get_results( $ip_address_check_query );
	
					if ( count( $rows ) > 0 ) {
						echo $custom_text_settings[ MRP_Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION ];
						die();
					}
				}			
		
				if ( strlen( $username ) > 0 && MRP_Multi_Rating_API::has_user_already_submitted_rating_form( $rating_form_id, $post_id, $username ) ) {
					echo $custom_text_settings[ MRP_Multi_Rating::ALREADY_SUBMITTED_RATING_FORM_MESSAGE_OPTION ];
					die();
				}
				
				// check include zero
				foreach ( $rating_items as $rating_item ) {
					$rating_item_id = $rating_item['id'];
					$rating_item_value = $rating_item['value'];
					
					if ( $rating_item_value == 0 ) {
						$query = 'SELECT include_zero FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME . ' WHERE rating_item_id = "' . $rating_item_id . '"';
						$include_zero = $wpdb->get_col( $query, 0 );
						
						if ( $include_zero[0] == false ) {
							echo __( 'An error occured', 'multi-rating-pro' );
							die();
						}
					}
				}
				
				// everything is OK so now insert the rating form entry and entry values into the database tables
				$wpdb->insert( $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
						'post_id' => $post_id,
						'rating_form_id' => $rating_form_id,
						'entry_date' => $entry_date_mysql,
						'ip_address' => $ip_address,
						'username' => $username,
						'name' => $name,
						'email' => $email,
						'comment' => esc_textarea( $comment )
				), array('%s', '%s', '%s', '%s') );
				
				$rating_item_entry_id = $wpdb->insert_id;
				
				foreach ( $rating_items as $rating_item ) {
					
					$rating_item_id = $rating_item['id'];
					$rating_item_value = $rating_item['value'];
					
					$wpdb->insert( $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array( 
							'rating_item_entry_id' => $rating_item_entry_id, 
							'rating_item_id' => $rating_item_id,
							'value' => $rating_item_value
						), array('%d', '%d', '%d') );
				}
				
				echo $custom_text_settings[ MRP_Multi_Rating::SUBMIT_RATING_SUCCESS_MESSAGE_OPTION ];
			
			} else { // update rating
				
				// check include zero
				foreach ( $rating_items as $rating_item ) {
					$rating_item_id = $rating_item['id'];
					$rating_item_value = $rating_item['value'];
					
					if ( $rating_item_value == 0 ) {
						$query = 'SELECT include_zero FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME . ' WHERE rating_item_id = "' . $rating_item_id . '"';
						$include_zero = $wpdb->get_col($query, 0);

						if ( $include_zero[0] == false ) {
							echo __( 'An error occured', 'multi-rating-pro' );
							die();
						}
					}
				}
				
				// check if user belongs to rating item entry id
				if ( strlen( $username ) > 0 && MRP_Multi_Rating_API::has_user_already_submitted_rating_form( $rating_form_id, $post_id, $username ) ) {
					
					$wpdb->update( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
							'name' => $name,
							'email' => $email,
							'comment' => $comment,
							'ip_address' => $ip_address,
							'entry_date' => $entry_date_mysql
					), array('rating_item_entry_id' => $rating_item_entry_id) );
					
					foreach ( $rating_items as $rating_item ) {
						
						$rating_item_id = $rating_item['id'];
						$rating_item_value = $rating_item['value'];
					
						$wpdb->update( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array( 'value' => $rating_item_value ), array(
								'rating_item_entry_id' => $rating_item_entry_id,
								'rating_item_id' =>	 $rating_item_id
						) );
					}
					
					echo $custom_text_settings[ MRP_Multi_Rating::UPDATE_RATING_SUCCESS_MESSAGE_OPTION];
					
				} else {
					echo __( 'You cannot update a rating form entry you did not submit.', 'multi-rating-pro' );
					die();
				}
			}
				
			// show rating result after rating form submit
			$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
			$show_rating_result_after_submit = $general_settings[MRP_Multi_Rating::SHOW_RATING_RESULT_AFTER_SUBMIT_OPTION];
			
			if ( $show_rating_result_after_submit == true ) {
				$rating_result = MRP_Multi_Rating_API::calculate_rating_item_entry_result( $rating_item_entry_id );
				
				// TODO provide substitutions e.g. %count%, %star_rating_result%, $percentage_rating_result%, %max_option_value% etc... 
				echo sprintf( __( 'Your rating was %s/5', 'multi-rating-pro' ), $rating_result['adjusted_star_result'] ); 
			}
		}
		
		die();
	
	}
	
	function add_custom_css() {
		?>
		<style type="text/css">
			<?php echo $this->style_settings[MRP_Multi_Rating::CUSTOM_CSS_OPTION]; ?>
			
			<?php 
			$style_settings = (array) get_option( MRP_Multi_Rating::STYLE_SETTINGS );
			$star_rating_colour = $style_settings[MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION];
			$star_rating_hover_colour = $style_settings[MRP_Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION];
			?>
			
			.mrp-star-hover {
				color: <?php echo $star_rating_hover_colour; ?> !important;
			}
			.mrp-star-full, .mrp-star-half, .mrp-star-empty, .mrp-thumbs-up-on, .mrp-thumbs-up-off, .mrp-thumbs-down-on, .mrp-thumbs-down-off {
				color: <?php echo $star_rating_colour; ?>;
			}
		</style>
		<?php 
	}
	
	
	/**
	 * About page
	 */
	function about_page() {
		?>
		<div class="wrap about-wrap">

			<h1><?php printf( __( 'Multi Rating Pro v%s', 'multi-rating-pro' ), MRP_Multi_Rating::VERSION ); ?></h1>
			
			<div class="about-text"><?php _e( 'Provides advanced features to the free Multi Rating plugin.', 'multi-rating-pro' ); ?></div>

			<h2 class="nav-tab-wrapper">
				<?php
				$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'support';
				$page = MRP_Multi_Rating::ABOUT_PAGE_SLUG;
				$tabs = array (
						'support' => __( 'Support', 'multi-rating-pro' ),
						'documentation' => __( 'Documentation', 'multi-rating-pro' ),
						'affiliates' => __( 'Affiliates', 'multi-rating-pro' )
				);
				
				foreach ( $tabs as $tab_key => $tab_caption ) {
					$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
					
					echo '<a class="nav-tab ' . $active . '" href="?page=' . $page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
				} ?>
			</h2>
			
			<?php 
		if ( $current_tab == 'support') {
			?>
			<p><?php printf( __( 'Please use the <a href="%1$s">contact form</a> for all Multi Rating Pro plugin support. '
					. 'Please check the <a href="%2$s">documentation</a> available first before submitting a technical support request. '
					. 'Please note that the WordPress.org support forum is not to be used for premium plugin support.', 'multi-rating-pro' ),
					'http://danielpowney.com/contact/', 
					'http://danielpowney.com/documentation/' ); 
			?></p>
			<?php
		} else if ( $current_tab == 'documentation' ) { // Documentation
			?>
			<p><?php printf( __( 'All plugin documentation is available <a href="%1$s">here</a>', 'multi-rating-pro' ),
					'http://danielpowney.com/documentation/'  ); 
			?></p>
			<?php
		} else  { // Documentation
			?>
			<p><?php printf( __( '<a href="%1$s">Register as an affiliate</a> and tell the world about Multi Rating Pro.', 'multi-rating-pro' ),
					'http://danielpowney.com/affiliate-area/'  ); 
			?></p>
			<?php
		}
	}
}

/**
 * Activate plugin
 */
function mrp_activate_plugin() {
	
	if ( is_admin() ) {
		add_option(MRP_MULTI_RATING::DO_ACTIVATION_REDIRECT_OPTION, true);
		MRP_Multi_Rating::activate_plugin();
	}
}
/**
 * Uninstall plugin
 */
function mrp_uninstall_plugin() {
	
	if ( is_admin() ) {
		MRP_Multi_Rating::uninstall_plugin();
	}
}

// Activation and deactivation
register_activation_hook( __FILE__, 'mrp_activate_plugin');
register_uninstall_hook( __FILE__, 'mrp_uninstall_plugin' );

$multi_rating = new MRP_Multi_Rating();


/**
 * Add plugin footer to admin dashboard
 *
 * @param       string $footer_text The existing footer text
 * @return      string
 */
function mrp_plugin_footer( $footer_text ) {

	$current_screen = get_current_screen();

	if ( $current_screen->parent_base == MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG ) {
		$plugin_footer = sprintf( __( 'Thank you for using <a href="%1$s" target="_blank">Multi Rating Pro</a>!', 'multi-rating-pro' ), 'http://danielpowney.com/downloads/multi-rating-pro' );

		return $plugin_footer . '<br />' . $footer_text;

	} else {
		return $footer_text;
	}
}
add_filter( 'admin_footer_text', 'mrp_plugin_footer' );

/**
 * Add to the WordPress version
 * 
 * @param $default
 */
function mrp_footer_version ( $default ) {
	
	$current_screen = get_current_screen();

	if ( $current_screen->parent_base == MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG ) {
		return 'Multi Rating Pro v' . MRP_Multi_Rating::VERSION . '<br />' . $default;
	}

	return $default;
}
add_filter ('update_footer', 'mrp_footer_version', 999);