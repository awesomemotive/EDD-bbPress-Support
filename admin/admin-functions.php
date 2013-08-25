<?php
/**
 * Admin Functions
 */

/**
 * The support forum checkbox will add resolved / not resolved status to all forums.
 * The premium forum will create a support forum that can only be viewed by that user and admin users.
 *
 * @since 1.0
 */
function edd_bbp_d_extend_forum_attributes_mb( $forum_id ) {
	// Get out the forum meta
	$premium_forum = edd_bbp_d_is_premium_forum( $forum_id );

	if ( $premium_forum )
		$checked = "checked";
	else
		$checked = "";

	$support_forum = edd_bbp_d_is_support_forum( $forum_id );

	if ( $support_forum )
		$checked1 = "checked";
	else
		$checked1 = "";
	?>
	<hr />

	<p>
		<strong><?php _e( 'Support Forum:', 'bbps' ); ?></strong>
		<input type="checkbox" name="bbps-support-forum" value="1" <?php echo $checked1; ?>/>
		<br />
	</p>
	<?php
}
add_action( 'bbp_forum_metabox' , 'bbps_extend_forum_attributes_mb' );

function edd_bbp_d_forum_attributes_mb_save( $forum_id ) {
	//get out the forum meta
	$premium_forum = get_post_meta( $forum_id, '_bbps_is_premium' );
	$support_forum = get_post_meta( $forum_id, '_bbps_is_support' );

	// If we have a value then save it
	if ( ! empty( $_POST['bbps-premium-forum'] ) )
		update_post_meta( $forum_id, '_bbps_is_premium', $_POST['bbps-premium-forum'] );

	// The forum used to be premium now its not
	if ( ! empty( $premium_forum ) && empty( $_POST['bbps-premium-forum'] ) )
		update_post_meta( $forum_id, '_bbps_is_premium', 0 );

	// Support options
	if ( ! empty( $_POST['bbps-support-forum'] ) )
		update_post_meta( $forum_id, '_bbps_is_support', $_POST['bbps-support-forum'] );

	//the forum used to be premium now its not
	if ( ! empty( $premium_forum ) && empty( $_POST['bbps-support-forum'] ) )
		update_post_meta( $forum_id, '_bbps_is_support', 0 );

	return $forum_id;
}
add_action( 'bbp_forum_attributes_metabox_save' , 'bbps_forum_attributes_mb_save' );

/**
 * Register all the settings
 */
function edd_bbp_d_register_admin_settings() {

	register_setting  ( 'bbpress', '_bbps_reply_count', 'edd_bbp_d_validate_options' );

	add_settings_field( '_bbps_enable_post_count', __( 'Show forum post count', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_post_count',      'bbpress', 'bbps-forum-setting' );
 	register_setting  ( 'bbpress', '_bbps_enable_post_count', 'intval');

	add_settings_field( '_bbps_enable_user_rank', __( 'Show Rank', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_user_rank',      'bbpress', 'bbps-forum-setting' );
 	register_setting  ( 'bbpress', '_bbps_enable_user_rank', 'intval');

	// Add the forum status section
	add_settings_section( 'bbps-status-setting',                __( 'Topic Status Settings',           'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_status_section',  'bbpress'             );

	register_setting  ( 'bbpress', '_bbps_default_status', 'intval' );
	add_settings_field( '_bbps_default_status', __( 'Default Status:', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_default_status', 'bbpress', 'bbps-status-setting' );

	// default topic option
	register_setting  ( 'bbpress', '_bbps_used_status', 'bbps_validate_checkbox_group' );

	// each drop down option for selection
	add_settings_field( '_bbps_used_status_1', __( 'Display Status:', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_displayed_status_res', 'bbpress', 'bbps-status-setting' );
	add_settings_field( '_bbps_used_status_2', __( 'Display Status:', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_displayed_status_notres', 'bbpress', 'bbps-status-setting' );
	add_settings_field( '_bbps_used_status_3', __( 'Display Status:', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_displayed_status_notsup', 'bbpress', 'bbps-status-setting' );

	// who can update the status
	register_setting  ( 'bbpress', '_bbps_status_permissions', 'bbps_validate_checkbox_group' );
	// each drop down option for selection
	add_settings_field( '_bbps_status_permissions_admin', __( 'Admin', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_permission_admin', 'bbpress', 'bbps-status-setting' );
	add_settings_field( '_bbps_status_permissions_user', __( 'Topic Creator', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_permission_user', 'bbpress', 'bbps-status-setting' );
	add_settings_field( '_bbps_status_permissions_moderator', __( 'Forum Moderator', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_permission_moderator', 'bbpress', 'bbps-status-setting' );

	/* support forum misc settings */
	add_settings_section( 'bbps-topic_status-setting',                __( 'Support Froum Settings',           'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_support_forum_section',  'bbpress'             );

	register_setting  ( 'bbpress', '_bbps_status_permissions_urgent', 'intval' );
	// each drop down option for selection
	add_settings_field( '_bbps_status_permissions_urgent', __( 'Urgent Topic Status', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_urgent', 'bbpress', 'bbps-topic_status-setting' );

	//the ability to move topics
 	add_settings_field( '_bbps_enable_topic_move', __( 'Move topics', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_move_topic',      'bbpress', 'bbps-topic_status-setting' );
 	register_setting  ( 'bbpress', '_bbps_enable_topic_move', 'intval');

 	//the ability to assign a topic to a mod or admin
 	add_settings_field( '_bbps_topic_assign', __( 'Assign topics', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_assign_topic',      'bbpress', 'bbps-topic_status-setting' );
 	register_setting  ( 'bbpress', '_bbps_topic_assign', 'intval');

 	//ability for admin and moderators to claim topics
 	add_settings_field( '_bbps_claim_topic', __( 'Claim topics', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_claim_topic',      'bbpress', 'bbps-topic_status-setting' );
 	register_setting  ( 'bbpress', '_bbps_claim_topic', 'intval');

 	add_settings_field( '_bbps_claim_topic_display', __( 'Display Username:', 'bbps-forum' ), 'edd_bbp_d_admin_setting_callback_claim_topic_display',      'bbpress', 'bbps-topic_status-setting' );
 	register_setting  ( 'bbpress', '_bbps_claim_topic_display', 'intval');
}
add_action( 'bbp_register_admin_settings' , 'edd_bbp_d_register_admin_settings' );

function edd_bbp_d_validate_checkbox_group( $input ) {
    // Update only the needed options
    foreach ( $input as $key => $value ) {
        $newoptions[ $key ] = $value;
    }

    // Return all options
    return $newoptions;
}

function edd_bbp_d_validate_options( $input ) {
	$options = get_option('_bbps_reply_count');

	$i = 1;

	foreach ( $input as $array ) {
		foreach ( $array as $key => $value ) {
		      $options[ $i ][ $key ] = $value;
		}
		$i++;
	}

    return $options;
}