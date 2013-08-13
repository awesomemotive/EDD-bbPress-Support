<?php
/**
 * Actions
 *
 * Copyright (c) 2013, Sunny Ratilal.
 */

function edd_bbp_d_process_actions() {
	if ( ! empty( $_POST['bbps_support_topic_assign'] ) ) {
		edd_bbp_d_assign_topic( $_POST );
	}

	if ( ! empty( $_POST['bbps_support_submit'] ) ) {
		edd_bbp_d_update_status( $_POST );
	}


	if ( ! empty( $_POST['bbps_topic_ping_submit'] ) ) {
		edd_bbp_d_ping_topic_assignee( $_POST );
	}

	if ( ( isset( $_GET['action'] ) && isset( $_GET['topic_id'] ) && $_GET['action'] == 'bbps_make_topic_urgent' )  )
		edd_bbp_d_urgent_topic();

	if ( ( isset( $_GET['action'] ) && isset( $_GET['topic_id'] ) && $_GET['action'] == 'bbps_make_topic_not_urgent' )  )
		edd_bbp_d_not_urgent_topic();
}
add_action( 'init', 'edd_bbp_d_process_actions' );
