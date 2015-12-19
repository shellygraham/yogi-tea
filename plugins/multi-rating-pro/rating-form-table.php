<?php
if( ! class_exists( 'WP_List_Table' ) ){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * MRP_Rating_Form_Table class
 * @author dpowney
 *
 */
class MRP_Rating_Form_Table extends WP_List_Table {

	const
	NAME_COLUMN = 'name',
	IS_DEFAULT_COLUMN = 'is_default',
	RATING_ITEMS_COLUMN = 'rating_items',
	CHECKBOX_COLUMN = 'cb',
	RATING_FORM_ID_COLUMN = 'rating_form_id',
	DELETE_CHECKBOX = 'delete[]';

	/**
	 * Constructor
	 */
	function __construct() {
		
		parent::__construct( array(
				'singular'=> __( 'Rating Forms', 'multi-rating-pro' ),
				'plural' => __( 'Rating Forms', 'multi-rating-pro' ),
				'ajax'	=> false
		) );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::extra_tablenav()
	 */
	function extra_tablenav( $which ) {
		
		if ( $which == 'top' ){
			echo "";
		}
		
		if ( $which == 'bottom' ){
			echo "";
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns() {
		
		return $columns= array(
				MRP_Rating_Form_Table::CHECKBOX_COLUMN => '<input type="checkbox" />',
				MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN =>__( 'Rating Form Id', 'multi-rating-pro' ),
				MRP_Rating_Form_Table::NAME_COLUMN => __( 'Name', 'multi-rating-pro' ),
				MRP_Rating_Form_Table::RATING_ITEMS_COLUMN => __( 'Rating Items', 'multi-rating-pro' ) . '<br /><span class="description">' . __( '(Comma separated list of Rating Item Id\'s)', 'multi-rating-pro' ) . '</span>',
				MRP_Rating_Form_Table::IS_DEFAULT_COLUMN => __( 'Default', 'multi-rating-pro' ),
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::prepare_items()
	 */
	function prepare_items() {
		global $wpdb;
		
		// Process any bulk actions first
		$this->process_bulk_action();

		// Register the columns
		$columns = $this->get_columns();
		$hidden = array(  );
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$query = 'SELECT * FROM ' . $wpdb->prefix.MRP_Multi_Rating::RATING_FORM_TBL_NAME;
		
		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Default column
	 * @param unknown_type $item
	 * @param unknown_type $column_name
	 * @return unknown|mixed
	 */
	function column_default( $item, $column_name ) {
		
		switch( $column_name ) {
			case MRP_Rating_Form_Table::CHECKBOX_COLUMN :
			case MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN :
				return $item[ $column_name ];
				break;
				
			case MRP_Rating_Form_Table::RATING_ITEMS_COLUMN :
				$this->column_rating_items( $item, $column_name );
				break;
				
			case MRP_Rating_Form_Table::IS_DEFAULT_COLUMN :
				$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
				$default_rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
				if ($item[ MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN ] == $default_rating_form_id) {
					echo __( 'Yes', 'multi-rating-pro' );
				} else { 
					echo __( 'No', 'multi-rating-pro' );
				}
				break;
				
			case MRP_Rating_Form_Table::NAME_COLUMN:
				$this->column_actions( $item, $column_name );
				break;
				
			default:
				return print_r( $item, true ) ;
		}
	}
	
	/**
	 * checkbox column
	 * @param unknown_type $item
	 * @return string
	 */
	function column_cb( $item ) {
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$default_rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
		if ( $item[ MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN ] == $default_rating_form_id ) {
			return;
		}
		
		return sprintf(
				'<input type="checkbox" name="'.MRP_Rating_Form_Table::DELETE_CHECKBOX.'" value="%s" />', $item[MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN]
		);
	}

	/**
	 * Actions
	 * @param unknown_type $item
	 * @param unknown_type $column_name
	 */
	function column_actions( $item, $column_name ) {
		$row_id = $item[MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN];
		$row_value = stripslashes( $item[$column_name] );
		$edit_btn_id = 'edit-'.$column_name.'-'.$row_id;
		$save_btn_id = 'save-'.$column_name.'-'.$row_id;
		$view_section_id = 'view-section-'. $column_name . '-'. $row_id;
		$edit_section_id = 'edit-section-'. $column_name . '-'. $row_id;
		$input_id = 'field-'. $column_name . '-'. $row_id;
		$text_id = 'text-'. $column_name . '-'. $row_id;
		echo '<div id="' .$view_section_id.'"><div id="'.$text_id.'">'.$row_value.'</div><div class="row-actions"><a href="#" id="'.$edit_btn_id.'">' . __( 'Edit', 'multi-rating-pro' ) . '</a></div></div>';
		echo '<div id="'.$edit_section_id.'" style="display: none;"><input type="text" id="'.$input_id.'" value="'.$row_value.'" style="width: 100%;" /><div class="row-actions"><a href="#" id="'.$save_btn_id.'">' . __( 'Save', 'multi-rating-pro' ) . '</a></div></div>';	
	}
	
	/**
	 * Rating items
	 * @param unknown_type $item
	 */
	function column_rating_items($item) {
		$column_name = MRP_Rating_Form_Table::RATING_ITEMS_COLUMN;
	
		$row_id = $item[MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN];
	
		// row value for textarea needs be be translated to HTML
		$rating_items =  $item[ $column_name ];
	
		$edit_btn_id = 'edit-'.$column_name.'-'.$row_id;
		$save_btn_id = 'save-'.$column_name.'-'.$row_id;
		$view_section_id = 'view-section-'. $column_name . '-'. $row_id;
		$edit_section_id = 'edit-section-'. $column_name . '-'. $row_id;
		$input_id = 'field-'. $column_name . '-'. $row_id;
		$text_id = 'text-'. $column_name . '-'. $row_id;
		echo '<div id="' .$view_section_id.'"><div id="'.$text_id.'">'.$rating_items.'</div><div class="row-actions"><a href="#" id="'.$edit_btn_id.'">' . __( 'Edit', 'multi-rating-pro' ) . '</a></div></div>';
		echo '<div id="'.$edit_section_id.'" style="display: none;"><textarea id="'.$input_id.'" cols="10" style="width: 100%;">' . $rating_items . '</textarea><div class="row-actions"><a href="#" id="'.$save_btn_id.'">' . __( 'Save', 'multi-rating-pro' ) . '</a></div></div>';
	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_bulk_actions()
	 */
	function get_bulk_actions() {
		
		$bulk_actions = array(
				'delete'    => __( 'Delete', 'multi-rating-pro' )
		);
		
		return $bulk_actions;
	}

	/**
	 * Handles bulk actions
	 */
	function process_bulk_action() {
		
		if ( $this->current_action() ==='delete' ) {
			global $wpdb;
			
			$checked = ( is_array( $_REQUEST['delete'] ) ) ? $_REQUEST['delete'] : array( $_REQUEST['delete'] );
			
			foreach( $checked as $id ) {
				$query = 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME . ' WHERE ' .  MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN . ' = ' . $id;
				$results = $wpdb->query( $query );
			}
			
			echo '<div class="updated"><p>' . __( 'Rating forms deleted successfully', 'multi-rating-pro' ) . '</p></div>';
		}
	}
	
	/**
	 * Saves column edit in rating item table
	 *
	 * @since 1.0
	 */
	public static function save_rating_form_table_column() {
		
		global $wpdb;
	
		$ajax_nonce = $_POST['nonce'];
		if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) ) {
			$column = $_POST['column'];
				
			// prevent SQL injection
			if ( ! ( $column == MRP_Rating_Form_Table::NAME_COLUMN || $column == MRP_Rating_Form_Table::RATING_ITEMS_COLUMN) ) {
				echo __( 'An error occured', 'multi-rating-pro' );
				die();
			}
			
			$error_message = '';
			
			$value = isset( $_POST['value'] ) ? addslashes($_POST['value']) : '';
			$rating_form_id = isset( $_POST['ratingFormId'] ) ? $_POST['ratingFormId'] : '';

			if ( $column == MRP_Rating_Form_Table::RATING_ITEMS_COLUMN ) {
				$error_message .= MRP_Utils::validate_rating_items_text( $value );
			}
			
			if ( strlen( $error_message ) == 0 ) {
				$result = $wpdb->query( 'UPDATE ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME . ' SET '. $column . ' = "' . $value . '" WHERE ' . MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN . ' = ' . $rating_form_id ) ;
				
				if ( $result === FALSE)  {
					$error_message = __( 'An error occured.', 'multi-rating-pro' );
				}
			}
			
			echo json_encode( array ( 'value' => $value, 'error_message' => $error_message ) );
		}
		die();
	}
}