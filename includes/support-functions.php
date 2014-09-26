<?php
/**
 * Support Forum Functions
 *
 * @package		EDD\BBP\SupportFunctions
 * @since		2.1
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Get array of all forum mods
 *
 * @since		1.0.0
 * @param		bool $admins_only Return only admins
 * @return		array $staff The array of mods
 */
function edd_bbp_get_all_mods( $admins_only = false ) {
	$wp_user_search = new WP_User_Query( array( 'role' => 'administrator' ) );
	$staff = $wp_user_search->get_results();

	if( ! $admins_only ) {
		$wp_user_search = new WP_User_Query( array( 'role' => 'bbp_moderator' ) );
		$moderators = $wp_user_search->get_results();

		$staff = array_merge( $moderators, $staff );
	}

	return $staff;
}

/**
 * Get array of all forum mods
 *
 * Backwards compat
 *
 * @since		1.0.0
 * @param		bool $admins_only Return only admins
 * @return		array $staff The array of mods
 */
function edd_bbp_d_get_all_mods( $admins_only = false ) {
	return edd_bbp_get_all_mods( $admins_only );
}


/**
 * Get forum topic status
 *
 * @since		1.0.0
 * @param		int $topic_id The ID of the topic
 * @return		string $status The status of the topic
 */
function edd_bbp_get_topic_status( $topic_id ) {
	$default = 1;

	$status = get_post_meta( $topic_id, '_bbps_topic_status', true );

	if ( $status )
		$switch = $status;
	else
		$switch = $default;

	switch ( $switch ) {
		case 1:
			$status = 'not resolved';
			break;
		case 2:
			$status = 'resolved';
			break;
		case 3:
			$status = 'not a support question';
			break;
	}

	return $status;
}


/**
 * Get forum topic status - Backwards compat version
 *
 * @since		1.0.0
 * @param		int $topic_id The ID of the topic
 * @return		string $status The status of the topic
 */
function edd_bbp_d_get_topic_status( $topic_id ) {
	return edd_bbp_get_topic_status( $topic_id );
}


/**
 * Generates a drop down list for administrators and moderators to change
 * the status of a forum topic
 *
 * @since		1.0.0
 * @param		int $topic_id The ID of the topic
 * @return		void
 */
function edd_bbp_generate_status_options( $topic_id ) {
	$status = get_post_meta( $topic_id, '_bbps_topic_status', true );
	$default = 1;

	// Only use the default value as selected if the topic doesnt ahve a status set
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
			echo '<option value="1" ' . selected( $value, 1 ) . '>Not Resolved</option>';
			echo '<option value="2" ' . selected( $value, 2 ) . '>Resolved</option>';
			echo '<option value="3" ' . selected( $value, 3 ) . '>Not a Support Question</option>';
		?>
		</select>
		<input type="submit" value="Update" name="bbps_support_submit" />
		<input type="hidden" value="bbps_update_status" name="bbps_action"/>
		<input type="hidden" value="<?php echo $topic_id ?>" name="bbps_topic_id" />
	</form>
	<?php
}

/**
 * Generates a drop down list for administrators and moderators to change
 * the status of a forum topic
 *
 * Backwards compay version
 *
 * @since		1.0.0
 * @param		int $topic_id The ID of the topic
 * @return		void
 */
function edd_bbp_d_generate_status_options( $topic_id ) {
	edd_bbp_generate_status_options( $topic_id );
}


/**
 * Process status updates
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_update_status() {
	$topic_id = absint( $_POST['bbps_topic_id'] );
	$status   = sanitize_text_field( $_POST['bbps_support_option'] );
	update_post_meta( $topic_id, '_bbps_topic_status', $status );
}


/**
 * Count the number of assigned tickets for a given mod
 *
 * @since		1.0.0
 * @param		int $mod_id The ID of a given mod
 * @return		int The number of assigned tickets
 * @todo		This function is known to be buggy!
 */
function edd_bbp_count_tickets_of_mod( $mod_id = 0 ) {
	$args = array(
		'post_type' => 'topic',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => 'bbps_topic_assigned',
				'value' => $mod_id,
			),
			array(
				'key' => '_bbps_topic_status',
				'value' => '1'
			)
		),
		'nopaging' => true,
		'post_parent__not_in' => array( 318 )
	);

	$query = new WP_Query( $args );

	return $query->post_count;
}


