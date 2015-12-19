<?php
/*
  Plugin Name: Custom - Well Wishes - Form Behavior
  Description: A plugin that allows you to insert onetime codes into Emails sent by Ninja Forms.
  Author: CJ Stritzel
  Version: 1.0
  */


// Form #1: Sending a Well Wish
///////////////////////////////////////////////

function ww_repurpose_admin_email(){

	// Repurposint/Hijacking the "Admin" email to ---
	// ----------------------------------
	//
	//   a) be delivered to the "To" field
	//   b) contain a different message that contains a token in the link.
	
  global $ninja_forms_processing; // The global variable gives us access to all the form and field settings.
  
  if( $ninja_forms_processing->get_form_ID() == 6 ){
  	$link = "54.201.89.77/well-wishes-retrieval/?wwt=";
  	$token = md5(time());
  	$tea_pairs = (object)array(
  		'allnew'    => array('All New','Our newest deliciously purposeful blends that support body and mind.','http://54.201.89.77/wp-content/uploads/2014/07/YT14-AllNew-Group-343x274.png'),
  		'energy'    => array('Energy','For a delicious and invigorating boost of energy.','http://54.201.89.77/wp-content/uploads/2014/07/YT14-Energy-Group-343x274.png'),
  		'greentea'  => array('Green Tea','Purposefully blended green teas support delicious well-being.','http://54.201.89.77/wp-content/uploads/2014/07/YT14-GreenTea-Group-343x274.png'),
  		'healthy'   => array('Healthy Glow','Delicious teas that support a healthy glow.','http://54.201.89.77/wp-content/uploads/2014/07/YT14-HealthyGlow-Group-343x274.png'),
  		'relax'     => array('Relaxation','Comforting and delicious blends to soothe the body and mind.','http://54.201.89.77/wp-content/uploads/2014/07/YT14-Relaxation-Group-343x274.png'),
  		'wellbeing' => array('Well-Being','Intriguingly complex blends support body\'s natural processes','http://54.201.89.77/wp-content/uploads/2014/07/YT14-WellBeing-Group-343x274.png')
  	);
  	
  	// Adding that token to the options table. 
  	// User will have to have that token on the end of their link for the link to work.
  	add_option('well_wish_token_' . $token, time());
  	
  	$well_wish     = ($ninja_forms_processing->get_field_value(55) != '') ? $ninja_forms_processing->get_field_value(55) : $ninja_forms_processing->get_field_value(53) ;
  	$tea_pair_key  = $ninja_forms_processing->get_field_value(90);
  	$new_recipient = array( $ninja_forms_processing->get_field_value(51) );
  	$new_subject   = $ninja_forms_processing->get_field_value(47) . " sent you a Yogi Well-Wish!";

	$new_message = str_replace('[USER_MESSAGE]'    ,$ninja_forms_processing->get_field_value(56),$ninja_forms_processing->get_form_setting('admin_email_msg'));
	$new_message = str_replace('[FROM_FIRST_NAME]' ,$ninja_forms_processing->get_field_value(47), $new_message);
	$new_message = str_replace('[TO_FIRST_NAME]'   ,$ninja_forms_processing->get_field_value(51), $new_message);
	$new_message = str_replace('[WELL_WISH]'       ,$well_wish, $new_message);
	$new_message = str_replace('[TEA_PAIR_HEAD]'   ,$tea_pairs->{$tea_pair_key}[0], $new_message);
	$new_message = str_replace('[TEA_PAIR_TEXT]'   ,$tea_pairs->{$tea_pair_key}[1], $new_message);
	$new_message = str_replace('[TEA_PAIR_IMG]'    ,$tea_pairs->{$tea_pair_key}[2], $new_message);
	$new_message = str_replace('[LINK]'            ,$link, $new_message);
	$new_message = str_replace('[TOKEN]'           ,$token, $new_message);
	
	//$new_message = preg_replace('/^\s+|\n|\r|\s+$/m', ' ', $new_message);
	
	// Remove default message
	$new_message = str_replace("Send a Message with your Well Wish","",$new_message);

    $ninja_forms_processing->update_form_setting( 'admin_mailto'   , $new_recipient );
    $ninja_forms_processing->update_form_setting( 'admin_subject'  , $new_subject   );
    $ninja_forms_processing->update_form_setting( 'admin_email_msg', $new_message   );
  }
}

add_action( 'ninja_forms_pre_process', 'ww_repurpose_admin_email' );






// Form #1: Retrieval of the Well Wish
///////////////////////////////////////////////


// Is it the right post (WW Retrieval form)? Is it legit?
function ww_check_for_token($content) {
	global $post;
	if (!is_single() || $post->ID != 4953) return $content;
	
	$well_wish_token = get_option('well_wish_token_' . $_GET['wwt']);

	// Let's see if it's legit...
	if (!$well_wish_token) {
		// Nope. Send 'em to the home page. Could make a setTimeout delay with a message... Or send 'em to the Well Wishes page... Punting...
		echo '<script type="text/javascript">alert("FPO: Text");window.location = "/";</script>';
	} else {
		// It is legit, so show the form.
		// Upon submission of _that_ form we will remove the token from the $wpdb->options table.
		return $content;
	}
}

add_filter('the_content', 'ww_check_for_token');


// See function name.
function ww_add_token_to_form() {
	global $ninja_forms_loading;
	$ninja_forms_loading->update_field_value( 88, $_GET['wwt'] );
}


// See function name.
function ww_destroy_token() {
	global $ninja_forms_processing;
	if( $ninja_forms_processing->get_form_ID() == 8 ){
		delete_option('well_wish_token_' . $ninja_forms_processing->get_field_value(88));
	}
}

add_action( 'ninja_forms_display_pre_init', 'ww_add_token_to_form' );
add_action( 'ninja_forms_pre_process',      'ww_destroy_token' );

?>
