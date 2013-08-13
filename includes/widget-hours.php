<?php

/**
 * Support Hours Widgets
 */
class BBPS_Forum_Support_Hours extends WP_Widget {
	public function __construct() {
		$widget_ops = array(
			'classname' => 'bbps_support_hours_widget',
			'description' => 'Set your support times for your support forum - these will be displayed to your posters'
		);

		$this->WP_Widget( 'bbps_support_hours_widget', __( 'Forum Support Hours', 'edd-bbpress-dashboard' ), $widget_ops );
	}

	public function form( $instance ) {
		$defaults = array(
			'title' => 'Support Hours',
			'open_time' => '',
			'close_time' => '',
			'forum_closed' => '',
			'forum_open_text' => 'Our forums are open',
			'forum_closed_text' => 'Our forums are closed',
			'closed_weekends' => ''
		);

		$instance = wp_parse_args( (array) $instance, $defaults );

		$title = $instance['title'];
		$open_time = $instance['open_time'];
		$close_time = $instance['close_time'];
		$clock_html = $instance['clock_html'];
		$forum_closed = $instance['forum_closed'];
		$forum_closed_text = $instance['forum_closed_text'];
		$forum_open_text = $instance['forum_open_text'];
		?>
		<p>Title: <input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr($title); ?>" /> </p>
		<p>Open Time: <input class="widefat" name="<?php echo $this->get_field_name( 'open_time' ); ?>" type="text" value="<?php echo esc_attr($open_time); ?>" /> Please enter the opening time for your support forum in 24 hour formate eg: 9am 09:00 </p>
		<p>Close Time: <input class="widefat" name="<?php echo $this->get_field_name( 'close_time' ); ?>" type="text" value="<?php echo esc_attr($close_time); ?>" /> Please enter the closing time for your support forum in 24 hour formate eg: 5pm 17:00 </p>
		<p>Open Text: <input class="widefat" name="<?php echo $this->get_field_name( 'forum_open_text' ); ?>" type="text" value="<?php echo esc_attr($forum_open_text); ?>" /> This will get displayed to your users when the forums are open. This text has a class of "forum_text" if you would like to style it differently</p>
		<p>Closed Text: <input class="widefat" name="<?php echo $this->get_field_name( 'forum_closed_text' ); ?>" type="text" value="<?php echo esc_attr($forum_closed_text); ?>" /> This will get displayed to your users when the forums are closed. This text has a class of "forum_text" if you would like to style it differently </p>
		<p>Forum Closed on Weekends?: <input class="checkbox" type="checkbox" <?php checked( $instance['closed_weekends'], 'on' ); ?> name="<?php echo $this->get_field_name( 'closed_weekends' ); ?>" /></p><p> Select this if your forum is closed on the weekends </p>
		<p>Forum Closed: <input class="checkbox" type="checkbox" <?php checked( $instance['forum_closed'], 'on' ); ?> name="<?php echo $this->get_field_name( 'forum_closed' ); ?>" /></p><p> Checking this box turns your widget into closed mode until you uncheck it - perfect if your away on holiday and not maintaining your forums. </p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = $new_instance['title'];
		$instance['open_time'] = $new_instance['open_time'];
		$instance['close_time'] = $new_instance['close_time'];
		$instance['forum_closed'] = $new_instance['forum_closed'];
		$instance['forum_closed_text'] = $new_instance['forum_closed_text'];
		$instance['forum_open_text'] = $new_instance['forum_open_text'];
		$instance['closed_weekends'] = $new_instance['closed_weekends'];

		return $instance;
	}

	public function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );

		echo $before_widget;

		$title = apply_filters('widget_title', $instance['title']);

		$open_time = empty( $instance['open_time']) ? '' : $instance['open_time'];
		$open_img = empty( $instance['open_img'] ) ? '' : $instance['open_img'];
		$close_time = empty( $instance['close_time'] ) ? '' : $instance['close_time'];
		$forum_closed = empty( $instance['forum_closed'] ) ? '&nbsp' : $instance['forum_closed'];
		$forum_closed_text = empty( $instance['forum_closed_text'] ) ? '&nbsp' : $instance['forum_closed_text'];
		$forum_open_text = empty( $instance['forum_open_text'] ) ? '&nbsp' : $instance['forum_open_text'];
		$closed_weekends = empty( $instance['closed_weekends'] ) ? '&nbsp' : $instance['closed_weekends'];

		echo $before_title . $title . $after_title;

		if ( $forum_closed == 'on' || ( $closed_weekends == 'on' && 6 == date( 'N' ) ) || ( $closed_weekends == 'on' && 7 == date( 'N' ) ) ) {
			$closed = true;
		} else {
			if ( ( $open_time_raw < $close_time_raw ) && ( $time >= $open_time_raw && ! ( $time >= $close_time_raw ) ) || ( $open_time_raw > $close_time_raw ) && ( $time >= $open_time_raw && ! ( $time <= $close_time_raw ) ) )
				$open = true;
			else
				$closed = true;
		}

		if ( $open ) {
			$text = $forum_open_text;
		} elseif ( $closed ) {
			$text = $forum_closed_text;
		}

		echo '<span class="forum_text">' . $text . '</span>';
		echo '<div class="forum_hours"> Our forum hours are: '. $open_time .' - '. $close_time . '</div>';

		echo $after_widget;
	}
}