/**
 * Assign a forum topic
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_assign_topic_form() {
	if ( ! current_user_can( 'moderate' ) ) {
		return;
	}

	$topic_id = bbp_get_topic_id();
	$topic_assigned = edd_bbp_get_topic_assignee_id( $topic_id );

	global $current_user;
	get_currentuserinfo();
	$current_user_id = $current_user->ID;
	?>

	<div class="moderator-tools clearfix">

		<div id="bbps_support_forum_options">
			<?php
			$user_login = $current_user->user_login;
			if ( ! empty( $topic_assigned ) ) {
				$assigned_user_name = edd_bbp_get_topic_assignee_name( $topic_assigned ); ?>
				<div class='bbps-support-forums-message'> Topic assigned to: <?php echo $assigned_user_name; ?></div><?php
			}
			?>
			<div id="bbps_support_topic_assign">
				<form id="bbps-topic-assign" name="bbps_support_topic_assign" action="" method="post">
					<?php
					$all_users       = edd_bbp_get_all_mods();
					$topic_id        = bbp_get_topic_id();
					$claimed_user_id = get_post_meta( $topic_id, 'bbps_topic_assigned', true );

					if ( ! empty( $all_users ) ) : ?>
						<select name="bbps_assign_list" id="bbps_support_options">
							<option value="">Unassigned</option>
							<?php foreach ( $all_users as $user ) : ?>
								<option value="<?php echo $user->ID; ?>"<?php selected( $user->ID, $claimed_user_id ); ?>><?php echo $user->user_firstname . ' ' . $user->user_lastname ; ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
					<input type="submit" value="Assign" name="bbps_support_topic_assign" />
					<input type="hidden" value="bbps_assign_topic" name="bbps_action"/>
					<input type="hidden" value="<?php echo $topic_id ?>" name="bbps_topic_id" />
				</form>
				<form id="bbs-topic-assign-me" name="bbps_support_topic_assign" action="" method="post">
					<input type="submit" value="Assign To Me" name="bbps_support_topic_assign" />
					<input type="hidden" value="<?php echo get_current_user_id(); ?>" name="bbps_assign_list" />
					<input type="hidden" value="bbps_assign_topic" name="bbps_action"/>
					<input type="hidden" value="<?php echo $topic_id ?>" name="bbps_topic_id" />
				</form>
			</div>
		</div><!-- /#bbps_support_forum_options -->
	</div>
	<?php
}
add_action( 'bbp_template_before_single_topic' , 'edd_bbp_assign_topic_form' );

/**
 * Send message on ticket assignment
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_assign_topic() {
	$user_id  = absint( $_POST['bbps_assign_list'] );
	$topic_id = absint( $_POST['bbps_topic_id'] );

	if ( $user_id > 0 ) {
		$userinfo = get_userdata( $user_id );
		$user_email = $userinfo->user_email;
		$post_link = get_permalink( $topic_id );
		// Add the user as a subscriber to the topic and send them an email to let them know they have been assigned to a topic
		bbp_add_user_subscription( $user_id, $topic_id );
		// Update the post meta with the assigned users id
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


/**
 * Ping topic assignee
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_ping_topic_assignee() {
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

/**
 * Ping topic assignee - Backwards compat
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_d_ping_asignee_button() {
	edd_bbp_ping_topic_assignee();
}


/**
 * Print the Ping Assignee button
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_ping_assignee_button() {
	if ( edd_bbp_is_support_forum( bbp_get_forum_id() ) ) {
		$topic_id = bbp_get_topic_id();
		$topic_assigned = edd_bbp_get_topic_assignee_id( $topic_id );
		$status = edd_bbp_get_topic_status( $topic_id );
		$forum_id = bbp_get_forum_id();
		$user_id = get_current_user_id();

		if ( current_user_can( 'moderate' ) && $topic_assigned ) {
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
add_action( 'bbp_template_before_single_topic', 'edd_bbp_ping_assignee_button' );


/**
 * Adds a class and status to topic title
 *
 * @since		1.0.0
 * @param		string $title The topic title
 * @param		int $topic_id The ID of this topic
 * @return		void
 */
function edd_bbp_modify_title( $title, $topic_id = 0 ) {
	$topic_id = bbp_get_topic_id( $topic_id );

	// 2 is the resolved status ID
	if ( get_post_meta( $topic_id, '_bbps_topic_status', true ) == 2 ) {
		echo '<span class="resolved">[Resolved] </span>';
	}
}
add_action( 'bbp_theme_before_topic_title', 'edd_bbp_modify_title' );


/**
 * Add topic meta
 *
 * @since		1.0.0
 * @param		int $topic_id The ID of this topic
 * @param		object $topic The object of this topic
 * @return		mixed
 */
