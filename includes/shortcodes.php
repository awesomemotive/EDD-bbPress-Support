<?php
/**
 * Shortcodes
 *
 * @package		EDD\BBP\Shortcodes
 * @since		1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Support Dashboard Shortcode Callback
 *
 * Show:
 *  - open tickets
 *  - assigned tickets
 *  - unassigned tickets
 *  - tickets awaiting answer
 *
 * @since		1.0.0
 * @param		array $atts Shortcode attributes
 * @param		string $content The shortcode content
 * @global		int $user_ID The ID of the current user
 * @return
 */
function edd_bbp_dashboard_shortcode( $atts, $content = null ) {
	global $user_ID;

	// Bail if not logged in
	if( ! is_user_logged_in() ) {
		return edd_login_form();
	}

	// Bail if current user isn't a moderator
	if( ! current_user_can( 'moderate' ) ) {
		return;
	}

	// Enqueue our styles
	wp_enqueue_script( 'bootstrap', EDD_BBP_URL . 'assets/js/bootstrap.min.js'   );
	wp_enqueue_style(  'bootstrap', EDD_BBP_URL . 'assets/css/bootstrap.min.css' );

	// Show ticket overview for all mods
	$mods = edd_bbp_get_all_mods(); ?>

	<?php if( $mods ) : ?>
		<div class="row" id="mods-grid">
		<?php foreach( $mods as $mod ) : ?>

			<?php $ticket_count = edd_bbp_count_tickets_of_mod( $mod->ID ); ?>

			<div class="mod col-xs-6 col-sm-3">
				<div class="mod-name"><?php echo $mod->display_name; ?></div>
				<div class="mod-gravatar"><?php echo get_avatar( $mod->ID, 45 ); ?></div>
				<div class="mod-ticket-count">
					<a href="<?php echo add_query_arg( 'mod', $mod->ID ); ?>">Tickets: <strong><?php echo $ticket_count; ?></strong></div></a>
			</div>

		<?php endforeach; ?>
		</div>
	<?php endif;

	if( ! empty( $_GET['mod'] ) ) {
		// Get open, assigned tickets
		$args = array(
			'post_type'  => 'topic',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'   => '_bbps_topic_status',
					'value' => '1',
				),
				array(
					'key'   => 'bbps_topic_assigned',
					'value' => $_GET['mod'],
				)
			),
			'posts_per_page' => -1,
			'post_parent__not_in' => array( 318 )
		);
		$assigned_tickets = new WP_Query( $args );
		$mod = get_userdata( $_GET['mod'] );
		ob_start(); ?>
		<div class="bbp-tickets">
			<?php if ( $assigned_tickets->have_posts() ) : ?>
				<h4>Tickets assigned to <?php echo $mod->display_name; ?></h4>
					<table class="table table-striped" width="100%">
						<tr>
							<th width="35%">Topic Title</th>
							<th width="25%">Last Post By</th>
							<th width="25%">Last Updated</th>
							<th width="15%">Post Count</th>
						</tr>
						<?php while( $assigned_tickets->have_posts() ) : $assigned_tickets->the_post(); ?>
							<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
							<?php $row_class = ( $parent == 499 ) ? 'danger' : ''; ?>
							<?php $last_reply_id = bbp_get_topic_last_reply_id( get_the_ID() ); ?>
							<?php $last_reply_data = get_post( $last_reply_id ); ?>
							<tr class = "<?php echo $row_class; ?>">
								<td>
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</td>
								<td><?php the_author_meta( 'display_name', $last_reply_data->post_author ); ?></td>
								<td><?php bbp_topic_freshness_link(); ?></td>
								<td><?php bbp_topic_post_count( get_the_ID() ); ?></td>
							</tr>
						<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
					</table>
			<?php else : ?>
				<div>This mod has no assigned tickets.</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	// Get tickets awaiting answer
	$args = array(
		'post_type'  => 'topic',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'   => '_bbps_topic_pending'
			),
			array(
				'key'   => 'bbps_topic_assigned',
				'value' => $user_ID,
			)
		),
		'order' => 'ASC',
		'orderby' => 'meta_value',
		'meta_key' => '_bbp_last_active_time',
		'posts_per_page' => -1,
		'post_parent__not_in' => array( 318 )
	);
	$waiting_tickets = new WP_Query( $args );


	// Get open, assigned tickets
	$args = array(
		'post_type'  => 'topic',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'   => '_bbps_topic_status',
				'value' => '1',
			),
			array(
				'key'   => 'bbps_topic_assigned',
				'value' => $user_ID,
			)
		),
		'order' => 'ASC',
		'orderby' => 'meta_value',
		'meta_key' => '_bbp_last_active_time',
		'posts_per_page' => -1,
		'post_parent__not_in' => array( 318 )
	);
	$assigned_tickets = new WP_Query( $args );


	// Get unassigned tickets
	$args = array(
		'post_type'  => 'topic',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'     => 'bbps_topic_assigned',
				'compare' => 'NOT EXISTS',
				'value'   => '1'
			),
			array(
				'key'   => '_bbps_topic_status',
				'value' => '1',
			),
		),
		'order' => 'ASC',
		'orderby' => 'meta_value',
		'meta_key' => '_bbp_last_active_time',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'post_parent__not_in' => array( 318 )
	);
	$unassigned_tickets = new WP_Query( $args );


	// Get tickets with no replies
	$args = array(
		'post_type'  => 'topic',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'     => '_bbp_voice_count',
				'value'   => '1'
			),
			array(
				'key'   => '_bbps_topic_status',
				'value' => '1',
			),
		),
		'posts_per_page' => -1,
		'post_status' => 'publish'
	);
	$no_reply_tickets = new WP_Query( $args );

	// Get unresolved tickets
	$args = array(
		'post_type'  => 'topic',
		'post_parent__not_in' => array( 318 ),
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'order' => 'ASC',
		'orderby' => 'meta_value',
		'meta_key' => '_bbp_last_active_time',
		'meta_query' => array(
			array(
				'key'   => '_bbps_topic_status',
				'value' => '1',
			),
		),
	);
	$unresolved_tickets = new WP_Query( $args );

	// Get unresolved tickets
	$args = array(
		'post_type'  => 'topic',
		'post_parent' => 318,
		'posts_per_page' => 30,
		'post_status' => 'publish'
	);
	$feature_requests = new WP_Query( $args );

	$open_count       = 0;
	$unassigned_count = 0;
	$unresolved_count = 0;

	ob_start(); ?>
	<style>
	#support-tabs { padding-left: 0; }
	#support-tabs li { list-style: none; margin-left: 0; font-size: 95%;}
	#support-tabs li a { padding: 4px; }
	#mods-grid { margin-bottom: 20px; }
	</style>
	<ul class="nav nav-tabs" id="support-tabs">
		<li><a href="#your-waiting-tickets" data-toggle="tab">Awaiting Your Response (<?php echo $waiting_tickets->post_count; ?>)</a></li>
		<li><a href="#your-tickets" data-toggle="tab">Your Open Tickets (<?php echo $assigned_tickets->post_count; ?>)</a></li>
		<li><a href="#unassigned" data-toggle="tab">Unassigned Tickets (<?php echo $unassigned_tickets->post_count; ?>)</a></li>
		<li><a href="#no-replies" data-toggle="tab">No Replies (<?php echo $no_reply_tickets->post_count; ?>)</a></li>
		<li><a href="#unresolved" data-toggle="tab">Unresolved Tickets (<?php echo $unresolved_tickets->post_count; ?>)</a></li>
		<li><a href="#feature-requests" data-toggle="tab">Feature Requests</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="your-waiting-tickets">
			<ul class="bbp-tickets">
				<?php if ( $waiting_tickets->have_posts() ) : ?>
					<form class="form-table" method="post">
						<table class="table table-striped" width="100%">
							<tr>
								<th></th>
								<th width="40%">Topic Title</th>
								<th width="25%">Last Updated</th>
								<th width="25%">Actions</th>
							</tr>
							<?php while( $waiting_tickets->have_posts() ) : $waiting_tickets->the_post(); ?>
								<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
								<?php $row_class = ( $parent == 499 ) ? 'danger' : ''; ?>
								<?php $remove_url = add_query_arg( array( 'topic_id' => get_the_ID(), 'bbps_action' => 'remove_pending' ) ); ?>
								<tr class = "<?php echo $row_class; ?>">
									<td>
										<input type="checkbox" name="tickets[]" value="<?php echo esc_attr( get_the_ID() ); ?>"/>
									</td>
									<td>
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</td>
									<td><?php bbp_topic_freshness_link( get_the_ID() ); ?></td>
									<td><a href="<?php echo $remove_url; ?>">Remove Pending Status</a></td>
								</tr>
							<?php endwhile; ?>
						</table>
						<input type="hidden" name="edd_action" value="remove_ticket_pending_status"/>
						<input type="submit" value="Remove Pending Status"/>
						<?php wp_reset_postdata(); ?>
					</form>
				<?php else : ?>
					<li>No tickets awaiting your reply. Excellent, now go grab some unresolved or unassigned tickets.</li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="tab-pane" id="your-tickets">
			<ul class="bbp-tickets">
				<?php if ( $assigned_tickets->have_posts() ) : ?>
					<table class="table table-striped" width="100%">
						<tr>
							<th width="40%">Topic Title</th>
							<th width="30%">Last Post By</th>
							<th width="30%">Last Updated</th>
						</tr>
						<?php while( $assigned_tickets->have_posts() ) : $assigned_tickets->the_post(); ?>
							<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
							<?php $row_class = ( $parent == 499 ) ? 'danger' : ''; ?>
							<?php $last_reply_id = bbp_get_topic_last_reply_id( get_the_ID() ); ?>
							<?php $last_reply_data = get_post( $last_reply_id ); ?>
							<tr class = "<?php echo $row_class; ?>">
								<td>
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</td>
								<td><?php the_author_meta( 'display_name', $last_reply_data->post_author ); ?></td>
								<td><?php bbp_topic_freshness_link( get_the_ID() ); ?></td>
							</tr>
						<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
					</table>
				<?php else : ?>
					<li>No unresolved tickets, yay! Now go grab some unresolved or unassigned tickets.</li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="tab-pane" id="unassigned">
			<ul class="bbp-tickets">
				<?php if( $unassigned_tickets->have_posts() ) : ?>
					<table class="table table-striped" width="100%">
						<tr>
							<th width="40%">Topic Title</th>
							<th width="30%">Last Post By</th>
							<th width="30%">Last Updated</th>
						</tr>
						<?php while( $unassigned_tickets->have_posts() ) : $unassigned_tickets->the_post(); ?>
							<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
							<?php $row_class = ( $parent == 499 ) ? 'danger' : ''; ?>
							<?php $last_reply_id = bbp_get_topic_last_reply_id( get_the_ID() ); ?>
							<?php $last_reply_data = get_post( $last_reply_id ); ?>
							<tr class = "<?php echo $row_class; ?>">
								<td>
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</td>
								<td><?php the_author_meta( 'display_name', $last_reply_data->post_author ); ?></td>
								<td><?php bbp_topic_freshness_link( get_the_ID() ); ?></td>
							</tr>
						<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
					</table>
				<?php else : ?>
					<li>No unassigned tickets, yay!</li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="tab-pane" id="no-replies">
			<ul class="bbp-tickets">
				<?php if( $no_reply_tickets->have_posts() ) : ?>
					<table class="table table-striped" width="100%">
						<tr>
							<th width="40%">Topic Title</th>
							<th width="30%">Posted</th>
							<th width="30%">Assignee</th>
						</tr>
						<?php while( $no_reply_tickets->have_posts() ) : $no_reply_tickets->the_post(); ?>
							<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
							<?php $row_class = ( $parent == 499 ) ? 'danger' : ''; ?>

							<?php $assignee_id = edd_bbp_get_topic_assignee_id( get_the_ID() ); ?>
							<?php $assignee_name = edd_bbp_get_topic_assignee_name( $assignee_id ); ?>
							<tr class = "<?php echo $row_class; ?>">
								<td>
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</td>
								<td><?php bbp_topic_freshness_link( get_the_ID() ); ?></td>
								<td><?php echo ( !empty( $assignee_name ) ) ? $assignee_name : 'Unassigned' ?></td>
							</tr>
						<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
					</table>
				<?php else : ?>
					<li>No tickets without replies, yay!</li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="tab-pane" id="unresolved">
			<ul class="bbp-tickets">
				<?php if( $unresolved_tickets->have_posts() ) : ?>
					<table class="table table-striped" width="100%">
						<tr>
							<th width="35%">Topic Title</th>
							<th width="25%">Last Updated</th>
							<th width="25%">Assignee</th>
							<th width="15%">Post Count</th>
						</tr>
						<?php while( $unresolved_tickets->have_posts() ) : $unresolved_tickets->the_post(); ?>
							<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
							<?php $row_class = ( $parent == 499 ) ? 'danger' : ''; ?>

							<?php $assignee_id = edd_bbp_get_topic_assignee_id( get_the_ID() ); ?>
							<?php $assignee_name = edd_bbp_get_topic_assignee_name( $assignee_id ); ?>
							<tr class = "<?php echo $row_class; ?>">
								<td>
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</td>
								<td><?php bbp_topic_freshness_link( get_the_ID() ); ?></td>
								<td><?php echo ( !empty( $assignee_name ) ) ? $assignee_name : 'Unassigned'; ?></td>
								<td><?php bbp_topic_post_count( get_the_ID() ); ?></td>
							</tr>
						<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
					</table>
				<?php else : ?>
					<li>No unassigned tickets, yay!</li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="tab-pane" id="feature-requests">
			<ul class="bbp-tickets">
				<?php if ( $feature_requests->have_posts() ) : ?>
					<?php while ( $feature_requests->have_posts() ) : $feature_requests->the_post(); ?>
						<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php endif; ?>
			</ul>
		</div>
	</div>

	<script>
	jQuery( function($) {
		$('#support-tabs a:first').tab('show');
	})
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'bbps_dashboard', 'edd_bbp_dashboard_shortcode' );


function edd_bbp_support_tickets( $atts, $content = null ) {

	$tickets = get_posts( array(
		'post_type' => 'topic',
		'author'    => get_current_user_id(),
		'nopaging'  => true
	) );

	if( $tickets ) :
?>
		<ul id="edd-support-tickets">

		<?php foreach( $tickets as $ticket ) : ?>

			<?php
			$status = get_post_meta( $ticket->ID, '_bbps_topic_status', true );
			$status = 1 == $status ? 'Not Resolved' : 'Resolved';
			?>

			<li>
				<span class="status <?php echo sanitize_key( $status ); ?>"><?php echo $status; ?></span>:&nbsp;
				<a href="<?php bbp_topic_permalink( $ticket->ID ); ?>"><?php bbp_topic_title( $ticket->ID ); ?></a>
			</li>

		<?php endforeach; ?>

		</ul>
<?php

	else :
?>
	<p class="edd-support-no-tickets">You have not opened any support tickets. Need help? <a href="<?php echo home_url( '/support' ); ?>">Open a ticket in the forums.</a></p>
<?php
	endif;

}
add_shortcode( 'support_tickets', 'edd_bbp_support_tickets' );
