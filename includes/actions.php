<?php
/**
 * Actions
 *
 * @package		EDD\BBP\Actions
 * @since		1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Process actions
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_actions() {
	if ( ! empty( $_POST['bbps_support_topic_assign'] ) ) {
		edd_bbp_assign_topic( $_POST );
	}

	if ( ! empty( $_POST['bbps_support_submit'] ) ) {
		edd_bbp_update_status( $_POST );
	}

	if ( ! empty( $_POST['bbps_topic_ping_submit'] ) ) {
		edd_bbp_ping_topic_assignee( $_POST );
	}
}
add_action( 'init', 'edd_bbp_actions' );
