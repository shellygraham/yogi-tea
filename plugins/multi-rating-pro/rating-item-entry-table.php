<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * MRP_Rating_Item_Entry_Table class
 * @author dpowney
 *
 */
class MRP_Rating_Item_Entry_Table extends WP_List_Table {

	const
	CHECKBOX_COLUMN = 'cb',
	RATING_ITEM_ENTRY_ID_COLUMN = 'rating_item_entry_id',
	POST_ID_COLUMN = 'post_id',
	RATING_FORM_ID_COLUMN = 'rating_form_id',
	ENTRY_DATE_COLUMN = 'entry_date',
	IP_ADDRESS_COLUMN = 'ip_address',
	USERNAME_COLUMN = 'username',
	NAME_COLUMN = 'name',
	EMAIL_COLUMN = 'email',
	COMMENT_COLUMN = 'comment',
	RATING_RESULT_COLUMN = 'rating_result',
	SHORTCODE_COLUMN = 'shortcode',
	COMMENT_ID_COLUMN = 'comment_id',
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
		
		if ( $which == 'top' ){
			
			$post_id = '';
			if ( isset( $_REQUEST['post_id'] ) ) {
				$post_id = $_REQUEST['post_id'];
			}
			
			$rating_form_id = '';
			if (isset( $_REQUEST['rating_form_id'] ) ) {
				$rating_form_id = $_REQUEST['rating_form_id'];
			}
			
			$comments_only_checked = '';
			if ( isset( $_REQUEST['comments_only'] ) && $_REQUEST['comments_only'] == 'true' ) {
				$comments_only_checked = ' checked="checked"';
			}
			
			global $wpdb;
			?>
			
			<div class="alignleft filters">
				<label for="post_id"><?php _e( 'Post', 'multi-rating-pro' ); ?></label>
				<select name="post_id" id="post_id">
					<?php	
					$query = 'SELECT DISTINCT post_id FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME;
					$rows = $wpdb->get_results( $query, ARRAY_A );

					foreach ( $rows as $row ) {
						$post = get_post($row['post_id']);
						?>
						<option value="<?php echo $post->ID; ?>" <?php if ( $post->ID == $post_id ) echo 'selected="selected"'; ?>>
							<?php echo get_the_title( $post->ID ); ?>
						</option>
					<?php } ?>
				</select>
				<label for="rating_form_id"><?php _e( 'Rating Form', 'multi-rating-pro' ); ?></label>
				<select id="rating_form_id" name="rating_form_id">
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
				<label for="post_id"><?php _e( 'Comments only', 'multi-rating-pro' ); ?></label>
				<input type="checkbox" name="comments_only" id="comments_only" value="true" <?php echo $comments_only_checked; ?>>
				<input type="submit" class="button" value="<?php _e( 'Filter', 'multi-rating-pro' ); ?>"/>
			</div>
			
			<?php
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
				MRP_Rating_Item_Entry_Table::CHECKBOX_COLUMN => '<input type="checkbox" />',
				MRP_Rating_Item_Entry_Table::RATING_ITEM_ENTRY_ID_COLUMN =>__( 'Entry Id', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Table::POST_ID_COLUMN => __( 'Post Id', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Table::RATING_FORM_ID_COLUMN => __( 'Rating Form Id', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Table::ENTRY_DATE_COLUMN =>__( 'Entry Date', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Table::IP_ADDRESS_COLUMN	=>__( 'IP Address', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Table::USERNAME_COLUMN => __( 'Username', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Table::NAME_COLUMN => __( 'Name', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Table::EMAIL_COLUMN => __( 'E-mail', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Table::COMMENT_COLUMN => __( 'Comment', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Table::RATING_RESULT_COLUMN => __( 'Rating Result', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Table::ACTION_COLUMN => __( 'Action', 'multi-rating-pro' ),
				MRP_Rating_Item_Entry_Table::COMMENT_ID_COLUMN => __( 'Comment ID', 'multi-rating-pro' )
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
		$hidden = array( MRP_Rating_Item_Entry_Table::COMMENT_ID_COLUMN );
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// get table data
		$query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME;
		
		$post_id = null;
		if ( isset( $_REQUEST['post_id'] ) ) {
			$post_id = $_REQUEST['post_id'];
		}
		
		$rating_form_id = null;
		if ( isset( $_REQUEST['rating_form_id'] ) ) {
			$rating_form_id = $_REQUEST['rating_form_id'];
		}
		
		$comments_only = false;
		if ( isset( $_REQUEST['comments_only'] ) ) {
			$comments_only = ( $_REQUEST['comments_only'] ) == 'true' ? true : false;
		}

		if ( $post_id != null || $comments_only == true || $rating_form_id != null ) {
			
			$query .= ' WHERE';
			$param_just_added = false;
			
			if ( $post_id != null ) {
				$query .= ' post_id = "' . $post_id . '"';
				$param_just_added = true;
			}
			
			if ( $comments_only != null ) {
				if ( $param_just_added == true ) {
					$query .= ' AND';
					$param_just_added = false;
				}
				
				$query .= ' (comment != "" OR comment_id != "")';
				$param_just_added = true;
			}
			
			if ( $rating_form_id != null ) {
				if ($param_just_added == true) {
					$query .= ' AND';
					$param_just_added = false;
				}
				
				$query .= ' rating_form_id = "' . $rating_form_id . '"';
				$param_just_added = true;
			}
		}
		
		$query .= ' ORDER BY entry_date DESC';
		
		// pagination
		$item_count = $wpdb->query( $query ); //return the total number of affected rows
		$items_per_page = 10;
		$page_num = ! empty( $_GET["paged"] ) ? mysql_real_escape_string ($_GET["paged"] ) : '';
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
		
		switch( $column_name ) {
			case MRP_Rating_Item_Entry_Table::CHECKBOX_COLUMN :
				return $item[ $column_name ];
				break;
				
			case MRP_Rating_Item_Entry_Table::ENTRY_DATE_COLUMN :
				$time = strtotime($item[$column_name]);
				echo date( 'F j, Y, g:i a', strtotime( $item[$column_name] ) );
				break;
				
			case MRP_Rating_Item_Entry_Table::RATING_ITEM_ENTRY_ID_COLUMN :
			case MRP_Rating_Item_Entry_Table::POST_ID_COLUMN :
			case MRP_Rating_Item_Entry_Table::RATING_FORM_ID_COLUMN :
			case MRP_Rating_Item_Entry_Table::EMAIL_COLUMN :
			case MRP_Rating_Item_Entry_Table::NAME_COLUMN :
			case MRP_Rating_Item_Entry_Table::IP_ADDRESS_COLUMN :
			case MRP_Rating_Item_Entry_Table::USERNAME_COLUMN :
				echo stripslashes( $item[ $column_name ] );
				break;
				
			case MRP_Rating_Item_Entry_Table::COMMENT_COLUMN :
				$comment_id = $item[ MRP_Rating_Item_Entry_Table::COMMENT_ID_COLUMN ];
				
				if ($comment_id != '') {
					$comment = get_comment($comment_id);
					
					if ($comment != null) {
						echo get_comment_excerpt($comment_id);
						$comment_id = $item[ MRP_Rating_Item_Entry_Table::COMMENT_ID_COLUMN ];
						
						if ( $comment->comment_approved != 1) {
							echo ' <span style="color: Orange">' . __( '(Not yet approved)', 'multi-rating-pro' ) . '</span>';
						}
						
						if ( $comment_id != '' ) {
							echo '<br /><a href="comment.php?action=editcomment&c=' . $comment_id . '">' . __( 'Edit Comment', 'multi-rating-pro' ) . '</a>';
						}
					}
					
				} else {
					echo nl2br( stripslashes( $item[ $column_name ] ) );
				}
				
				break;
					
			case MRP_Rating_Item_Entry_Table::RATING_RESULT_COLUMN :
				$rating_result = MRP_Multi_Rating_API::calculate_rating_item_entry_result( $item[ MRP_Rating_Item_Entry_Table::RATING_ITEM_ENTRY_ID_COLUMN ] );
				
				echo __( 'Star Rating: ', 'multi-rating-pro' ) . $rating_result['adjusted_star_result'] . '/5<br />'
				. __( 'Score: ', 'multi-rating-pro' ) . $rating_result['adjusted_score_result'] . '/' . $rating_result['total_max_option_value'] . '<br />'
				. __( 'Percentage: ', 'multi-rating-pro' ) . $rating_result['adjusted_percentage_result'] . '%';
				break;
				
			case MRP_Rating_Item_Entry_Table::ACTION_COLUMN :
				?>
				<a class="view-rating-item-entry-values-anchor" href="?page=<?php echo MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG; ?>&tab=<?php echo MRP_Multi_Rating::RATING_RESULT_DETAILS_TAB; ?>&rating-item-entry-id=<?php echo $item[ MRP_Rating_Item_Entry_Table::RATING_ITEM_ENTRY_ID_COLUMN ]; ?>"><?php _e( 'View Entry Values', 'multi-rating-pro' ); ?></a>
				<?php
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
		return sprintf(
				'<input type="checkbox" name="' . MRP_Rating_Item_Entry_Table::DELETE_CHECKBOX . '" value="%s" />', $item[MRP_Rating_Item_Entry_Table::RATING_ITEM_ENTRY_ID_COLUMN]
		);
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
				$query = 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE ' .  MRP_Rating_Item_Entry_Table::RATING_ITEM_ENTRY_ID_COLUMN . ' = ' . $id;
				$results = $wpdb->query($query);
				
				$query = 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' WHERE ' .  MRP_Rating_Item_Entry_Table::RATING_ITEM_ENTRY_ID_COLUMN . ' = ' . $id;
				$results = $wpdb->query( $query );
				
			}
			
			echo '<div class="updated"><p>' . __( 'Entries deleted successfully', 'multi-rating-pro' ) . '</p></div>';
		}
	}
}