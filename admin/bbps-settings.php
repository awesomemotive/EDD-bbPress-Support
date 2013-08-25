<?php


//register the settings
function bbps_register_admin_settings() {

	add_settings_section( 'bbps-status-setting',__( 'Support Froum Settings', 'bbps-forum' ), 'bbps_admin_setting_callback_status_section',  'bbpress' );

	// Add the forum status section

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

 	//the ability to assign a topic to a mod or admin
 	add_settings_field( '_bbps_topic_assign', __( 'Assign topics', 'bbps-forum' ), 'bbps_admin_setting_callback_assign_topic', 'bbpress', 'bbps-status-setting' );
 	register_setting  ( 'bbpress', '_bbps_topic_assign', 'intval');


}
add_action( 'bbp_register_admin_settings' , 'bbps_register_admin_settings' );

function bbps_admin_setting_callback_status_section(){
?>
	<p><?php _e( 'Enable and configure the settings for topic statuses these will be displayed on each topic', 'bbps-forum' ); ?></p>
<?php
}

function bbps_admin_setting_callback_support_forum_section(){
?>
	<p><?php _e( 'Enable and configure the settings for support forums, these options will be displayed on each topic within your support forums', 'bbps-forum' ); ?></p>
<?php

}

function bbps_admin_setting_callback_post_count(){
?>
	<input id="_bbps_enable_post_count" name="_bbps_enable_post_count" type="checkbox" <?php checked( edd_bbp_d_is_post_count_enabled(),1 ); ?> value="1" />
	<label for="_bbps_enable_post_count"><?php _e( 'Show the users post count below their gravatar?', 'bbpress' ); ?></label>
<?php
}

function bbps_admin_setting_callback_user_rank(){
?>
	<input id="bbps_enable_user_rank" name="_bbps_enable_user_rank" type="checkbox" <?php checked( edd_bbp_d_is_user_rank_enabled(),1 ); ?> value="1" />
	<label for="bbps_enable_user_rank"><?php _e( 'Display the users rank title below their gravatar?', 'bbpress' ); ?></label>
<?php
}

function bbps_admin_setting_callback_default_status(){
	$option = get_option('_bbps_default_status');
	?>
		<select name="_bbps_default_status" id="bbps_default_status">
			<option value="1" <?php  selected( $option, 1 ) ; ?> >not resolved</option>
			<option value="2" <?php  selected( $option, 2 ) ; ?> >resolved</option>
			<option value="3" <?php	 selected( $option, 3 ) ; ?> >not a support question</option>
		</select>
	<label for="bbps_default_status"><?php _e( 'This is the default status that will get displayed on all topics', 'bbpress' ); ?></label>
	<?php
}

function bbps_admin_setting_callback_displayed_status_res(){
?>
	<input id="_bbps_used_status[res]" name="_bbps_used_status[res]" <?php checked( edd_bbp_d_is_resolved_enabled(),1 ); ?> type="checkbox"  value="1" />
	<label for="_bbps_used_status[res]"><?php _e( 'Resolved', 'bbpress' ); ?></label>
<?php
}

function bbps_admin_setting_callback_displayed_status_notres(){
?>
	<input id="_bbps_used_status[notres]" name="_bbps_used_status[notres]" <?php checked( edd_bbp_d_is_not_resolved_enabled(),1 ); ?> type="checkbox"  value="1" />
	<label for="_bbps_used_status[notres]"><?php _e( 'Not Resolved', 'bbpress' ); ?></label>
<?php
}

function bbps_admin_setting_callback_displayed_status_notsup(){
?>
	<input id="_bbps_used_status[notsup]" name="_bbps_used_status[notsup]" <?php checked( edd_bbp_d_is_not_support_enabled(),1 ); ?> type="checkbox"  value="1" />
	<label for="_bbps_used_status[notsup]"><?php _e( 'Not a support question', 'bbpress' ); ?></label>
<?php
}

function bbps_admin_setting_callback_permission_admin(){
?>
	<input id="bbps_status_permissions" name="_bbps_status_permissions[admin]" <?php checked( edd_bbp_d_is_admin_enabled(),1 ); ?> type="checkbox"  value="1" />
	<label for="bbps_status_permissions"><?php _e( 'Allow the admin to update the topic status (recommended).', 'bbpress' ); ?></label>
<?php
}

function bbps_admin_setting_callback_permission_user(){
?>
	<input id="bbps_status_permissions" name="_bbps_status_permissions[user]" <?php checked( edd_bbp_d_is_user_enabled(),1 ); ?> type="checkbox"  value="1" />
	<label for="bbps_status_permissions"><?php _e( 'Allow the person who created the topic to update the status.', 'bbpress' ); ?></label>
<?php
}

function bbps_admin_setting_callback_permission_moderator(){
?>
	<input id="bbps_status_permissions" name="_bbps_status_permissions[mod]" <?php checked( edd_bbp_d_is_moderator_enabled(),1 ); ?> type="checkbox"  value="1" />
	<label for="bbps_status_permissions"><?php _e( 'Allow the forum moderators to update the post status.', 'bbpress' ); ?></label>
<?php
}

function bbps_admin_setting_callback_assign_topic(){
?>
	<input id="bbps_topic_assign" name="_bbps_topic_assign" <?php checked( edd_bbp_d_is_topic_assign_enabled(),1 ); ?> type="checkbox"  value="1" />
	<label for="bbps_topic_assign"><?php _e( 'Allow administrators and forum moderators to assign topics to other administrators and forum moderators', 'bbpress' ); ?></label>
<?php

}