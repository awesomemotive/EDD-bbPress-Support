<?php

//hook into the forum atributes meta box

add_action('bbp_forum_metabox' , 'bbps_extend_forum_attributes_mb');

/* the support forum checkbox will add resolved / not resolved status to all forums */
/* The premium forum will create a support forum that can only be viewed by that user and admin users */
function bbps_extend_forum_attributes_mb($forum_id){

	$support_forum = edd_bbp_d_is_support_forum( $forum_id );
	?>

	<p>
		<strong><?php _e( 'Support Forum:', 'bbps' ); ?></strong>
		<input type="checkbox" name="bbps-support-forum" value="1"<?php checked( true, $support_forum ); ?>/>
	</p>

<?php
}

//hook into the forum save hook.

add_action( 'bbp_forum_attributes_metabox_save' , 'bbps_forum_attributes_mb_save' );

function bbps_forum_attributes_mb_save($forum_id){

	//get out the forum meta
	$support_forum = get_post_meta( $forum_id, '_bbps_is_support');

	//support options
	if ( !empty( $_POST['bbps-support-forum'] ) )
		update_post_meta($forum_id, '_bbps_is_support', $_POST['bbps-support-forum']);
	else
		delete_post_meta( $forum_id, '_bbps_is_support' );

	return $forum_id;

}


//register the settings
function bbps_register_admin_settings() {

	register_setting  ( 'bbpress', '_bbps_reply_count' );

	// show post count
	add_settings_field( '_bbps_enable_post_count', __( 'Show forum post count', 'bbps-forum' ), 'bbps_admin_setting_callback_post_count', 'bbpress', 'bbps-forum-setting' );
 	register_setting  ( 'bbpress', '_bbps_enable_post_count', 'intval');

	// Add the forum status section
	add_settings_section( 'bbps-status-setting',__( 'Topic Status Settings', 'bbps-forum' ), 'bbps_admin_setting_callback_status_section',  'bbpress' );

	register_setting  ( 'bbpress', '_bbps_default_status', 'intval' );
	add_settings_field( '_bbps_default_status', __( 'Default Status:', 'bbps-forum' ), 'bbps_admin_setting_callback_default_status', 'bbpress', 'bbps-status-setting' );


	// default topic option
	register_setting  ( 'bbpress', '_bbps_used_status' );
	// each drop down option for selection
	add_settings_field( '_bbps_used_status_1', __( 'Display Status:', 'bbps-forum' ), 'bbps_admin_setting_callback_displayed_status_res', 'bbpress', 'bbps-status-setting' );
	add_settings_field( '_bbps_used_status_2', __( 'Display Status:', 'bbps-forum' ), 'bbps_admin_setting_callback_displayed_status_notres', 'bbpress', 'bbps-status-setting' );
	add_settings_field( '_bbps_used_status_3', __( 'Display Status:', 'bbps-forum' ), 'bbps_admin_setting_callback_displayed_status_notsup', 'bbpress', 'bbps-status-setting' );

	// who can update the status
	register_setting  ( 'bbpress', '_bbps_status_permissions' );

	// each drop down option for selection
	add_settings_field( '_bbps_status_permissions_user', __( 'Topic Creator', 'bbps-forum' ), 'bbps_admin_setting_callback_permission_user', 'bbpress', 'bbps-status-setting' );

	/* support forum misc settings */
	add_settings_section( 'bbps-topic_status-setting',__( 'Support Froum Settings', 'bbps-forum' ), 'bbps_admin_setting_callback_support_forum_section',  'bbpress' );

	//the ability to move topics
 	add_settings_field( '_bbps_enable_topic_move', __( 'Move topics', 'bbps-forum' ), 'bbps_admin_setting_callback_move_topic', 'bbpress', 'bbps-topic_status-setting' );
 	register_setting  ( 'bbpress', '_bbps_enable_topic_move', 'intval');

 	//the ability to assign a topic to a mod or admin
 	add_settings_field( '_bbps_topic_assign', __( 'Assign topics', 'bbps-forum' ), 'bbps_admin_setting_callback_assign_topic', 'bbpress', 'bbps-topic_status-setting' );
 	register_setting  ( 'bbpress', '_bbps_topic_assign', 'intval');


}
add_action( 'bbp_register_admin_settings' , 'bbps_register_admin_settings' );