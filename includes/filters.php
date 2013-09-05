<?php
/**
 * Filters
 *
 * Copyright (c) 2013, Easy Digital Downloads.
 */


function edd_bbp_get_subforums( $args ) {
	$args['nopaging'] = true;
	return $args;
}
add_filter( 'bbp_after_forum_get_subforums_parse_args', 'edd_bbp_list_all_forums' );