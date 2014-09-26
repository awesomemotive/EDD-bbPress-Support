<?php
/**
 * Admin Functions
 *
 * @package		EDD\BBP\Admin\Functions
 * @since		2.1
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * The support forum checkbox will add resolved / not resolved status to all forums.
 *
 * @since		1.0
 * @param		int $forum_id The ID of this forum
 * @return		void
 */
function edd_bbp_extend_forum_attributes_mb( $forum_id ) {
	$support_forum = edd_bbp_is_support_forum( $forum_id );

	if ( $support_forum ) {
		$checked1 = 'checked';
	} else {
		$checked1 = '';
	}
	?>
	<hr />

	<p>
		<strong>Support Forum:</strong>
		<input type="checkbox" name="bbps-support-forum" value="1" <?php echo $checked1; ?>/>
		<br />
	</p>
	<?php
}
add_action( 'bbp_forum_metabox' , 'bbps_extend_forum_attributes_mb' );


/**
 * Save the metabox
 *
 * @since		1.0
 * @param		int $forum_id The ID of this forum
 * @return		int $forum_id The ID of this forum
 */
function edd_bbp_forum_attributes_mb_save( $forum_id ) {
	//get out the forum meta
	$support_forum = get_post_meta( $forum_id, '_bbps_is_support' );

	// Support options
	if ( ! empty( $_POST['bbps-support-forum'] ) ) {
		update_post_meta( $forum_id, '_bbps_is_support', $_POST['bbps-support-forum'] );
	}

	return $forum_id;
}
add_action( 'bbp_forum_attributes_metabox_save' , 'bbps_forum_attributes_mb_save' );


/**
 * Register all the settings
 *
 * @since		1.0
 * @return		void
 */
function edd_bbp_register_admin_settings() {

	register_setting  ( 'bbpress', '_bbps_reply_count', 'edd_bbp_validate_options' );

	add_settings_field( '_bbps_enable_post_count', 'Show forum post count', 'edd_bbp_admin_setting_callback_post_count', 'bbpress', 'bbps-forum-setting' );
 	register_setting  ( 'bbpress', '_bbps_enable_post_count', 'intval');

	// Add the forum status section
	add_settings_section( 'bbps-status-setting', 'Topic Status Settings', 'edd_bbp_admin_setting_callback_status_section', 'bbpress' );

	/* support forum misc settings */
	add_settings_section( 'bbps-topic_status-setting', 'Support Froum Settings', 'edd_bbp_admin_setting_callback_support_forum_section', 'bbpress' );

	// the ability to move topics
 	add_settings_field( '_bbps_enable_topic_move', 'Move topics', 'edd_bbp_admin_setting_callback_move_topic', 'bbpress', 'bbps-topic_status-setting' );
 	register_setting  ( 'bbpress', '_bbps_enable_topic_move', 'intval');

 	// ability for admin and moderators to claim topics
 	add_settings_field( '_bbps_claim_topic', 'Claim topics', 'edd_bbp_admin_setting_callback_claim_topic', 'bbpress', 'bbps-topic_status-setting' );
 	register_setting  ( 'bbpress', '_bbps_claim_topic', 'intval');

 	add_settings_field( '_bbps_claim_topic_display', 'Display Username:', 'edd_bbp_admin_setting_callback_claim_topic_display', 'bbpress', 'bbps-topic_status-setting' );
 	register_setting  ( 'bbpress', '_bbps_claim_topic_display', 'intval');
}
add_action( 'bbp_register_admin_settings' , 'edd_bbp_register_admin_settings' );


/**
 * Checkbox validation callback
 *
 * @since		1.0
 * @param		array $input The field input
 * @return		array $newoptions The sanitized input
 */
function edd_bbp_validate_checkbox_group( $input ) {
    // Update only the needed options
    foreach ( $input as $key => $value ) {
        $newoptions[ $key ] = $value;
    }

    // Return all options
    return $newoptions;
}


/**
 * General validation callback
 *
 * @since		1.0
 * @param		array $input The field input
 * @return		array $newoptions The sanitized input
 */
function edd_bbp_validate_options( $input ) {
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
