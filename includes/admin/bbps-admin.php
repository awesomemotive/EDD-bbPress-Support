<?php
/**
 * Add admin metabox
 *
 * @since		1.0
 * @package		EDD\BBP\Admin\MetaBox
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Is this a support forum?
 *
 * @since		1.0
 * @param		int $forum_id The forum ID
 * @return		void
 */
function bbps_extend_forum_attributes_mb( $forum_id ) {

	$support_forum = edd_bbp_is_support_forum( $forum_id );
	?>

	<p>
		<strong><?php _e( 'Support Forum:', 'edd-bbpress-dashboard' ); ?></strong>
		<input type="checkbox" name="bbps-support-forum" value="1"<?php checked( true, $support_forum ); ?>/>
	</p>

<?php
}
add_action( 'bbp_forum_metabox', 'bbps_extend_forum_attributes_mb' );


/**
 * Save metabox
 *
 * @since		1.0
 * @param		int $forum_id The forum ID
 * @return		int $forum_id The forum ID
 */
function bbps_forum_attributes_mb_save( $forum_id ) {
	// get out the forum meta
	$support_forum = get_post_meta( $forum_id, '_bbps_is_support');

	// support options
	if( !empty( $_POST['bbps-support-forum'] ) ) {
		update_post_meta($forum_id, '_bbps_is_support', $_POST['bbps-support-forum']);
	} else {
		delete_post_meta( $forum_id, '_bbps_is_support' );
	}

	return $forum_id;
}
add_action( 'bbp_forum_attributes_metabox_save' , 'bbps_forum_attributes_mb_save' );
