<?php
/**
 * Support Forum Functions
 */

function edd_bbp_d_add_support_forum_features() {
	if ( edd_bbp_d_is_support_forum( bbp_get_forum_id() ) ) {
		$topic_id = bbp_get_topic_id();
		$status = edd_bbp_d_get_topic_status( $topic_id );
	?>
	<div id="edd_bbp_d_support_forum_options" style="width: 100%;clear:both;">
		<?php
		if ( current_user_can( 'moderate' ) ) {
			edd_bbp_d_generate_status_options( $topic_id, $status );
		} else { ?>
			This topic is: <?php echo $status; ?>
		<?php } ?>
	</div>
	<?php
	}
}
add_action( 'bbp_template_before_single_topic', 'edd_bbp_d_add_support_forum_features' );

function edd_bbp_d_get_topic_status( $topic_id ) {
	$default = get_option( '_bbps_default_status' );

	$status = get_post_meta( $topic_id, '_bbps_topic_status', true );

	if ( $status )
		$switch = $status;
	else
		$switch = $default;

	switch ( $switch ) {
	case 1:
		return "not resolved";
		break;
	case 2:
		return "resolved";
		break;
	case 3:
		return "not a support question";
		break;
	}
}

/**
 * Generates a drop down list for administrators and moderators to change
 * the status of a forum topic
 */
function edd_bbp_d_generate_status_options( $topic_id ) {
	$dropdown_options = get_option( '_bbps_used_status' );
	$status = get_post_meta( $topic_id, '_bbps_topic_status', true );
	$default = get_option( '_bbps_default_status' );

	//only use the default value as selected if the topic doesnt ahve a status set
	if ( $status )
		$value = $status;
	else
		$value = $default;
	?>
	<form id="bbps-topic-status" name="bbps_support" action="" method="post">
		<label for="bbps_support_options">This topic is: </label>
		<select name="bbps_support_option" id="bbps_support_options">
		<?php
			// we only want to display the options the user has selected. the long term goal is to let users add their own forum statuses
			if ( $dropdown_options['res'] == 1 ) { ?> <option value="1" <?php selected( $value, 1 ) ; ?> >Not Resolved</option> <?php }
			if ( $dropdown_options['notres'] == 1 ) {?> <option value="2" <?php selected( $value, 2 ) ; ?> >Resolved</option> <?php }
			if ( $dropdown_options['notsup'] == 1 ) {?> <option value="3" <?php selected( $value, 3 ) ; ?> >Not a Support Question</option> <?php
		} ?>
		</select>
		<input type="submit" value="Update" name="bbps_support_submit" />
		<input type="hidden" value="bbps_update_status" name="bbps_action"/>
		<input type="hidden" value="<?php echo $topic_id ?>" name="bbps_topic_id" />
	</form>
	<?php
}

function edd_bbp_d_update_status() {
	$topic_id = absint( $_POST['bbps_topic_id'] );
	$status   = sanitize_text_field( $_POST['bbps_support_option'] );
	update_post_meta( $topic_id, '_bbps_topic_status', $status );
}

function edd_bbp_d_assign_topic_form() {
	if ( ( get_option( '_bbps_topic_assign' ) == 1 ) && ( current_user_can( 'administrator' ) || current_user_can( 'bbp_moderator' ) ) ) {
		$topic_id = bbp_get_topic_id();
		$topic_assigned = get_post_meta( $topic_id, 'bbps_topic_assigned', true );
		global $current_user;
		get_currentuserinfo();
		$current_user_id = $current_user->ID;
		?>
		<div id="bbps_support_forum_options">
			<?php
			$user_login = $current_user->user_login;
			if ( ! empty( $topic_assigned ) ) {
				if ( $topic_assigned == $current_user_id ) {
					?> <div class='bbps-support-forums-message'>This topic is assigned to you.</div><?php
				}
				else {
					$user_info = get_userdata( $topic_assigned );
					$assigned_user_name = $user_info->user_firstname . ' ' . $user_info->user_lastname;
					?>
					<div class='bbps-support-forums-message'> This topic is already assigned to: <?php echo $assigned_user_name; ?></div><?php
				}
			}
			?>
			<div id ="bbps_support_topic_assign">
				<form id="bbps-topic-assign" name="bbps_support_topic_assign" action="" method="post">
				<?php edd_bbp_d_user_assign_dropdown(); ?>
					<input type="submit" value="Assign" name="bbps_support_topic_assign" />
					<input type="hidden" value="bbps_assign_topic" name="bbps_action"/>
					<input type="hidden" value="<?php echo $topic_id ?>" name="bbps_topic_id" />
				</form>
			</div>
		</div><!-- /#bbps_support_forum_options -->
		<?php
	}

}
add_action( 'bbp_template_before_single_topic' , 'edd_bbp_d_assign_topic_form' );

