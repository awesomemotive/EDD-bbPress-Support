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
	if( 1 == (int) $status ) {
		delete_post_meta( $topic_id, '_bbps_topic_pending' );
	}
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
	$topic_id = bbp_get_topic_id();
	$forum_id = bbp_get_forum_id();
	$topic_assigned = edd_bbp_get_topic_assignee_id( $topic_id );

	global $current_user;
	get_currentuserinfo();
	$current_user_id = $current_user->ID;

	$status       = get_post_meta( $topic_id, '_bbps_topic_status', true );
	$status_label = $status == '1' ? 'not resolved' : 'resolved';

	if ( ! current_user_can( 'moderate' ) ) {

?>
	<div class="moderator-tools clearfix">This topic is: <?php echo $status_label; ?></div>
<?php
		return;
	}



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

				<form id="bbps-topic-ping" name="bbps_support_topic_ping" action="" method="post">
					<input type="submit" class="edd-submit button" value="Ping Assignee" name="bbps_topic_ping_submit" />
					<input type="hidden" value="bbps_ping_topic" name="bbps_action"/>
					<input type="hidden" value="<?php echo $topic_id ?>" name="bbps_topic_id" />
					<input type="hidden" value="<?php echo $forum_id ?>" name="bbp_old_forum_id" />
				</form>

				<?php if( ! get_post_meta( $topic_id, '_bbp_override_auto_close', true ) ) : ?>
				<form id="bbps-topic-keep-open" name="bbps_support_topic_keep_open" action="" method="post">
					<input type="submit" class="edd-submit button" value="Keep Open"title="This prevents this topic from beeing closed automatically" name="bbps_topic_keep_open_submit" />
					<input type="hidden" value="bbps_ping_topic" name="bbps_action"/>
					<input type="hidden" value="<?php echo $topic_id ?>" name="bbps_topic_id" />
				</form>
				<?php endif; ?>

				<div class="clearfix"></div>
				<form id="bbps-topic-status" name="bbps_support" action="" method="post">
					<select name="bbps_support_option" id="bbps_support_options">
						<option value="1"<?php selected( $status, 1 ) ; ?>>Not Resolved</option>
						<option value="2"<?php selected( $status, 2 ) ; ?>>Resolved</option>
						<option value="3"<?php selected( $status, 3 ) ; ?>>Not a Support Question</option>
					</select>
					<input type="submit" value="Update" name="bbps_support_submit" />
					<input type="hidden" value="bbps_update_status" name="bbps_action"/>
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

	delete_post_meta( $topic_id, '_bbps_topic_pending' );
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
 * Send priority messages to Slack
 *
 * @since		1.0.0
 * @param		int $topic_id The ID of this topic
 * @param		int $forum_id The ID of the forum this topic belongs to
 * @param		bool $anonymous_data
 * @param		int $topic_author The author of this topic
 * @return		void
 */
function edd_bbp_send_priority_to_slack( $topic_id = 0, $forum_id = 0, $anonymous_data = false, $topic_author = 0 ) {
	// Bail if topic is not published
	if ( ! bbp_is_topic_published( $topic_id ) ) {
		return;
	}

	if( $forum_id != 499 ) {
		return;
	}

	$json = json_encode( array(
		'username' => 'edd-bot',
		'icon_emoji' => ':happy:',
		'text' => 'A new priority ticket has been posted - ' . esc_html( get_the_title( $topic_id ) ) . ' - <' . esc_url( get_permalink( $topic_id ) ) . '|View Ticket>'
	) );

	$args = array(
		'headers' => array(
			'content-type' => 'application/json'
		),
		'body' => $json,
		'timeout' => 15,
		'sslverify' => false
	);

	wp_remote_post( 'https://hooks.slack.com/services/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', $args );
}
add_action( 'bbp_new_topic', 'edd_bbp_send_priority_to_slack', 10, 4 );


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
 * Find all tickets that are 10 days old, close them, and send notices to the customer
 *
 * @since		2.1
 * @return		void
 */
