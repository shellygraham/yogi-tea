<?php
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * MRP_Post_Rating_Results_Table class
 * 
 * @author dpowney
 *
 */
class MRP_Post_Rating_Results_Table extends WP_List_Table {

	const
	CHECKBOX_COLUMN = 'cb',
	POST_ID_COLUMN = 'post_id',
	RATING_FORM_ID_COLUMN = 'rating_form_id',
	TITLE_COLUMN = 'title',
	RATING_RESULT_COLUMN = 'rating_result',
	SHORTCODE_COLUMN = 'shortcode',
	COMMENTS_COLUMN = 'comments_count',
	ENTRIES_COUNT_COLUMN = 'entries_count',
	ACTION_COLUMN = 'action',
	DELETE_CHECKBOX = 'delete[]';

	/**
	 * Constructor
	 */
	function __construct() {
		
		parent::__construct( array(
				'singular'=> __( 'Rating Results', 'multi-rating-pro' ),
				'plural' => __( 'Rating Results', 'multi-rating-pro' ),
				'ajax'	=> false
		) );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::extra_tablenav()
	 */
	function extra_tablenav( $which ) {
		
		if ( $which == "top" ){
			$rating_form_id = '';
			
			if ( isset( $_REQUEST['rating_form_id'] ) ) {
				$rating_form_id = $_REQUEST['rating_form_id'];
			} ?>
						
			<div class="alignleft filters">
				<label for="rating_form_id"><?php _e( 'Rating Form', 'multi-rating-pro' ); ?></label>
				<select id="rating_form_id" name="rating_form_id">
					<?php 
					global $wpdb;
					
					$query = 'SELECT name, rating_form_id FROM ' . $wpdb->prefix .  MRP_Multi_Rating::RATING_FORM_TBL_NAME;
					$rows = $wpdb->get_results($query, ARRAY_A);
					
					foreach ( $rows as $row ) {
						$selected = '';
						
						if ( intval( $row['rating_form_id'] ) == intval( $rating_form_id ) ) {
							$selected = ' selected="selected"';
						}
						
						echo '<option value="' . $row['rating_form_id'] . '"' . $selected . '>' . $row['name'] . '</option>';
					} ?>
				</select>
				
				<input type="submit" class="button" value="<?php _e( 'Filter', 'multi-rating-pro' ); ?>"/>
			</div>
						
			<?php
		}
		
		if ( $which == "bottom" ){
			
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns() {
		
		return $columns= array(
				MRP_Post_Rating_Results_Table::CHECKBOX_COLUMN => '<input type="checkbox" />',
				MRP_Post_Rating_Results_Table::POST_ID_COLUMN => __( 'Post Id', 'multi-rating-pro' ),
				MRP_Post_Rating_Results_Table::RATING_FORM_ID_COLUMN => __( 'Rating Form Id', 'multi-rating-pro' ),
				MRP_Post_Rating_Results_Table::TITLE_COLUMN => __( 'Title', 'multi-rating-pro' ),
				MRP_Post_Rating_Results_Table::RATING_RESULT_COLUMN => __( 'Rating Result', 'multi-rating-pro' ),
				MRP_Post_Rating_Results_Table::ENTRIES_COUNT_COLUMN => __( 'Entries', 'multi-rating-pro' ),
				MRP_Post_Rating_Results_Table::COMMENTS_COLUMN => __( 'Comments', 'multi-rating-pro' ),
				MRP_Post_Rating_Results_Table::ACTION_COLUMN => __( 'Action', 'multi-rating-pro' ),
				MRP_Post_Rating_Results_Table::SHORTCODE_COLUMN => __( 'Shortcode', 'multi-rating-pro' )
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
		$hidden = array( );
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		// get table data
		$query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME;
		
		$rating_form_id = null;
		if ( isset( $_REQUEST['rating_form_id'] ) ) {
			$rating_form_id = $_REQUEST['rating_form_id'];
			
			if ( strlen( $rating_form_id ) > 0 ) {
				$query .= ' WHERE rating_form_id = "' . $rating_form_id . '"';
			}
		}
		
		$query .= ' GROUP BY post_id, rating_form_id';
		
		// pagination
		$item_count = $wpdb->query( $query ); //return the total number of affected rows
		$items_per_page = 10;
		$page_num = ! empty( $_GET["paged"] ) ? mysql_real_escape_string( $_GET["paged"] ) : '';
		if ( empty( $page_num ) || ! is_numeric( $page_num ) || $page_num <= 0 ) {
			$page_num = 1;
		}
		$total_pages = ceil( $item_count / $items_per_page );
		// adjust the query to take pagination into account
		if ( ! empty( $page_num ) && ! empty( $items_per_page ) ) {
			$offset = ( $page_num -1 ) * $items_per_page;
			$query .= ' LIMIT ' . ( int ) $offset. ',' . ( int ) $items_per_page;
		}
		
		$this->set_pagination_args( array( 
				'total_items' => $item_count,
				'total_pages' => $total_pages,
				'per_page' => $items_per_page
		) );
		
		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Default column
	 * @param unknown_type $item
	 * @param unknown_type $column_name
	 * @return unknown|mixed
	 */
	function column_default( $item, $column_name ) {
		
		$post_id =  $item[MRP_Post_Rating_Results_Table::POST_ID_COLUMN];
		$rating_form_id = $item[MRP_Post_Rating_Results_Table::RATING_FORM_ID_COLUMN];
		
		switch( $column_name ) {
			case MRP_Post_Rating_Results_Table::SHORTCODE_COLUMN : {
				
				echo '[display_rating_result post_id="' . $post_id . '" rating_form_id="' . $rating_form_id . '"]';
				break;
			}
			
			case MRP_Post_Rating_Results_Table::POST_ID_COLUMN : {
				echo $post_id;
				break;
			}
			
			case MRP_Post_Rating_Results_Table::RATING_FORM_ID_COLUMN : {
				echo $rating_form_id;
				break;
			}
			
			case MRP_Post_Rating_Results_Table::TITLE_COLUMN : {
				echo '<a href="' . get_permalink( $post_id ) . '">' . get_the_title( $post_id ) . '</a>';
				break;
			}
			
			case MRP_Post_Rating_Results_Table::ACTION_COLUMN : {
				?>
				<a class="view-rating-result-entries-anchor" href="?page=<?php echo MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG; ?>&tab=<?php echo MRP_Multi_Rating::RATING_RESULTS_TAB; ?>&post_id=<?php echo $post_id ?>&rating_form_id=<?php echo $rating_form_id; ?>"><?php _e( 'View Entries', 'multi-rating-pro' ); ?></a>
				<?php
				break;
			}
			
			case MRP_Post_Rating_Results_Table::COMMENTS_COLUMN : {
				global $wpdb;
				
				$query = 'SELECT COUNT(comment_id) FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE post_id = "' 
						. $post_id . '" AND comment_id != "" AND rating_form_id = "' . $rating_form_id . '"';
				$rows = $wpdb->get_col( $query, 0 );
				
				echo $rows[0];
				break;
			}
			
			case MRP_Post_Rating_Results_Table::ENTRIES_COUNT_COLUMN : {
				global $wpdb;
				
				$query = $query = 'SELECT COUNT(*) FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE post_id = "' 
						. $post_id . '" AND rating_form_id = "'. $rating_form_id . '"';
				$rows = $wpdb->get_col( $query, 0 );
				
				echo $rows[0];
				
				break;
			}
			
			case MRP_Post_Rating_Results_Table::RATING_RESULT_COLUMN : {
				
				$rating_items = MRP_Multi_Rating_API::get_rating_items(array('post_id' => $post_id, 'rating_form_id' => $rating_form_id));
				$rating_result = MRP_Multi_Rating_API::calculate_rating_result(array('post_id' => $post_id, 'rating_items' => $rating_items, 'rating_form_id' => $rating_form_id));
				
				$entries = $rating_result['count'];
				$html = '';
				if ($entries != 0) {
					
					echo __( 'Star Rating: ', 'multi-rating-pro' ) . $rating_result['adjusted_star_result'] . '/5<br />'
					. __( 'Score: ', 'multi-rating-pro' ) . $rating_result['adjusted_score_result'] . '/' . $rating_result['total_max_option_value'] . '<br />'
					. __( 'Percentage: ', 'multi-rating-pro' ) . $rating_result['adjusted_percentage_result'] . '%';
					
				} else {
					echo 'None';	
				}
				
				echo $html;
				break;
			}
			
			case Rating_Item_Entry_Table::CHECKBOX_COLUMN :
				return $item[ $column_name ];
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
				'<input type="checkbox" name="' . MRP_Post_Rating_Results_Table::DELETE_CHECKBOX . '" value="%s" />', $item[MRP_Post_Rating_Results_Table::POST_ID_COLUMN] . '-' . $item[MRP_Post_Rating_Results_Table::RATING_FORM_ID_COLUMN]
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_bulk_actions()
	 */
	function get_bulk_actions() {
		
		$bulk_actions = array(
				'delete'    => __( 'Delete Rating Results', 'multi-rating-pro' )
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
				$key = preg_split('/[-]+/', $id);
				$post_id = $key[0];
				$rating_form_id = $key[1];
				
				$query = 'DELETE FROM '. $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . '  WHERE ' .  MRP_Post_Rating_Results_Table::POST_ID_COLUMN . ' = "' . $post_id 
						. '" AND ' . MRP_Post_Rating_Results_Table::RATING_FORM_ID_COLUMN . ' = "' . $rating_form_id .'"';
				$results = $wpdb->query($query);
				
				// TODO delete rating item entry values as well
			}
				
			echo '<div class="updated"><p>' . sprintf( __( 'Rating results deleted successfully for Post Id %s and Rating Form Id %s.', 'multi-rating-pro' ), $post_id, $rating_form_id ) . '</p></div>';
		}
	}
}