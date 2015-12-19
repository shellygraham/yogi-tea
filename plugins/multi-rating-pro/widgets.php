<?php

/**
 * Top Rating Results Widget
 */
class MRP_Top_Rating_Results_Widget extends WP_Widget {
	
	function __construct() {
		
		$widget_ops = array( 'classname' => 'top-rating-results-widget', 'description' => __('Displays the top rating results.', 'multi-rating-pro' ) );
		$control_ops = array( 'width' => 400, 'height' => 350 );

		parent::__construct( 'top_rating_results_widget', __('Top Rating Results Widget', 'multi-rating-pro' ), $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		
		extract($args);
		
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$limit = empty( $instance['limit'] ) ? 10 : intval( $instance['limit'] );
		$rating_form_id = empty( $instance['rating_form_id'] ) ? null : intval( $instance['rating_form_id'] );
		$category_id = 0;
		if ( ! empty( $instance['category_id'] ) && is_numeric( $instance['category_id'] ) ) {
			$category_id = intval( $instance['category_id'] );
		}
		$show_category_filter = empty( $instance['show_category_filter'] ) ? false : $instance['show_category_filter'];
		
		$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
		
		echo $before_widget;
		
		MRP_Multi_Rating_API::display_top_rating_results( array(
				'rating_form_id' => $rating_form_id,
				'limit' => $limit, 'title' => $title,
				'show_category_filter' => $show_category_filter,
				'category_id' => $category_id,
				'class' => 'widget',
				'before_title' => '<h3>',
				'after_title' => '</h3>'
		) );
		
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['limit'] = intval( $new_instance['limit'] );
		$instance['rating_form_id'] = intval( $new_instance['rating_form_id'] );
		$instance['category_id'] = 0;
		if ( ! empty($new_instance['category_id'] ) && is_numeric( $new_instance['category_id'] ) ) {
			$instance['category_id'] = intval( $new_instance['category_id'] );
		}
		$instance['show_category_filter'] = false;
		if ( isset( $new_instance['show_category_filter'] ) && ( $new_instance['show_category_filter'] == 'true' ) ) {
			$instance['show_category_filter'] = true;
		}
		return $instance;
	}

	function form( $instance ) {
		
		$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		
		$instance = wp_parse_args( (array) $instance, array( 
				'title' => $custom_text_settings[MRP_Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION],
				'limit' => 10,
				'rating_form_id' => $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ] 
		) );
		
		$title = strip_tags( $instance['title'] );
		$limit = intval( $instance['limit'] );
		$rating_form_id = intval( $instance['rating_form_id'] );
		$category_id = 0;
		if ( ! empty( $instance['category_id'] ) && is_numeric($instance['category_id'] ) ) {
			$category_id = intval($instance['category_id']);
		}
		
		$show_category_filter = empty( $instance['show_category_filter'] ) ? false : $instance['show_category_filter'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit'); ?>"><?php _e( 'Limit', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'rating_form_id' ); ?>"><?php _e( 'Rating Form', 'multi-rating-pro' ); ?></label>
			
			<select class="widefat" name="<?php echo $this->get_field_name( 'rating_form_id' ); ?>" id="<?php echo $this->get_field_id( 'rating_form_id' ); ?>">
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
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'category_id' ); ?>"><?php _e('Category', 'multi-rating-pro' ); ?></label>
			<?php wp_dropdown_categories(array(
					'true' => false,
					'class' => 'widefat',
					'name' => $this->get_field_name( 'category_id' ),
					'id' => $this->get_field_id('category_id'),
					'selected' => $category_id,
					'show_option_all' => __( 'All', 'multi-rating-pro' )
			) ); ?>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_category_filter' ); ?>" name="<?php echo $this->get_field_name( 'show_category_filter' ); ?>" type="checkbox" value="true" <?php checked( true, $show_category_filter, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_category_filter' ); ?>"><?php _e( 'Show category filter', 'multi-rating-pro' ); ?></label>
		</p>
		<?php
	}
}


/**
 * User Ratings Widget
 */
class MRP_User_Rating_Results_Widget extends WP_Widget {
	
	function __construct() {
		
		$widget_ops = array( 'classname' => 'user-rating-results-widget', 'description' => __( 'Displays all rating results belonging to a logged in user.', 'multi-rating-pro' ) );
		$control_ops = array( 'width' => 400, 'height' => 350 );

		parent::__construct( 'user_rating_results_widget', __('User Rating Results Widget', 'multi-rating-pro' ), $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		
		extract($args);
		
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$limit = empty( $instance['limit'] ) ? 10 : intval( $instance['limit'] );
		$category_id = 0;
		if ( ! empty( $instance['category_id'] ) && is_numeric( $instance['category_id'] ) ) {
			$category_id = intval( $instance['category_id'] );
		}
		$show_category_filter = empty( $instance['show_category_filter'] ) ? false : $instance['show_category_filter'];
		$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
		
		// get username
		global $wp_roles;
		$current_user = wp_get_current_user();
		$username = $current_user->user_login;
		
		echo $before_widget;
		
		MRP_Multi_Rating_API::display_user_rating_results( array(
				'title' => $title,
				'username' => $username,
				'category_id' =>$category_id,
				'show_category_filter' => $show_category_filter,
				'limit' => $limit,
				'class' => 'widget',
				'before_title' => '<h3>',
				'after_title' => '</h3>'
		) );
		
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['limit'] = intval($new_instance['limit']);
		$instance['category_id'] = 0;
		if ( ! empty( $new_instance['category_id'] ) && is_numeric( $new_instance['category_id'] ) ) {
			$instance['category_id'] = intval($new_instance['category_id']);
		}
		$instance['show_category_filter'] = false;
		if (isset( $new_instance['show_category_filter'] ) && ( $new_instance['show_category_filter'] == 'true' ) ) {
			$instance['show_category_filter'] = true;
		}
		return $instance;
	}

	function form( $instance ) {
		$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
		$instance = wp_parse_args( (array) $instance, array( 
				'title' => $custom_text_settings[MRP_Multi_Rating::USER_RATING_RESULTS_TITLE_TEXT_OPTION],
				'limit' => 10
		) );
		
		$title = strip_tags( $instance['title'] );
		$limit = intval( $instance['limit'] );
		$category_id = 0;
		if ( ! empty( $instance['category_id'] ) && is_numeric( $instance['category_id'] ) ) {
			$category_id = intval( $instance['category_id'] );
		}

		$show_category_filter = empty( $instance['show_category_filter'] ) ? false : $instance['show_category_filter'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'category_id' ); ?>"><?php _e( 'Category', 'multi-rating-pro' ); ?></label>
			<?php wp_dropdown_categories(array(
					'true' => false,
					'class' => 'widefat',
					'name' => $this->get_field_name('category_id'),
					'id' => $this->get_field_id('category_id'),
					'selected' => $category_id,
					'show_option_all' => __( 'All', 'multi-rating-pro' )
			) ); ?>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_category_filter' ); ?>" name="<?php echo $this->get_field_name( 'show_category_filter' ); ?>" type="checkbox" value="true" <?php checked( true, $show_category_filter, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_category_filter' ); ?>"><?php _e( 'Show category filter', 'multi-rating-pro' ); ?></label>
		</p>
		<?php
	}
}

function mrp_register_widgets() {
	register_widget( 'MRP_Top_Rating_Results_Widget' );
	register_widget( 'MRP_User_Rating_Results_Widget' );
}
add_action( 'widgets_init', 'mrp_register_widgets' );
?>