<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Rating_Item_Table class
 * @author dpowney
 *
 */
class MRP_Rating_Item_Table extends WP_List_Table {

	const
	DESCRIPTION_COLUMN = 'description',
	MAX_OPTION_VALUE_COLUMN = 'max_option_value',
	CHECKBOX_COLUMN = 'cb',
	RATING_ITEM_ID_COLUMN = 'rating_item_id',
	RATING_ID_COLUMN = 'rating_id',
	DEFAULT_OPTION_VALUE_COLUMN = 'default_option_value',
	OPTION_VALUE_TEXT_COLUMN = 'option_value_text',
	INCLUDE_ZERO_COLUMN = 'include_zero',
	WEIGHT_COLUMN = 'weight',
	TYPE_COLUMN = 'type',
	DELETE_CHECKBOX = 'delete[]';

	/**
	 * Constructor
	 */
	function __construct() {
		
		parent::__construct( array(
				'singular'=> __( 'Rating Items', 'multi-rating-pro' ),
				'plural' => __( 'Rating Items', 'multi-rating-pro' ),
				'ajax'	=> false
		) );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::extra_tablenav()
	 */
	function extra_tablenav( $which ) {
		if ( $which == 'top' ){
			echo '';
		}
		
		if ( $which == 'bottom' ){
			echo '';
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns() {
		return array(
				MRP_Rating_Item_Table::CHECKBOX_COLUMN => '<input type="checkbox" />',
				MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN =>__( 'Rating Item Id', 'multi-rating-pro' ),
				MRP_Rating_Item_Table::RATING_ID_COLUMN => '',
				MRP_Rating_Item_Table::DESCRIPTION_COLUMN =>__( 'Description', 'multi-rating-pro' ),
				MRP_Rating_Item_Table::TYPE_COLUMN => __( 'Type', 'multi-rating-pro' ),
				MRP_Rating_Item_Table::WEIGHT_COLUMN	=>__(' Weight', 'multi-rating-pro' ),
				MRP_Rating_Item_Table::DEFAULT_OPTION_VALUE_COLUMN => __( 'Default Option Value', 'multi-rating-pro' ),
				MRP_Rating_Item_Table::MAX_OPTION_VALUE_COLUMN => __('Max Option Value', 'multi-rating-pro' ),
				MRP_Rating_Item_Table::INCLUDE_ZERO_COLUMN => __( 'Include zero', 'multi-rating-pro' ),
				MRP_Rating_Item_Table::OPTION_VALUE_TEXT_COLUMN => __('Option Value Text', 'multi-rating-pro' ) . '<br /><span class="description">' . __( 'Each option must be separated on a newline ', 'multi-rating-pro' ) . '<code>' . __( 'value=text description', 'multi-rating-pro' ) . '</code></span>'
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
		$hidden = array( MRP_Rating_Item_Table::RATING_ID_COLUMN );
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$query = 'SELECT * FROM ' . $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_TBL_NAME;
		
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
			case MRP_Rating_Item_Table::CHECKBOX_COLUMN :
			case MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN :
			case MRP_Rating_Item_Table::RATING_ID_COLUMN :
			case MRP_Rating_Item_Table::OPTION_VALUE_TEXT_COLUMN:
			case MRP_Rating_Item_Table::TYPE_COLUMN:
				return $item[ $column_name ];
				break;
				
			case MRP_Rating_Item_Table::WEIGHT_COLUMN:
			case MRP_Rating_Item_Table::DESCRIPTION_COLUMN :
			case MRP_Rating_Item_Table::DEFAULT_OPTION_VALUE_COLUMN:
			case MRP_Rating_Item_Table::MAX_OPTION_VALUE_COLUMN:
				$this->column_actions( $item, $column_name );
				break;
				
			case MRP_Rating_Item_Table::INCLUDE_ZERO_COLUMN:
				$this->column_checkbox( $item, $column_name );
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
	function column_cb($item) {
		
		return sprintf(
				'<input type="checkbox" name="' . MRP_Rating_Item_Table::DELETE_CHECKBOX . '" value="%s" />', $item[MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN]
		);
	}

	/**
	 * Type column
	 * @param unknown_type $item
	 */
	function column_type($item) {
		
		$column_name = MRP_Rating_Item_Table::TYPE_COLUMN;
		$row_id = $item[MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN];
		$row_value = stripslashes( $item[$column_name] );
		$edit_btn_id = 'edit-' . $column_name.'-'.$row_id;
		$save_btn_id = 'save-' . $column_name.'-'.$row_id;
		$view_section_id = 'view-section-' . $column_name . '-' . $row_id;
		$edit_section_id = 'edit-section-' . $column_name . '-' . $row_id;
		
		// if column is type, use a select
		$field_id = 'field-'. $column_name . '-'. $row_id;
		$text_id = 'text-'. $column_name . '-'. $row_id;

		$type_options = array( 'select' => __( 'Select', 'multi-rating-pro' ), 'radio' => __( 'Radio', 'multi-rating-pro' ), 'star_rating' => __( 'Star Rating', 'multi-rating-pro' ), 'thumbs' => __( 'Thumbs', 'multi-rating-pro' ) );
		
		$text_value = isset( $type_options[$row_value] ) ? $type_options[$row_value] : $row_value;
		
		echo '<div id="' . $view_section_id . '"><div id="' . $text_id . '">' . $text_value . '</div><div class="row-actions"><a href="#" id="' . $edit_btn_id.'">' . __( 'Edit', 'multi-rating-pro' ) . '</a></div></div>';
		echo '<div id="'. $edit_section_id . '" style="display: none;">';
		
		echo '<select name="' . $field_id . '" id="' . $field_id .'">';
		foreach ( $type_options as $type_option_value => $type_option_text ) {
			echo '<option value="' . $type_option_value . '"';
			
			if ( $type_option_value == $row_value ) {
				echo ' checked="checked"';
			}
			echo '>' . $type_option_text . '</option>';
		}
		echo '</select>';
		
		echo '<div class="row-actions"><a href="#" id="' . $save_btn_id . '">' . __( 'Save', 'multi-rating-pro' ) . '</a></div></div>';
	}
	
	/**
	 * Actions columns
	 * @param unknown_type $item
	 * @param unknown_type $column_name
	 */
	function column_actions($item, $column_name) {
		
		$row_id = $item[MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN];
		$row_value = stripslashes( $item[$column_name] );
		$edit_btn_id = 'edit-' . $column_name . '-' . $row_id;
		$save_btn_id = 'save-' . $column_name . '-' . $row_id;
		$view_section_id = 'view-section-'. $column_name . '-'. $row_id;
		$edit_section_id = 'edit-section-'. $column_name . '-'. $row_id;
		
		// if column is type, use a select
		$field_id = 'field-'. $column_name . '-'. $row_id;
		$text_id = 'text-'. $column_name . '-'. $row_id;
		
		echo '<div id="' . $view_section_id . '"><div id="' . $text_id . '">' . $row_value . '</div><div class="row-actions"><a href="#" id="' . $edit_btn_id . '">' . __( 'Edit', 'multi-rating-pro' ) . '</a></div></div>';
		echo '<div id="' . $edit_section_id . '" style="display: none;">';
		echo '<input type="text" name="' . $field_id . '" id="'. $field_id . '" value="'. $row_value . '" style="width: 100%;" />';
		echo '<div class="row-actions"><a href="#" id="' . $save_btn_id . '">' . __( 'Save', 'multi-rating-pro' ) . '</a></div></div>';	
	}
	
	/**
	 * Option value text column
	 * @param unknown_type $item
	 */
	function column_option_value_text( $item ) {
		$column_name = MRP_Rating_Item_Table::OPTION_VALUE_TEXT_COLUMN;
		$row_id = $item[MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN];
		
		// row value for textarea needs be be translated to HTML
		$row_value = '';
		$option_value_text =  $item[ $column_name ];
		$option_value_text_array = preg_split( '/[\r\n,]+/', $option_value_text, -1, PREG_SPLIT_NO_EMPTY );
		foreach ( $option_value_text_array as $current_option_value_text ) {
			$row_value .= $current_option_value_text . '<br />';
		}

		$edit_btn_id = 'edit-' . $column_name . '-' . $row_id;
		$save_btn_id = 'save-' . $column_name . '-' . $row_id;
		$view_section_id = 'view-section-' . $column_name . '-' . $row_id;
		$edit_section_id = 'edit-section-' . $column_name . '-' . $row_id;
		$input_id = 'field-'. $column_name . '-'. $row_id;
		$text_id = 'text-'. $column_name . '-'. $row_id;
		
		echo '<div id="' . $view_section_id . '"><div id="' . $text_id . '">' . stripslashes( $row_value ) . '</div><div class="row-actions"><a href="#" id="' . $edit_btn_id . '">' . __( 'Edit', 'multi-rating-pro' ) . '</a></div></div>';
		echo '<div id="'. $edit_section_id . '" style="display: none;"><textarea id="' . $input_id . '" cols="10" style="width: 100%;">' . stripslashes( $option_value_text ) . '</textarea><div class="row-actions"><a href="#" id="' . $save_btn_id . '">' . __( 'Save', 'multi-rating-pro' ) . '</a></div></div>';
	}
	
	/**
	 * Checkbox column
	 * @param $item
	 * @param $column_name
	 */
	function column_checkbox( $item, $column_name ) {
		
		$row_id = $item[MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN];
		$row_value = stripslashes( $item[$column_name] );
		$edit_btn_id = 'edit-' . $column_name . '-' . $row_id;
		$save_btn_id = 'save-' . $column_name . '-' . $row_id;
		$view_section_id = 'view-section-' . $column_name . '-' . $row_id;
		$edit_section_id = 'edit-section-' . $column_name . '-' . $row_id;
	
		// if column is type, use a select
		$field_id = 'field-' . $column_name . '-' . $row_id;
		$text_id = 'text-' . $column_name . '-'.  $row_id;
	
		echo '<div id="' . $view_section_id.'"><div id="'.$text_id.'">' . ($row_value == true ? 'Yes' : 'No') . '</div><div class="row-actions"><a href="#" id="'.$edit_btn_id.'">' . __( 'Edit', 'multi-rating-pro' ) . '</a></div></div>';
		echo '<div id="'. $edit_section_id.'" style="display: none;">';
		echo '<input type="checkbox" name="' . $field_id . '" id="'. $field_id . '"';

		if ( $row_value == true ) {
			echo ' checked="checked"';
		}
		echo ' value="true" />';
		
		echo '<div class="row-actions"><a href="#" id="' . $save_btn_id . '">' . __( 'Save', 'multi-rating-pro' ) . '</a></div></div>';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_bulk_actions()
	 */
	function get_bulk_actions() {
		
		$bulk_actions = array(
				'delete' => __( 'Delete', 'multi-rating-pro' )
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
				$query = 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME . ' WHERE ' .  MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN . ' = ' . $id;
				$results = $wpdb->query( $query );
			}
			
			echo '<div class="updated"><p>' . __( 'Rating items deleted successfully.', 'multi-rating-pro' ) . '</p></div>';
		}
	}
	
	/**
	 * Saves column edit in rating item table
	 *
	 * @since 1.0
	 */
	public static function save_rating_item_table_column() {
		
		global $wpdb;
	
		$ajax_nonce = $_POST['nonce'];
		if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) ) {
			$column = $_POST['column'];
				
			// prevent SQL injection
			if (! ( $column == MRP_Rating_Item_Table::DESCRIPTION_COLUMN || $column == MRP_Rating_Item_Table::MAX_OPTION_VALUE_COLUMN
					|| $column == MRP_Rating_Item_Table::DEFAULT_OPTION_VALUE_COLUMN || $column == MRP_Rating_Item_Table::WEIGHT_COLUMN 
					|| $column == MRP_Rating_Item_Table::OPTION_VALUE_TEXT_COLUMN || $column == MRP_Rating_Item_Table::TYPE_COLUMN
					|| $column == MRP_Rating_Item_Table::INCLUDE_ZERO_COLUMN ) ) {
				echo __( 'An error occured', 'multi-rating-pro' );
				die();
			}
			
			/* 
			 * validate each column
			 */
			
			$error_message = '';					
				
			$value = isset( $_POST['value'] ) ? addslashes( $_POST['value'] ) : '';
			
			if ( $column == MRP_Rating_Item_Table::INCLUDE_ZERO_COLUMN ) {
				$value = false;
				if ( isset($_POST['value'] ) && $_POST['value'] == 'true' ) {
					$value = true;
				}
			}
			
			$rating_item_id = isset( $_POST['ratingItemId'] ) ? $_POST['ratingItemId'] : '';
			
			// get current values for validation
			$query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME . ' WHERE rating_item_id = "' . $rating_item_id . '"';
			$row = $wpdb->get_row( $query, ARRAY_A, 0 );
			
			$max_option_value = intval( $row['max_option_value'] );
			$default_option_value = intval( $row['default_option_value'] );
			$type = $row['type'];
			
			
			if ( $column == MRP_Rating_Item_Table::DESCRIPTION_COLUMN ) {
				if ( strlen( trim( $value ) ) == 0 ) {
					$error_message .= __( 'Description cannot be empty. ', 'multi-rating-pro' );
				}
				
			} else if ( $column == MRP_Rating_Item_Table::MAX_OPTION_VALUE_COLUMN ) {
				if ( is_numeric( $value ) == false ) {
					$error_message .= __( 'Max option value cannot be empty and must be a whole number. ', 'multi-rating-pro' );
				}
				
				if ( $default_option_value > intval( $value ) ) {
					$error_message .= __( 'Default option value cannot be greater than the max option value. ', 'multi-rating-pro' );
				}
				
				if ( $type == 'thumbs' && $value != 1 ) {
					$error_message .= __( 'Max option value must be 1 for thumbs rating item type. ', 'multi-rating-pro' );
				}
				
			} else if ( $column == MRP_Rating_Item_Table::DEFAULT_OPTION_VALUE_COLUMN ) {
				if ( is_numeric( $value ) == false ) {
					$error_message .= __( 'Default option value cannot be empty and must be a whole number. ', 'multi-rating-pro' );
				}
				
				if ( intval( $value ) > $max_option_value ) {
					$error_message .= __( 'Default option value cannot be greater than the max option value. ', 'multi-rating-pro' );
				}
				
				if ( $type == 'thumbs' && $value != 0 && $value != 1 ) {
					$error_message .= __( 'Default option value must be 0 or 1 for thumbs rating item type. ', 'multi-rating-pro' );
				}
				
			} else if ( $column == MRP_Rating_Item_Table::WEIGHT_COLUMN ) {
				if ( is_numeric( $value ) == false ) {
					$error_message .= __( 'Weight must be numeric. ', 'multi-rating-pro' );
				}
				
			} else if ( $column == MRP_Rating_Item_Table::OPTION_VALUE_TEXT_COLUMN ) {
				$error_message .= MRP_Utils::validate_option_value_text( $value, $max_option_value );
			}
			
			if ( strlen( $error_message ) == 0 ) {
				$query = 'UPDATE ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME.' SET ' . $column . ' = "' . $value . '"';

				if ( $column == MRP_Rating_Item_Table::TYPE_COLUMN && $type == 'thumbs') {
					// set max option value and default option value for thumbs up/down if required
					if ( $max_option_value != 1 ) {
						$query .= ', max_option_value = 1';
					}
					
					if ( $default_option_value != 0 && $default_option_value != 1 ) {
						$query .= ', default_option_value = 1';
					}
				}
				 
				$query .= ' WHERE ' . MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN .' = ' . $rating_item_id;
				$result = $wpdb->query( $query );
				
				if ( $result === FALSE ) {
					$error_message = __( 'An error occured.', 'multi-rating-pro' );
				}
			}
			
			if ( strlen( $error_message ) == 0 && $column == MRP_Rating_Item_Table::OPTION_VALUE_TEXT_COLUMN ) {
				$option_value_text_array = preg_split( '/[\r\n,]+/', stripslashes( $value ), -1, PREG_SPLIT_NO_EMPTY );
				$value = '';
				
				foreach ( $option_value_text_array as $current_option_value_text ) {
					$value .= stripslashes( $current_option_value_text ) . '<br />';
				}
			}
			
			$text_value = $value;
			
			if ( $column == MRP_Rating_Item_Table::TYPE_COLUMN ) {
				$type_options = array('select' => __('Select', 'multi-rating-pro' ), 'radio' => __( 'Radio', 'multi-rating-pro' ), 'star_rating' => __( 'Star Rating', 'multi-rating-pro' ), 'thumbs' => __( 'Thumbs', 'multi-rating-pro' ) );
				$text_value = isset( $type_options[$value] ) ? $type_options[$value] : $value;
			} else if ( $column == MRP_Rating_Item_Table::INCLUDE_ZERO_COLUMN ) {
				$text_value = ( $value == true ) ? __( 'Yes', 'multi-rating-pro' ) : __( 'No', 'multi-rating-pro' );
			}
			
			echo json_encode( array ( 'value' => $text_value, 'error_message' => $error_message ) );
		}
		
		die();
	}
}