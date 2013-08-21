<?php
/*
bbps-user-ranking
contains functions relating to the user ranking
this file will grow as we extend our forum user options
to include things like upload badges for your rankings etc!
*/

//update the user post count meta everytime the user creates a new post
function edd_bbp_d_increament_post_count(){
	global $current_user;

	$post_type = get_post_type();

	// Bail unless we are creating topics or replies
	if ( $post_type == 'topic' || $post_type == 'reply' ){

		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		$user_rank = get_user_meta($user_id, '_bbps_rank_info');

		 //if this is their first post
		if ( empty($user_rank[0]) )
			bbps_create_user_ranking_meta($user_id);

		edd_bbp_d_check_ranking($user_id);

	}
	return;

}
add_action( 'save_post','edd_bbp_d_increament_post_count' );

function edd_bbp_d_check_ranking( $user_id ) {
	$user_rank    = get_user_meta( $user_id, '_bbps_rank_info' );

	$post_count   = $user_rank[0]['post_count'];
	$current_rank = $user_rank[0]['current_ranking'];

	$post_count   = $post_count + 1;
	$rankings     = get_option('_bbps_reply_count');

	foreach ( (array) $rankings as $rank ) {
		//if post count == the end value then this title no longer applies so remove it
		//we subtract one here to allow for the between number eg between 1 - 4 we still
		//want to dispaly the title if the post count is 4
		if ( $post_count - 1 == $rank['end'] ) $current_rank = "";

		if ( $post_count == $rank['start'] ) $current_rank = $rank['title'];
	}

	$meta = array(
		'post_count'      => $post_count,
		'current_ranking' => $current_rank
	);

	update_user_meta( $user_id, '_bbps_rank_info', $meta );
}

/*
function bbps_create_user_ranking_meta
called by bbps_increament_post_count function, this will create the usermeta if this is their first post
uses:
@param $user_id - The User Id to create the meta for.
@function get_option to get out all the rank info
@function bbp_get_reply_author_id to get the user id for the reply / topic
@function update_user_meta to create the user meta rank info
@return nothing
*/
function edd_bbp_d_create_user_ranking_meta( $user_id ) {
	$rankings = get_option( '_bbps_reply_count' );

	$meta = array(
		'post_count'      => '0',
		'current_ranking' => ''
	);

	update_user_meta( $user_id, '_bbps_rank_info', $meta);
}

/*
function bbps_display_user_title
called by the bbp_theme_after_reply_author_details hook in bbpress 2.0
uses:
@function get_option to check if it should show the rank
@function bbp_get_reply_author_id to get the user id for the reply / topic
@function get_user_meta to get the users rank info
@return nothing
*/
function edd_bbp_d_display_user_title() {
	if ( 1 == get_option( '_bbps_enable_user_rank' ) ) {
		$user_id   = bbp_get_reply_author_id();
		$user_rank = get_user_meta( $user_id, '_bbps_rank_info' );

		if ( ! empty( $user_rank[0]['current_ranking'] ) )
			echo '<div id ="bbps-user-title">'.$user_rank[0]['current_ranking'] . '</div>';
	}
}

/*
function bbps_display_user_post_count
called by the bbp_theme_after_reply_author_details hook in bbpress 2.0
uses:
@function get_option to check if it should show the post count
@function bbp_get_reply_author_id to get the user id for the reply / topic
@function get_user_meta to get the users rank info
@return nothing
*/
function edd_bbp_d_display_user_post_count() {
	if ( 1 == get_option( '_bbps_enable_post_count') && current_user_can( 'moderate' ) ) {
		$user_id   = bbp_get_reply_author_id();
		$user_rank = get_user_meta( $user_id, '_bbps_rank_info' );

		if ( ! empty( $user_rank[0]['post_count'] ) )
			echo '<div id ="bbps-post-count">Post count: '.$user_rank[0]['post_count'] . '</div>';
	}
}

/*
function bbps_display_trusted_tag
called by the bbp_theme_after_reply_author_details hook in bbpress 2.0,
will display a trusted tag below the site administrators and bp-moderators gravitar
uses:
@function get_option to check if it should show the trusted message
@function bbp_get_reply_author_id to get the user id for the reply / topic
@function get_userdata to get the users capabilities
@return nothing
*/
function edd_bbp_d_display_trusted_tag() {
	$user_id = bbp_get_reply_author_id();
	$user    = get_userdata( $user_id );

	if ( get_option('_bbps_enable_trusted_tag')== 1 && ( ( ! empty( $user->wp_capabilities['administrator'] ) == 1 ) || ( ! empty( $user->wp_capabilities['bbp_moderator'] ) == 1 ) ) )
		echo '<div id ="trusted"><em>Trusted</em></div>';
}

add_action( 'bbp_theme_after_reply_author_details', 'edd_bbp_d_display_user_title'      );
add_action( 'bbp_theme_after_reply_author_details', 'edd_bbp_d_display_user_post_count' );
add_action( 'bbp_theme_after_reply_author_details', 'edd_bbp_d_display_trusted_tag'     );