function edd_bbp_d_user_assign_dropdown() {
	$wp_user_search = new WP_User_Query( array( 'role' => 'administrator' ) );
	$admins = $wp_user_search->get_results();

	$wp_user_search = new WP_User_Query( array( 'role' => 'bbp_moderator' ) );
	$moderators = $wp_user_search->get_results();

	$all_users = array_merge( $moderators, $admins );
	$topic_id = bbp_get_topic_id();
	$claimed_user_id = get_post_meta( $topic_id, 'bbps_topic_assigned', true );

	if ( ! empty( $all_users ) ) {
		if ( $claimed_user_id > 0 ) {
			$text = "Reassign topic to: ";
		} else {
			$text = "Assign topic to: ";
		}

		echo $text;
?>
		<select name="bbps_assign_list" id="bbps_support_options">
		<option value="">Unassigned</option><?php
		foreach ( $all_users as $user ) {
?>
			<option value="<?php echo $user->ID; ?>"> <?php echo $user->user_firstname . ' ' . $user->user_lastname ; ?></option>
		<?php
		}
		?> </select> <?php
	}

}

function edd_bbp_d_assign_topic() {
	$user_id  = absint( $_POST['bbps_assign_list'] );
	$topic_id = absint( $_POST['bbps_topic_id'] );

	if ( $user_id > 0 ) {
		$userinfo = get_userdata( $user_id );
		$user_email = $userinfo->user_email;
		$post_link = get_permalink( $topic_id );
		//add the user as a subscriber to the topic and send them an email to let them know they have been assigned to a topic
		bbp_add_user_subscription( $user_id, $topic_id );
		/*update the post meta with the assigned users id*/
		$assigned = update_post_meta( $topic_id, 'bbps_topic_assigned', $user_id );
		if ( $user_id != get_current_user_id() ) {
			$message = <<< EMAILMSG
		You have been assigned to the following topic, by another forum moderator or the site administrator. Please take a look at it when you get a chance.
		$post_link
EMAILMSG;
			if ( $assigned == true ) {
				wp_mail( $user_email, 'A forum topic has been assigned to you', $message );
			}
		}
	}
}

function edd_bbp_d_ping_topic_assignee() {
	$topic_id = absint( $_POST['bbps_topic_id'] );
	$user_id  = get_post_meta( $topic_id, 'bbps_topic_assigned', true );

	if ( $user_id ) {
		$userinfo = get_userdata( $user_id );
		$user_email = $userinfo->user_email;
		$post_link = get_permalink( $topic_id );
		$message = <<< EMAILMSG
		A ticket that has been assigned to you is in need of attention.
		$post_link
EMAILMSG;
		wp_mail( $user_email, 'EDD Ticket Ping', $message );
	}
}

