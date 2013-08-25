<?php
/**
 * Common Functions
 */

/**
 * Checks if the current forum is a premium one
 *
 * @return bool
 */
function edd_bbp_d_is_premium_forum( $forum_id ) {
	$premium_forum = get_post_meta( $forum_id, '_bbps_is_premium', true );

	if ( 1 == $premium_forum )
		return true;
	else
		return false;
}

/**
 * Checks if this is the support forum
 *
 * @param int $forum_id
 * @return bool
 */
function edd_bbp_d_is_support_forum( $forum_id ) {
	$support_forum = get_post_meta( $forum_id, '_bbps_is_support', true );

	if ( 1 == $support_forum )
		return true;
	else
		return false;
}


/**
 * Checks whether the topic is a premium topic
 *
 * @return bool
 */
function edd_bbp_d_is_topic_premium() {
	$is_premium = get_post_meta( bbp_get_topic_forum_id(), '_bbps_is_premium' );

	if ( 1 == $is_premium[0] )
		return true;
	else
		return false;
}


/**
 * Checks if the topic has been marked as resolved
 *
 * @return bool
 */
function edd_bbp_d_topic_resolved( $topic_id ) {
	if ( 2 == get_post_meta( $topic_id, '_bbps_topic_status', true ) )
		return true;
	else
		return false;
}