function edd_bbp_close_old_tickets_and_notify() {

	$args = array(
		'post_type'    => 'topic',
		'meta_query'   => array(
			'relation' => 'AND',
			array(
				'key'   => '_bbps_topic_status',
				'value' => '1', // Open tickets only
			),
			array(
				'key'     => '_bbp_last_active_time',
				'value'   => date( 'Y-n-d H:i:s', strtotime( '-10 days' ) ),
				'compare' => '<=', // Tickets older than ten days
				'type'    => 'DATETIME'
			),
			array(
				'key'     => '_bbp_override_auto_close',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_bbp_voice_count',
				'value'   => '1',
				'compare' => '>' // Only tickets with at least two voices (one mod and one user normally)
			),
		),
		'posts_per_page'      => 50,
		'post_parent__not_in' => array( 318 ) // No feature requests
	);
	$tickets = get_posts( $args );

	if( $tickets ) {

		$emails = EDD()->emails;
		$emails->__set( 'from_address', 'no-reply@easydigitaldownloads.com' );
		$emails->heading = 'Support Alert';

		$headers = $emails->get_headers();

		foreach( $tickets as $ticket ) {

			$emails->__set( 'headers', $headers );
			$author_name  = get_the_author_meta( 'display_name', $ticket->post_author );
			$author_email = get_the_author_meta( 'user_email', $ticket->post_author );

			$url = bbp_get_topic_permalink( $ticket->ID );

			$to   = array();
			$to[] = $author_email;

			$message  = "Hello {$author_name},\n\n";
			$message .= "This email is to alert you that your ticket titled {$ticket->post_title} at https://easydigitaldownloads.com has been automatically closed due to inactivity.\n\n";
			$message .= "If you believe this is in error or you are still needing assistance with this issue, simply reply to the ticket again and let us know: \n\n";
			$message .= "Ticket URL: {$url}";

			$emails->send( $to, 'Support Ticket Closed', $message );

			delete_post_meta( $ticket->ID, '_bbps_topic_pending' );
			update_post_meta( $ticket->ID, '_bbps_topic_status', '2' );

		}

		$emails->__set( 'from_address', false );
		$emails->__set( 'headers', '' );

	}

}
add_action( 'edd_daily_scheduled_events', 'edd_bbp_close_old_tickets_and_notify');

/**
 * Emails a moderator a reminder when the Ping Assignee button is clicked
 *
 * @since		1.0
 * @return		void
 */
function edd_bbp_ping_topic_assignee() {
	$topic_id = absint( $_POST['bbps_topic_id'] );
	$user_id  = get_post_meta( $topic_id, 'bbps_topic_assigned', true );

	if ( $user_id ) {
		$userinfo   = get_userdata( $user_id );
		$user_email = $userinfo->user_email;
		$post_link  = bbp_get_topic_permalink( $topic_id );

		$message = <<< EMAILMSG
		A ticket that has been assigned to you is in need of attention.
		$post_link
EMAILMSG;
		wp_mail( $user_email, 'EDD Ticket Ping', $message );
	}
}

function edd_bbp_common_issues() {

	if( bbp_is_topic_edit() ) {
		return;
	}
?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#bbp-new-topic-fields').hide();
		$('#edd-bbp-common-issues-select').change(function() {
			var val = $(this).val();
			$('#edd-common-ticket-answers div').hide();
			$('#edd-common-ticket-answers #edd-common-issue-' + val ).show();
		});
		$('input[name="edd-bbp-docs-help"]').change(function() {
			if( $(this).val() == '3' ) {
				$('#bbp-new-topic-fields').hide();
				$('#edd-bbp-google-search').show();
			} else {
				$('#edd-bbp-google-search').hide();
				$('#bbp-new-topic-fields').show();
			}
		});
	});
	</script>
	<div id="edd-bbp-common-issues">
		<p>Is your ticket about one of these issues?</p>
		<select id="edd-bbp-common-issues-select" name="edd-bbp-common-issues">
			<option value="0">Select from common issues . . . </option>
			<option value="install-extension">I do not know how to install the extension I purchased</option>
			<option value="pending">Payments not being marked as complete</option>
			<option value="emails">Email receipts not being sent to customers</option>
			<option value="fes-upload">File upload error in Frontend Submissions</option>
			<option value="no">No, I need to open a new ticket</option>
		</select>
		<div id="edd-common-ticket-answers">
			<div id="edd-common-issue-install-extension" class="bbp-template-notice" style="display:none;">
				<p>Extensions are installed in the same way that standard WordPress plugins are installed. See <a href="https://easydigitaldownloads.com/docs/how-to-install-an-extension/">this FAQ for more information</a>.</p>
			</div>
			<div id="edd-common-issue-pending" class="bbp-template-notice" style="display:none;">
				<p>Payments not getting marked as complete can be an effect of numerous causes, including conflicting plugins and improper merchant account configuration. See <a href="https://easydigitaldownloads.com/docs/payments-stay-pending/">this FAQ for more information</a>.</p>
			</div>
			<div id="edd-common-issue-emails" class="bbp-template-notice" style="display:none;">
				<p>If your emails are not getting delivered, it could be due to a plugin conflict or common server issue. See <a href="https://easydigitaldownloads.com/docs/email-receipts-sent/">this FAQ for more information</a>.</p>
			</div>
			<div id="edd-common-issue-fes-upload" class="bbp-template-notice" style="display:none;">
				<p>Upload errors can happen for a number of reasons and are usually the result of a conflicting plugin. See <a href="https://easydigitaldownloads.com/docs/fes-faqs/">this FAQ for more information</a>.</p>
			</div>
		</div>
		<div>
			<p>Have you consulted the documentation?</p>
			<div>
				<label for="edd-bbp-docs-no-help">
					<input type="radio" id="edd-bbp-docs-no-help" name="edd-bbp-docs-help" value="1"/> I read the documentation but it did not help or the docs aren't applicable to my situation
				</label>
			</div>
			<div>
				<label for="edd-bbp-no-read-docs">
					<input type="radio" id="edd-bbp-no-read-docs" name="edd-bbp-docs-help" value="3"/> I did not read the documentation
				</label>
			</div>
			<div>
				<label for="edd-bbp-no-docs">
					<input type="radio" id="edd-bbp-no-docs" name="edd-bbp-docs-help" value="2"/> I did not find any documentation about my issue
				</label>
			</div>
		</div>
		<div id="edd-bbp-google-search" style="display:none">
			<p>Enter keywords to search for documentation or similar tickets to your issue.</p>
			<script>
			  (function() {
			    var cx = '013364375160530833496:u0gpdnp1z-8';
			    var gcse = document.createElement('script');
			    gcse.type = 'text/javascript';
			    gcse.async = true;
			    gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
			        '//www.google.com/cse/cse.js?cx=' + cx;
			    var s = document.getElementsByTagName('script')[0];
			    s.parentNode.insertBefore(gcse, s);
			  })();
			</script>
			<gcse:search></gcse:search>
		</div>
	</div>
