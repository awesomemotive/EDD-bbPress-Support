<?php
/**
 * Shortcodes
 */

/**
 * Support Dashboard Shortcode Callback
 */
function edd_bbp_d_dashboard_shortcode( $atts, $content = null ) {
	/*
	 Show:
	 - open tickets
	 - assigned tickets
	 - unassigned tickets
	 - tickets awaiting answer
	*/
	global $user_ID;

	if ( ! current_user_can( 'moderate' ) )
		return;

	wp_enqueue_script( 'bootstrap', EDD_BBP_D_URL . 'bootstrap/js/bootstrap.min.js'   );
	wp_enqueue_style(  'bootstrap', EDD_BBP_D_URL . 'bootstrap/css/bootstrap.min.css' );


	// Show ticket overview for all mods
	$mods = edd_bbp_d_get_all_mods(); ?>

	<?php if( $mods ) : ?>
		<div class="row" id="mods-grid">
		<?php foreach( $mods as $mod ) : ?>

			<?php $ticket_count = edd_bbp_d_count_tickets_of_mod( $mod->ID ); ?>

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
				<?php while ( $assigned_tickets->have_posts() ) : $assigned_tickets->the_post(); ?>
					<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
					<?php if ( $parent != 318 ) : ?>
					<?php $remove_url = add_query_arg( array( 'topic_id' => get_the_ID(), 'bbps_action' => 'remove_pending' ) ); ?>
					<div>
						<?php if ( $parent == 499 ) { ?>
						<strong>Priority:</strong>
						<?php } ?>
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> - <a href="<?php echo $remove_url; ?>"><?php _e( 'Remove Pending Status', 'edd-bbpress-dashboard' ); ?></a>
					</div>
					<?php endif; ?>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<div><?php _e( 'This mod has no assigned tickets.', 'edd-bbpress-dashboard' ); ?></div>
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
				'key'   => '_bbps_topic_pending',
				'value' => '1',
			),
			array(
				'key'   => '_bbps_topic_status',
				'value' => '1',
				'compare' => '!='
			),
			array(
				'key'   => 'bbps_topic_assigned',
				'value' => $user_ID,
			)
		),
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
		'meta_key'   => '_bbps_topic_status',
		'meta_value' => '1',
		'post_parent__not_in' => array( 318 ),
		'posts_per_page' => -1,
		'post_status' => 'publish'
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
					<?php while ( $waiting_tickets->have_posts() ) : $waiting_tickets->the_post(); ?>
						<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
						<?php if ( $parent != 318 ) : ?>
						<?php $remove_url = add_query_arg( array( 'topic_id' => get_the_ID(), 'bbps_action' => 'remove_pending' ) ); ?>
						<li>
							<?php if ( $parent == 499 ) { ?>
							<strong>Priority:</strong>
							<?php } ?>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> - <a href="<?php echo $remove_url; ?>"><?php _e( 'Remove Pending Status', 'edd-bbpress-dashboard' ); ?></a>
						</li>
						<?php endif; ?>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<li><?php _e( 'No tickets awaiting your reply. Excellent, now go grab some unresolved or unassigned tickets.', 'edd-bbpress-dashboard' ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="tab-pane" id="your-tickets">
			<ul class="bbp-tickets">
				<?php if ( $assigned_tickets->have_posts() ) : ?>
					<?php while ( $assigned_tickets->have_posts() ) : $assigned_tickets->the_post(); ?>
						<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
						<li>
							<?php if ( $parent == 499 ) { ?>
							<strong><?php _e( 'Priority:', 'edd-bbpress-dashboard' ); ?></strong>
							<?php } ?>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<li><?php _e( 'No unresolved tickets, yay! Now go grab some unresolved or unassigned tickets.', 'edd-bbpress-dashboard' ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="tab-pane" id="unassigned">
			<ul class="bbp-tickets">
				<?php if( $unassigned_tickets->have_posts() ) : ?>
					<?php while( $unassigned_tickets->have_posts() ) : $unassigned_tickets->the_post(); ?>
						<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
						<li>
							<?php if( $parent == 499 ) { ?>
							<strong><?php _e( 'Priority:', 'edd-bbpress-dashboard' ); ?></strong>
							<?php } ?>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<li><?php _e( 'No unassigned tickets, yay!', 'edd-bbpress-dashboard' ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="tab-pane" id="no-replies">
			<ul class="bbp-tickets">
				<?php if( $no_reply_tickets->have_posts() ) : ?>
					<?php while( $no_reply_tickets->have_posts() ) : $no_reply_tickets->the_post(); ?>
						<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
						<li>
							<?php if( $parent == 499 ) { ?>
							<strong><?php _e( 'Priority:', 'edd-bbpress-dashboard' ); ?></strong>
							<?php } ?>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<li><?php _e( 'No tickets without replies, yay!', 'edd-bbpress-dashboard' ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="tab-pane" id="unresolved">
			<ul class="bbp-tickets">
				<?php if( $unresolved_tickets->have_posts() ) : ?>
					<?php while( $unresolved_tickets->have_posts() ) : $unresolved_tickets->the_post(); ?>
						<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
						<li>
							<?php if( $parent == 499 ) { ?>
							<strong><?php _e( 'Priority:', 'edd-bbpress-dashboard' ); ?></strong>
							<?php } ?>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<li><?php _e( 'No unassigned tickets, yay!', 'edd-bbpress-dashboard' ); ?></li>
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
add_shortcode( 'bbps_dashboard', 'edd_bbp_d_dashboard_shortcode' );