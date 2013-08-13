<?php
/**
 * Shortcodes
 */

/**
 * Support Dashboard Shortcode Callback
 */
function bbps_dashboard_shortcode( $atts, $content = null ) {
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

	wp_enqueue_script( 'bootstrap', BBPS_URL . 'bootstrap/js/bootstrap.min.js' );
	wp_enqueue_style( 'bootstrap', BBPS_URL . 'bootstrap/css/bootstrap.min.css' );

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
	#support-tabs li { list-style: none; margin-left: 0; font-size: 95%;}
	</style>
	<ul class="nav nav-tabs" id="support-tabs">
		<li><a href="#your-waiting-tickets" data-toggle="tab">Awaiting Your Respose (<?php echo $waiting_tickets->post_count; ?>)</a></li>
		<li><a href="#your-tickets" data-toggle="tab">Your Open Tickets (<?php echo $assigned_tickets->post_count; ?>)</a></li>
		<li><a href="#unassigned" data-toggle="tab">Unassigned Tickets (<?php echo $unassigned_tickets->post_count; ?>)</a></li>
		<li><a href="#unresolved" data-toggle="tab">Unresolved Tickets (<?php echo $unresolved_tickets->post_count; ?>)</a></li>
		<li><a href="#feature-requests" data-toggle="tab">Feature Requests</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="your-waiting-tickets">
			<ul class="bbp-tickets">
				<?php if( $waiting_tickets->have_posts() ) : ?>
					<?php while( $waiting_tickets->have_posts() ) : $waiting_tickets->the_post(); ?>
						<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
						<?php if( $parent != 318 ) : ?>
						<?php $remove_url = add_query_arg( array( 'topic_id' => get_the_ID(), 'bbps_action' => 'remove_pending' ) ); ?>
						<li>
							<?php if( $parent == 499 ) { ?>
							<strong>Priority:</strong>
							<?php } ?>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> - <a href="<?php echo $remove_url; ?>">Remove Pending Status</a>
						</li>
						<?php endif; ?>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<li>No tickets awaiting youre reply. Excellent, now go grab some unresolved or unassigned tickets.</li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="tab-pane" id="your-tickets">
			<ul class="bbp-tickets">
				<?php if( $assigned_tickets->have_posts() ) : ?>
					<?php while( $assigned_tickets->have_posts() ) : $assigned_tickets->the_post(); ?>
						<?php $parent = get_post_field( 'post_parent', get_the_ID() ); ?>
						<li>
							<?php if( $parent == 499 ) { ?>
							<strong>Priority:</strong>
							<?php } ?>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<li>No unresolved tickets, yay! Now go grab some unresolved or unassigned tickets.</li>
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
							<strong>Priority:</strong>
							<?php } ?>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<li>No unassigned tickets, yay!</li>
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
							<strong>Priority:</strong>
							<?php } ?>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<li>No unresolved tickets, yay!</li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="tab-pane" id="feature-requests">
			<ul class="bbp-tickets">
				<?php if( $feature_requests->have_posts() ) : ?>
					<?php while( $feature_requests->have_posts() ) : $feature_requests->the_post(); ?>
						<li>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php endif; ?>
			</ul>
			<p><a href="https://easydigitaldownloads.com/support/forum/feature-requests/">View More</a></p>
		</div>
	</div>

	<script>
	jQuery(function($) {
		$('#support-tabs a:first').tab('show');
	})
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'bbps_dashboard', 'bbps_dashboard_shortcode' );