function edd_bbp_add_topic_meta( $topic_id = 0, $topic ) {
	// Bail if this isn't a support topic
	if ( $topic->post_type != 'topic' ) {
		return;
	}

	$status = get_post_meta( $topic_id, '_bbps_topic_status', true );

	if ( ! $status ) {
		add_post_meta( $topic_id, '_bbps_topic_status', '1' );
	}

	add_post_meta( $topic_id, '_bbps_topic_pending', '1' );
}
add_action( 'wp_insert_post', 'edd_bbp_add_topic_meta', 10, 2 );


/**
 * Remove pending status?
 *
 * @since		1.0.0
 * @param		int $reply_id The ID of this reply
 * @param		int $topic_id The ID of the topic this belongs to
 * @param		int $forum_id The ID of the parent forum
 * @param		array $anonymous_data
 * @param		object $reply_author The author of this reply
 * @return		void
 */
function edd_bbp_maybe_remove_pending( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author ) {
	if( user_can( $reply_author, 'moderate' ) ) {
		// If the new reply is posted by the assignee, remove the pending flag
		delete_post_meta( $topic_id, '_bbps_topic_pending' );
	} else {
		// If the reply is posted by anyone else, add the pending reply
		update_post_meta( $topic_id, '_bbps_topic_pending', '1' );
	}
}
add_action( 'bbp_new_reply', 'edd_bbp_maybe_remove_pending', 20, 5 );


/**
 * Remove pending flag
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_bulk_remove_pending() {
	if( ! current_user_can( 'moderate' ) ) {
		return;
	}

	if( empty( $_POST['tickets'] ) ) {
		return;
	}

	$tickets = array_map( 'absint', $_POST['tickets'] );

	foreach( $tickets as $ticket ) {
		delete_post_meta( $ticket, '_bbps_topic_pending' );
	}
}
add_action( 'edd_remove_ticket_pending_status', 'edd_bbp_bulk_remove_pending', 20, 5 );


/**
 * Auto-assign tickets on reply
 *
 * @since		1.0.0
 * @param		int $reply_id The ID of this reply
 * @param		int $topic_id The ID of the topic this belongs to
 * @param		int $forum_id The ID of the parent forum
 * @param		array $anonymous_data
 * @param		object $reply_author The author of this reply
 * @return		void
 */
function edd_bbp_assign_on_reply( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author ) {
	if ( ! edd_bbp_get_topic_assignee_id( $topic_id ) && user_can( $reply_author, 'moderate' ) ) {
		update_post_meta( $topic_id, 'bbps_topic_assigned', $reply_author );
	}
}
add_action( 'bbp_new_reply', 'edd_bbp_assign_on_reply', 20, 5 );


/**
 * Force remove pending
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_force_remove_pending() {
	if ( ! isset( $_GET['topic_id'] ) ) {
		return;
	} elseif ( ! isset( $_GET['bbps_action'] ) || $_GET['bbps_action'] != 'remove_pending' ) {
		return;
	} elseif ( ! current_user_can( 'moderate' ) ) {
		return;
	}

	delete_post_meta( $_GET['topic_id'], '_bbps_topic_pending' );
	wp_redirect( remove_query_arg( array( 'topic_id', 'bbps_action' ) ) ); exit;
}
add_action( 'init', 'edd_bbp_force_remove_pending' );


/**
 * Add user purchases link
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_add_user_purchases_link() {
	if ( ! current_user_can( 'moderate' ) ) {
		return;
	} elseif ( ! function_exists( 'edd_get_users_purchases' ) ) {
		return;
	}

	$user_email = bbp_get_displayed_user_field( 'user_email' );

	echo '<div class="edd_users_purchases">';
	echo '<h4>User\'s Purchases:</h4>';
	$purchases = edd_get_users_purchases( $user_email, 100, false, 'any' );
	if ( $purchases ) :
		echo '<ul>';
		foreach ( $purchases as $purchase ) {

			echo '<li><strong><a href="' . admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $purchase->ID ) . '">#' . $purchase->ID . ' - ' . edd_get_payment_status( $purchase, true ) . '</a></strong></li>';
			$downloads = edd_get_payment_meta_downloads( $purchase->ID );
			foreach ( $downloads as $download ) {
				echo '<li>' . get_the_title( $download['id'] ) . ' - ' . date( 'F j, Y', strtotime( $purchase->post_date ) ) . '</li>';
			}

			if( function_exists( 'edd_software_licensing' ) ) {
				echo '<li><strong>Licenses:</strong></li>';
				$licenses  = edd_software_licensing()->get_licenses_of_purchase( $purchase->ID );
				if( $licenses ) {
					foreach ( $licenses as $license ) {
						echo '<li>' . get_the_title( $license->ID ) . ' - ' . edd_software_licensing()->get_license_status( $license->ID ) . '</li>';
					}
				}
				echo '<li><hr/></li>';
			}
		}
		echo '</ul>';
	else :
		echo '<p>This user has never purchased anything.</p>';
	endif;
	echo '</div>';
}
add_action( 'bbp_template_after_user_profile', 'edd_bbp_add_user_purchases_link' );


/**
 * Add priority support status to users
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_add_user_priority_support_status() {
	if ( ! current_user_can( 'moderate' ) ) {
		return;
	} elseif ( ! function_exists( 'rcp_get_status' ) ) {
		return;
	}

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
add_action( 'bbp_template_after_user_profile', 'edd_bbp_add_user_priority_support_status' );


/**
 * Resolve on reply
 *
 * @since		1.0.0
 * @param		int $reply_id The ID of this reply
 * @param		int $topic_id The ID of the topic this belongs to
 * @param		int $forum_id The ID of the parent forum
 * @param		array $anonymous_data
 * @param		int $author_id The ID of the post author
 * @param		bool $is_edit
 * @return		void
 */