function edd_bbp_d_ping_asignee_button() {
	if ( edd_bbp_d_is_support_forum( bbp_get_forum_id() ) ) {
		$topic_id = bbp_get_topic_id();
		$status = edd_bbp_d_get_topic_status( $topic_id );
		$forum_id = bbp_get_forum_id();
		$user_id = get_current_user_id();

		if ( current_user_can( 'moderate' ) ) {
?>
		<div id ="bbps_support_forum_ping">
			<form id="bbps-topic-ping" name="bbps_support_topic_ping" action="" method="post">
				<input type="submit" class="edd-submit button" value="Ping Assignee" name="bbps_topic_ping_submit" />
				<input type="hidden" value="bbps_ping_topic" name="bbps_action"/>
				<input type="hidden" value="<?php echo $topic_id ?>" name="bbps_topic_id" />
				<input type="hidden" value="<?php echo $forum_id ?>" name="bbp_old_forum_id" />
			</form>
		</div>
		<?php
		}
	}
}
add_action( 'bbp_template_before_single_topic', 'edd_bbp_d_ping_asignee_button' );

// adds a class and status to the front of the topic title
function edd_bbp_d_modify_title( $title, $topic_id = 0 ) {
	$topic_id = bbp_get_topic_id( $topic_id );
	//2 is the resolved status ID
	if ( get_post_meta( $topic_id, '_bbps_topic_status', true ) == 2 )
		echo '<span class="resolved"> [Resolved] </span>';
}
add_action( 'bbp_theme_before_topic_title', 'edd_bbp_d_modify_title' );


function edd_bbp_d_add_topic_status( $topic_id = 0, $topic ) {
	if ( $topic->post_type != 'topic' )
		return;

	$status = get_post_meta( $topic_id, '_bbps_topic_status', true );

	if ( ! $status )
		add_post_meta( $topic_id, '_bbps_topic_status', 1 );
}
add_action( 'wp_insert_post', 'edd_bbp_d_add_topic_status', 10, 2 );

function edd_bbp_d_set_as_pending( $post ) {
	if ( $post->post_type != 'topic' )
		return;

	add_post_meta( $post->ID, '_bbps_topic_pending', 1 );
}
add_action( 'new_to_publish',     'edd_bbp_d_set_as_pending' );
add_action( 'draft_to_publish',   'edd_bbp_d_set_as_pending' );
add_action( 'pending_to_publish', 'edd_bbp_d_set_as_pending' );

function edd_bbp_d_maybe_remove_pending( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author, $something, $reply_to ) {
	if ( current_user_can( 'moderate' ) )
		delete_post_meta( $topic_id, '_bbps_topic_pending' );
	else
		update_post_meta( $topic_id, '_bbps_topic_pending', '1' );
}
add_action( 'bbp_new_reply', 'edd_bbp_d_maybe_remove_pending', 10, 7 );

function edd_bbp_d_force_remove_pending() {
	if ( ! isset( $_GET['topic_id'] ) )
		return;
	if ( ! isset( $_GET['bbps_action'] ) || $_GET['bbps_action'] != 'remove_pending' )
		return;
	if ( ! current_user_can( 'moderate' ) )
		return;

	delete_post_meta( $_GET['topic_id'], '_bbps_topic_pending' );
	wp_redirect( remove_query_arg( array( 'topic_id', 'bbps_action' ) ) ); exit;
}
add_action( 'init', 'edd_bbp_d_force_remove_pending' );

function edd_bbp_d_add_user_purchases_link() {
	if ( ! current_user_can( 'moderate' ) )
		return;

	if ( ! function_exists( 'edd_get_users_purchases' ) )
		return;

	$user_email = bbp_get_displayed_user_field( 'user_email' );

	echo '<div class="edd_users_purchases">';
	echo '<h4>User\'s Purchases:</h4>';
	$purchases = edd_get_users_purchases( $user_email );
	if ( $purchases ) :
		echo '<ul>';
	foreach ( $purchases as $purchase ) {
		$downloads = edd_get_payment_meta_downloads( $purchase->ID );
		foreach ( $downloads as $download ) {
			echo '<li>' . get_the_title( $download['id'] ) . ' - ' . date( 'F j, Y', strtotime( $purchase->post_date ) ) . '</li>';
		}
	}
	echo '</ul>';
	else :
		echo '<p>This user has never purchased anything.</p>';
	endif;
	echo '</div>';
}
add_action( 'bbp_template_after_user_profile', 'edd_bbp_d_add_user_purchases_link' );

