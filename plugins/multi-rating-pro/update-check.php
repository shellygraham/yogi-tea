<?php

/**
 * This file performs a check to determine whether an update is required
 */

// Check if we need to do an upgrade from a previous version
$previous_plugin_version = get_option( MRP_Multi_Rating::VERSION_OPTION );
if ( $previous_plugin_version != MRP_Multi_Rating::VERSION ) {
	
	// reactivate plugin and db updates will occur
	MRP_Multi_Rating::activate_plugin();
	
	try {
		// Delete old files that are no longer used from previous versions

		
	} catch (Exception $e) {
		die(__( 'An error occured updating the plugin file structure! Try manually deleting the plugin files to fix the problem.', 'multi-rating-pro' ) );
	}

	update_option( MRP_Multi_Rating::VERSION_OPTION, MRP_Multi_Rating::VERSION );
}

/**
 * Recursive function to remove a directory and all it's sub-directories and contents
 * @param unknown_type $dir
 */
function mr_recursive_rmdir_and_unlink( $dir ) {
	if ( is_dir( $dir ) ) {
		
		$objects = scandir( $dir );
		
		foreach ( $objects as $object ) {
			if ( $object != "." && $object != ".." ) {
				if ( filetype($dir . DIRECTORY_SEPARATOR . $object ) == "dir" )
					recursive_rmdir_and_unlink( $dir. DIRECTORY_SEPARATOR . $object );
				else unlink( $dir . DIRECTORY_SEPARATOR . $object );
			}
		}
		
		reset( $objects );
		
		rmdir( $dir );
	}
}
 
 ?>