function edd_bbp_reply_and_resolve( $reply_id = 0, $topic_id = 0, $forum_id = 0, $anonymous_data = false, $author_id = 0, $is_edit = false ) {
	if ( isset( $_POST['bbp_reply_close'] ) ) {
		update_post_meta( $topic_id, '_bbps_topic_status', 2 );
	}

	if ( isset( $_POST['bbp_reply_open'] ) ) {
		update_post_meta( $topic_id, '_bbps_topic_status', 1 );
	}
}
add_action( 'bbp_new_reply', 'edd_bbp_reply_and_resolve', 0, 6 );


/**
 * EDD Forum Sidebar
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_sidebar() {
	global $post;

	$user_id = get_the_author_meta( 'ID' );
	$user_data = get_userdata( $user_id );

?>
	<div class="box">

		<?php do_action( 'edd_bbp_sidebar' ); ?>

		<h3><?php echo get_the_author_meta( 'first_name' ) . '  ' . get_the_author_meta( 'last_name' ); ?></h3>
		<p class="bbp-user-forum-role"><?php  printf( 'Forum Role: %s', bbp_get_user_display_role( $user_id ) ); ?></p>
		<p class="bbp-user-topic-count"><?php printf( 'Topics Started: %s', bbp_get_user_topic_count_raw( $user_id ) ); ?></p>
		<p class="bbp-user-reply-count"><?php printf( 'Replies Created: %s', bbp_get_user_reply_count_raw( $user_id ) ); ?></p>

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

		<div class="edd_users_purchases">
			<h4>User's Purchases:</h4>
			<?php
			$purchases = edd_get_users_purchases( $user_data->user_email, 100, false, 'any' );
			if ( $purchases ) :
				echo '<ul>';
				foreach ( $purchases as $purchase ) {

					echo '<li>';

						echo '<strong><a href="' . admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $purchase->ID ) . '">#' . $purchase->ID . ' - ' . edd_get_payment_status( $purchase, true ) . '</a></strong><br/>';

						$downloads = edd_get_payment_meta_downloads( $purchase->ID );
						foreach ( $downloads as $download ) {
							echo get_the_title( $download['id'] ) . ' - ' . date( 'F j, Y', strtotime( $purchase->post_date ) ) . '<br/>';
						}

						if( function_exists( 'edd_software_licensing' ) ) {
							$licenses  = edd_software_licensing()->get_licenses_of_purchase( $purchase->ID );
							if( $licenses ) {
								echo '<strong>Licenses:</strong><br/>';
								foreach ( $licenses as $license ) {
									$key = edd_software_licensing()->get_license_key( $license->ID );
									echo '<a href="' . admin_url( 'edit.php?post_type=download&page=edd-licenses&s=' . $key ) . '">' . $key . '</a>';
									echo ' - ' . edd_software_licensing()->get_license_status( $license->ID );
									echo '<br/>';
								}
							}
							echo '<hr/>';
						}
					echo '</li>';				}
				echo '</ul>';
			else :
				echo '<p>This user has never purchased anything.</p>';
			endif; ?>
		</div>
	</div>
	<?php
}

function edd_bbp_d_sidebar() {
	edd_bbp_sidebar();
}


/**
 * Get assignee ID
 *
 * @since		1.0.0
 * @param		int $topic_id ID of this topic
 * @return		int $topic_assignee_id The ID of the assignee
 */
