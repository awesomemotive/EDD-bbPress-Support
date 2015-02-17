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
 * The premium forum will create a support forum that can only be viewed by that user and admin users.
 *
 * @since		1.0
 * @param		int $forum_id The ID of this forum
 * @return		void
 */
function edd_bbp_extend_forum_attributes_mb( $forum_id ) {
	// Get out the forum meta
	$premium_forum = edd_bbp_is_premium_forum( $forum_id );

	if ( $premium_forum ) {
		$checked = 'checked';
	} else {
		$checked = '';
	}

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
	$premium_forum = get_post_meta( $forum_id, '_bbps_is_premium' );
	$support_forum = get_post_meta( $forum_id, '_bbps_is_support' );

	// If we have a value then save it
	if ( ! empty( $_POST['bbps-premium-forum'] ) ) {
		update_post_meta( $forum_id, '_bbps_is_premium', $_POST['bbps-premium-forum'] );
	}

	// The forum used to be premium now its not
	if ( ! empty( $premium_forum ) && empty( $_POST['bbps-premium-forum'] ) ) {
		update_post_meta( $forum_id, '_bbps_is_premium', 0 );
	}

	// Support options
	if ( ! empty( $_POST['bbps-support-forum'] ) ) {
		update_post_meta( $forum_id, '_bbps_is_support', $_POST['bbps-support-forum'] );
	}

	// The forum used to be premium now its not
	if ( ! empty( $premium_forum ) && empty( $_POST['bbps-support-forum'] ) ) {
		update_post_meta( $forum_id, '_bbps_is_support', 0 );
	}

	return $forum_id;
}
add_action( 'bbp_forum_attributes_metabox_save' , 'bbps_forum_attributes_mb_save' );


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
