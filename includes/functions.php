<?php
/**
 * Misc Functions
 *
 * @package		EDD\BBP\Functions
 * @since		2.1
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Is this a premium forum?
 *
 * @since		1.0.0
 * @param		int $forum_id The ID of the forum to check
 * @return		bool $return True if premium, false otherwise
 */
function edd_bbp_is_premium_forum( $forum_id ) {
	$premium_forum = get_post_meta( $forum_id, '_bbps_is_premium', true );

	if( $premium_forum == 1 ) {
		$return = true;
	} else {
		$return = false;
	}

	return apply_filters( 'edd_bbp_is_premium_forum', $return, $forum_id );
}


/**
 * Is this a support forum?
 *
 * @since		1.0.0
 * @param		int $forum_id The ID of the forum to check
 * @return		bool $return True if support forum, false otherwise
 */
function edd_bbp_is_support_forum( $forum_id ) {
	$support_forum = get_post_meta( $forum_id, '_bbps_is_support', true );

	if( $support_forum == 1 ) {
		$return = true;
	} else {
		$return = false;
	}

	return apply_filters( 'edd_bbp_is_support_forum', $return, $forum_id );
}


/**
 * Is this a premium topic?
 *
 * @since		1.0.0
 * @return		bool $return True if premium topic, false otherwise
 */
function edd_bbp_is_premium_topic() {
	$is_premium = get_post_meta( bbp_get_topic_forum_id(), '_bbps_is_premium' );

	if( $is_premium[0] ) {
		$return = true;
	} else {
		$return = false;
	}

	return $return;
}


/**
 * Is this topic marked as resolved?
 *
 * @since		1.0.0
 * @param		int $topic_id The ID of a topic to check
 * @return		bool $return True if resolved, false otherwise
 */
function edd_bbp_is_resolved( $topic_id ) {
	$is_resolved = get_post_meta( $topic_id, '_bbps_topic_status', true );

	if( $is_resolved == 2 ) {
		$return = true;
	} else {
		$return = false;
	}

	return $return;
}
