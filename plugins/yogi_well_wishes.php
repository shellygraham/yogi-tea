<?php
/*
  Plugin Name: Yogi - Well Wishes
  Description: All the programming needed for the Yogi "Well Wishes" page.
  Author: Pop Art
  Version: 1.0
 */

function well_wishes_css () {
	$css = '
	
	<style>
		.ninja-forms-form-wrap {text-align:left;}
		h3.well-wish-header {
			clear:both;
		}
		.half-size-wrap, .half-size-right-wrap {
			width:49%;
			float:left;
			margin-right:1%;
		}
		.half-size-right-wrap {
			margin-right:-1% !important;
			width:50%;
		}
		.half-size-wrap select {
			width:100% !important;
			font-size:2em;
		}
	</style>
	
	';
}
?>