function edd_bbp_get_topic_assignee_id( $topic_id = NULL ) {
	if ( empty( $topic_id ) ) {
		$topic_id = get_the_ID();
	}

	if ( empty( $topic_id ) ) {
		return false;
	}

	$topic_assignee_id = get_post_meta( $topic_id, 'bbps_topic_assigned', true );

	return $topic_assignee_id;
}


/**
 * Get assignee name
 *
 * @since		1.0.0
 * @param		int $user_id The ID of the assignee
 * @return		string $topic_assignee_name The name of the assignee
 */
function edd_bbp_get_topic_assignee_name( $user_id = NULL ) {
	if ( empty( $user_id ) ) {
		return false;
	}

	$user_info = get_userdata( $user_id );
	$topic_assignee_name = trim( $user_info->user_firstname . ' ' . $user_info->user_lastname );

	if ( empty( $topic_assignee_name ) ) {
		$topic_assignee_name = $user_info->user_nicename;
	}

	return $topic_assignee_name;
}


/**
 * Send priority messages to Hall
 *
 * @since		1.0.0
 * @param		int $topic_id The ID of this topic
 * @param		int $forum_id The ID of the forum this topic belongs to
 * @param		bool $anonymous_data
 * @param		int $topic_author The author of this topic
 * @return		void
 */
function edd_bbp_send_priority_to_hall( $topic_id = 0, $forum_id = 0, $anonymous_data = false, $topic_author = 0 ) {
	// Bail if topic is not published
	if ( ! bbp_is_topic_published( $topic_id ) ) {
		return;
	}

	if( $forum_id != 499 ) {
		return;
	}

	$json = json_encode( array(
		'title'   => 'A new priority ticket has been posted',
		'message' => esc_html( get_the_title( $topic_id ) ) . ' - ' . esc_url( get_permalink( $topic_id ) )
	) );

	$args = array(
		'headers' => array(
			'content-type' => 'application/json'
		),
		'body' => $json,
		'timeout' => 15,
		'sslverify' => false
	);

	wp_remote_post( 'https://hall.com/api/1/services/generic/7a4672fbb62a48920058d7cc0c1da6c8', $args );
}
add_action( 'bbp_new_topic', 'edd_bbp_send_priority_to_hall', 10, 4 );


/**
 * Connect forum to docs
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_connect_forum_to_docs() {
    p2p_register_connection_type( array(
        'name' => 'forums_to_docs',
        'from' => 'forum',
        'to' => 'docs'
    ) );
}
add_action( 'p2p_init', 'edd_bbp_connect_forum_to_docs' );


/**
 * Display connected docs
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_display_connected_docs() {
    if ( ! current_user_can( 'moderate' ) ) {
		return;
	}

	$item_id = bbp_get_forum_id();

    // Find connected pages
    $connected = new WP_Query( array(
      'connected_type' => 'forums_to_docs',
      'connected_items' => $item_id,
      'nopaging' => true,
      'post_status' => 'publish'
    ) );

    // Display connected pages
    if ( $connected->have_posts() ) :
    ?>
    <div class="edd_bbp_support_forum_options">
    <?php if( bbp_is_single_topic() ) : ?>
        <h3>Related Documentation:</h3>
    <?php else : ?>
        <strong>Related Documentation:</strong>
    <?php endif; ?>
        <?php while ( $connected->have_posts() ) : $connected->the_post(); ?>
            <div><a href="<?php the_permalink(); ?>" target="_blank"><?php the_title(); ?></a></div>
        <?php endwhile; ?>
    </div><br/>
    <?php
    // Prevent weirdness
    wp_reset_postdata();

    endif;
}
add_action( 'bbp_template_before_single_forum', 'edd_bbp_display_connected_docs' );
add_action( 'edd_bbp_sidebar', 'edd_bbp_display_connected_docs' );


/**
 * Display forum notices
 *
 * @since		1.0.0
 * @return		void
 */
function edd_bbp_new_topic_notice() {
	if( bbp_is_single_forum() )
		echo '<div class="bbp-template-notice"><p>Please search the forums for existing questions before posting a new one.</p></div>';
}
add_action( 'bbp_template_notices', 'edd_bbp_new_topic_notice');