function edd_bbp_d_add_user_priority_support_status() {
	if ( ! current_user_can( 'moderate' ) )
		return;

	if ( ! function_exists( 'rcp_get_status' ) )
		return;

	$user_id = bbp_get_displayed_user_field( 'ID' );

	echo '<div class="rcp_support_status">';
	echo '<h4>Priority Support Access</h4>';
	if ( rcp_is_active( $user_id ) ) {
		echo '<p>Has <strong>Priority Support</strong> access.</p>';
	} elseif ( rcp_is_expired( $user_id ) ) {
		echo '<p><strong>Priority Support</strong> access has <span style="color:red;">expired</span>.</p>';
	} else {
		echo '<p>Has no priority support accesss</p>';
	}

	echo '</div>';
}
add_action( 'bbp_template_after_user_profile', 'edd_bbp_d_add_user_priority_support_status' );


function edd_bbp_d_reply_and_resolve( $reply_id = 0, $topic_id = 0, $forum_id = 0, $anonymous_data = false, $author_id = 0, $is_edit = false ) {
	if ( isset( $_POST['bbp_reply_close'] ) ) {
		update_post_meta( $topic_id, '_bbps_topic_status', 2 );
	}

	if ( isset( $_POST['bbp_reply_open'] ) ) {
		update_post_meta( $topic_id, '_bbps_topic_status', 1 );
	}
}
add_action( 'bbp_new_reply', 'edd_bbp_d_reply_and_resolve', 0, 6 );

/**
 * Creates the toggle for the action links dropdown
 */
function edd_bbp_d_action_links_dropdown() {
	$reply_id = bbp_get_reply_id();

	// If post is not a reply, return
	if ( ! bbp_is_reply( $reply_id ) && ! bbp_is_topic( $reply_id ) )
		return;

	// Make sure user can edit this reply
	if ( ! current_user_can( 'edit_reply', $reply_id ) )
		return;

	// If topic is trashed, do not show admin links
	if ( bbp_is_topic_trash( bbp_get_reply_topic_id( $reply_id ) ) )
		return;

?>
	<button class="bbp-action-links-dropdown-toggle" data-toggle="dropdown"><span class="filter-option pull-left">Actions</span> <i class="icon icon-angle-down"></i></button>
	<i class="icon-caret-up icon"></i>
	<?php
}
add_action( 'bbp_theme_before_reply_admin_links', 'edd_bbp_d_action_links_dropdown' );

function edd_bbp_d_sidebar() {
	global $post;

	$user_id = get_the_author_meta( 'ID' );

?>
	<div class="box">
		<h3><?php echo get_the_author_meta( 'first_name' ) . '  ' . get_the_author_meta( 'last_name' ); ?></h3>
		<p class="bbp-user-forum-role"><?php  printf( __( 'Forum Role: %s',      'bbpress' ), bbp_get_user_display_role( $user_id )    ); ?></p>
		<p class="bbp-user-topic-count"><?php printf( __( 'Topics Started: %s',  'bbpress' ), bbp_get_user_topic_count_raw( $user_id ) ); ?></p>
		<p class="bbp-user-reply-count"><?php printf( __( 'Replies Created: %s', 'bbpress' ), bbp_get_user_reply_count_raw( $user_id ) ); ?></p>

		<div class="rcp_support_status">
			<h4>Priority Support Access</h4>
			<?php if ( function_exists( 'rcp_is_active' ) ) { if ( rcp_is_active( $user_id ) ) { ?>
				<p>Has <strong>Priority Support</strong> access.</p>
			<?php } elseif ( rcp_is_expired( $user_id ) ) { ?>
				<p><strong>Priority Support</strong> access has <span style="color:red;">expired</span>.</p>
			<?php } else { ?>
				<p>Has no priority support accesss</p>
			<?php } } ?>
		</div><!-- /.rcp_support_status -->
	</div>
	<?php
}
