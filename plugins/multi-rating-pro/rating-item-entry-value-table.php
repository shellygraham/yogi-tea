<?php
if( ! class_exists( 'WP_List_Table' ) ){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * MRP_Rating_Item_Entry_Value_Table class
 * @author dpowney
 *
 */
class MRP_Rating_Item_Entry_Value_Table extends WP_List_Table {

	const
	CHECKBOX_COLUMN = 'cb',
	RATING_ITEM_ENTRY_ID_COLUMN = 'rating_item_entry_id',
	RATING_ITEM_ID_COLUMN = 'rating_item_id',
	DESCRIPTION_COLUMN = 'description',
	OPTION_VALUE_TEXT_COLUMN = 'option_value_text',
	VALUE_COLUMN = 'value',
	MAX_OPTION_VALUE_COLUMN = 'max_option_value',
	ACTION_COLUMN = 'action',
	DELETE_CHECKBOX = 'delete[]';

	/**
	 * Constructor
	 */
	function __construct() {
		
		parent::__construct( array(
				'singular'=> __( 'Entry Values', 'multi-rating-pro' ),
				'plural' => __( 'Entry Values', 'multi-rating-pro' ),
				'ajax'	=> false
		) );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::extra_tablenav()
	 */
	function extra_tablenav( $which ) {
		
		if ( $which == 'top' ){
			
			$rating_item_entry_id = $this->get_rating_item_entry_id();
			if ( $rating_item_entry_id == null ) {
				$rating_item_entry_id = '';
			}
			
			echo '<label for="rating-item_entry-id">' . __( 'Entry Id', 'multi-rating-pro' ) . '</label>';
			echo '<input type="text" class="regular-text" name="rating-item-entry-id" value="' . $rating_item_entry_id . '" />';
			echo '<input type="submit" class="button" value="' . __( 'Submit', 'multi-rating-pro' ) . '" />';
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
				MRP_Rating_Item_Entry_Value_Table::RATING_ITEM_ENTRY_ID_COLUMN =>__( 'Entry Id', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Value_Table::RATING_ITEM_ID_COLUMN => __( 'Rating Item Id', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Value_Table::DESCRIPTION_COLUMN =>__( 'Description', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Value_Table::VALUE_COLUMN	=>__( 'Value', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Value_Table::MAX_OPTION_VALUE_COLUMN => __( 'Max Option Value', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Value_Table::OPTION_VALUE_TEXT_COLUMN => ''
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::prepare_items()
	 */
	function prepare_items() {
		global $wpdb;

		// Register the columns
		$columns = $this->get_columns();
		$hidden = array( MRP_Rating_Item_Entry_Value_Table::RATING_ITEM_ENTRY_ID_COLUMN, 
				MRP_Rating_Item_Entry_Value_Table::RATING_ITEM_ID_COLUMN, MRP_Rating_Item_Entry_Value_Table::OPTION_VALUE_TEXT_COLUMN );
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$rating_item_entry_id = $this->get_rating_item_entry_id();
		if ( $rating_item_entry_id == null ) {
			return;
		}
		
		$query = 'SELECT ri.description AS description, ri.option_value_text as option_value_text, riev.value AS value, ri.max_option_value AS max_option_value, '
				. 'riev.rating_item_entry_id AS rating_item_entry_id, ri.rating_item_id AS rating_item_id ' 
				. 'FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' AS riev, '
				. $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME . ' AS ri WHERE ri.rating_item_id = riev.rating_item_id '
				. 'AND riev.rating_item_entry_id = "' . $rating_item_entry_id . '"';

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
			case MRP_Rating_Item_Entry_Value_Table::RATING_ITEM_ENTRY_ID_COLUMN :
			case MRP_Rating_Item_Entry_Value_Table::RATING_ITEM_ID_COLUMN :
			case MRP_Rating_Item_Entry_Value_Table::DESCRIPTION_COLUMN :
			case MRP_Rating_Item_Entry_Value_Table::MAX_OPTION_VALUE_COLUMN :
				echo $item[ $column_name ];
				break;
				
			case MRP_Rating_Item_Entry_Value_Table::VALUE_COLUMN :
				$value = $item[ $column_name ];
				$value_text = $value;
				$option_value_text = $item[ MRP_Rating_Item_Entry_Value_Table::OPTION_VALUE_TEXT_COLUMN ];
				
				$option_value_text_array = preg_split('/[\r\n,]+/',  $option_value_text, -1, PREG_SPLIT_NO_EMPTY );
				
				// try to find the option value text if it has been set
				foreach ( $option_value_text_array as $current_option_value_text ) {
					$parts = explode("=", $current_option_value_text);
						
					if ( isset( $parts[0] ) && isset( $parts[1] ) ) {
						$curent_value = intval($parts[0]);
						$current_text = $parts[1];
				
						if ( $value == $curent_value ) {
							$value_text = $value . ' (' . stripslashes( $current_text ) . ')';
							break;
						}
				
					}
				}
				
				echo $value_text;
				break;
				
			default:
				return print_r( $item, true ) ;
		}
	}
	
	/**
	 * Gets entry id from HTTP request
	 */
	private function get_rating_item_entry_id() {
		
		if ( isset( $_POST['rating-item-entry-id'] ) ) {
			return $_POST['rating-item-entry-id'];
		} else if ( isset( $_GET['rating-item-entry-id'] ) ) {
			return $_GET['rating-item-entry-id'];
		}
		
		return null;
	}
}