<?php
}
add_action( 'bbp_theme_before_topic_form_notices', 'edd_bbp_common_issues' );

/**
 * Store docs were helpful selection
 *
 * @since		1.0.0
 * @param		int $topic_id The ID of the topic this belongs to
 * @param		int $forum_id The ID of the parent forum
 * @param		array $anonymous_data
 * @param		int $topic_author The author of this topic
 * @return		void
 */
function edd_bbp_store_docs_helpful_selection( $topic_id = 0, $forum_id = 0, $anonymous_data = false, $topic_author = 0 ) {

	if( empty( $_POST['edd-bbp-docs-help'] ) ) {
		return;
	}

	$helpful = absint( $_POST['edd-bbp-docs-help'] );
	add_post_meta( $topic_id, '_edd_bbp_docs_helpful', $helpful );

}
add_action( 'bbp_new_topic', 'edd_bbp_store_docs_helpful_selection', 20, 4 );

function edd_bbp_show_docs_helpful_selection() {

	static $edd_bbp_doc_notice;

	$helpful  = get_post_meta( bbp_get_topic_id(), '_edd_bbp_docs_helpful', true );
	if( empty( $helpful ) || ! current_user_can( 'moderate' ) ) {
		return;
	}

	if( $edd_bbp_doc_notice ) {
		return;
	}

?>
	<div class="bbp-template-notice edd-bbp-docs-helpful">
		<p>
		<?php if( 1 == $helpful ) : ?>
			Docs were not helpful
		<?php elseif( 2 == $helpful ) : ?>
			Did not find relevant docs
		<?php else : ?>
			Did not read docs
		<?php endif; ?>
		</p>
	</div>
<?php
	$edd_bbp_doc_notice = true;
}
add_action( 'bbp_theme_after_reply_content', 'edd_bbp_show_docs_helpful_selection' );

/**
 * Send a Pushover Notification when a moderator is assigned to a topic
 */
function edd_bbp_send_pushover_notification_on_assignment() {
	if ( isset( $_POST['bbps_support_topic_assign'] ) ) {

		if( ! function_exists( 'ckpn_send_notification' ) )
			return;

		$user_id  = absint( $_POST['bbps_assign_list'] );
		$topic    = bbp_get_topic( $_POST['bbps_topic_id'] );

		if ( $user_id > 0 && $user_id != get_current_user_id() ) {
			$title = __( 'Easy Digital Downloads: A forum topic has been assigned to you', 'eddwp' );
			$message = sprintf( __( 'You have been assigned to %1$s by another moderator', 'eddwp' ), $topic->post_title );
			$user_push_key = get_user_meta( $user_id, 'ckpn_user_key', true );

			if( $user_push_key ) {
				$url       = $topic->guid;
				$url_title = __( 'View Topic', 'eddwp' );

				$args = array(
					'title' => $title,
					'message' => $message,
					'user' => $user_push_key,
					'url' => $url,
					'url_title' => $url_title
				);

				ckpn_send_notification( $args );
			}
		}
	}
}
add_action( 'init', 'edd_bbp_send_pushover_notification_on_assignment' );
