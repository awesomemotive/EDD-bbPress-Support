<?php
/**
 * Common Functions
 */

/**
 * Checks if the current forum is a premium one
 *
 * @return bool
 */
function bbps_is_premium_forum( $forum_id ) {
	$premium_forum = get_post_meta( $forum_id, '_bbps_is_premium', true );

	if ( 1 == $premium_forum )
		return true;
	else
		return false;
}

/**
 * Checks if this is the support forum
 *
 * @param int     $forum_id
 * @return bool
 */
function bbps_is_support_forum( $forum_id ) {
	$support_forum = get_post_meta( $forum_id, '_bbps_is_support', true );

	if ( 1 == $support_forum )
		return true;
	else
		return false;
}

/**
 * Checks whether the topic is a premium topic
 *
 * @param int     $id
 * @return bool
 */
function bbps_is_topic_premium2( $id ) {
	$is_premium = get_post_meta( $id, '_bbps_is_premium' );

	if ( 1 == $is_premium[0] )
		return true;
	else
		return false;
}

/**
 * Checks whether the topic is a premium topic
 *
 * @return bool
 */
function bbps_is_topic_premium() {
	$is_premium = get_post_meta( bbp_get_topic_forum_id(), '_bbps_is_premium' );

	if ( 1 == $is_premium[0] )
		return true;
	else
		return false;
}

/**
 * Checks whether the reply is a premium
 *
 * @return bool
 */
function bbps_is_reply_premium() {
	$is_premium = get_post_meta( bbp_get_reply_forum_id(), '_bbps_is_premium' );

	if ( 1 == $is_premium[0] )
		return true;
	else
		return false;
}

/**
 * Gets all the premium topic IDs
 *
 * @return object $premuim_topics
 */
function bbps_get_all_premium_topic_ids() {
	global $wpdb;

	$forum_query = "SELECT `post_id` FROM ". $wpdb->postmeta ." WHERE `meta_key` = '_bbps_is_premium'" ;
	$premium_forums = $wpdb->get_col( $forum_query );

	$exclude = implode( ',', $premium_forums );
	$topics_query = "SELECT `id` FROM ". $wpdb->posts ." WHERE `post_parent` IN (".$exclude.")" ;
	$premium_topics = $wpdb->get_col( $topics_query );

	return $premium_topics;
}

/**
 * Displays a support forum drop down list of only forums that have been marked as premium
 */
function bbps_support_forum_ddl() {
	global $wpdb;

	$sql = "SELECT `post_id` FROM " . $wpdb->postmeta . " WHERE `meta_key` = '_bbps_is_premium' AND `meta_value` = '1'";
	$premium_forum_ids = $wpdb->get_col( $sql );

	$select = '<select id="bbp_forum_id" name="bbp_forum_id">';
	foreach ( $premium_forum_ids as $id ) {
		$select .= '<option value="' . $id . '">' . get_the_title( $id ) . '</option>';
	}
	$select .= '</select>';

	echo $select;
}

/**
 * Checks if the topic has been marked as resolved
 *
 * @return bool
 */
function bbps_topic_resolved( $topic_id ) {
	if ( 2 == get_post_meta( $topic_id, '_bbps_topic_status', true ) )
		return true;
	else
		return false;
}
