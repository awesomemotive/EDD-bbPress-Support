<?php

//hook into the forum atributes meta box

add_action('bbp_forum_metabox' , 'bbps_extend_forum_attributes_mb');

/* the support forum checkbox will add resolved / not resolved status to all forums */
/* The premium forum will create a support forum that can only be viewed by that user and admin users */
function bbps_extend_forum_attributes_mb($forum_id){

	$support_forum = edd_bbp_d_is_support_forum( $forum_id );
	?>

	<p>
		<strong><?php _e( 'Support Forum:', 'bbps' ); ?></strong>
		<input type="checkbox" name="bbps-support-forum" value="1"<?php checked( true, $support_forum ); ?>/>
	</p>

<?php
}

//hook into the forum save hook.

add_action( 'bbp_forum_attributes_metabox_save' , 'bbps_forum_attributes_mb_save' );

function bbps_forum_attributes_mb_save($forum_id){

	//get out the forum meta
	$support_forum = get_post_meta( $forum_id, '_bbps_is_support');

	//support options
	if ( !empty( $_POST['bbps-support-forum'] ) )
		update_post_meta($forum_id, '_bbps_is_support', $_POST['bbps-support-forum']);
	else
		delete_post_meta( $forum_id, '_bbps_is_support' );

	return $forum_id;

}