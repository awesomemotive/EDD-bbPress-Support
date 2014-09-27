<?php
/**
 * Plugin Name:		EDD bbPress Support Dashboard
 * Plugin URI:		https://github.com/easydigitaldownloads/EDD-bbPress-Support/
 * Description:		Support dashboard for sites running EDD and bbPress
 * Version:			2.1
 * Author:			Pippin Williamson, Daniel J Griffiths and Sunny Ratilal
 * Author URI:		https://easydigitaldownloads.com/
 *
 * @package			EDD\BBP
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


if( ! class_exists( 'EDD_BBP' ) ) {

	/**
	 * Main EDD_BBP class
	 *
	 * @since		2.1
	 */
	class EDD_BBP {

		/**
		 * @var			EDD_BBP $instance The one true EDD_BBP
		 * @since		2.1
		 */
		private static $instance;


		/**
		 * Get active instance
		 *
		 * @access		public
		 * @since		2.1
		 * @return		object self::$instance The one true EDD_BBP
		 */
		public static function instance() {
			if( ! self::$instance ) {
				self::$instance = new EDD_BBP();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->hooks();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access		private
		 * @since		2.1
		 * @return		void
		 */
		private function setup_constants() {
			// Plugin path
			define( 'EDD_BBP_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'EDD_BBP_URL', plugin_dir_url( __FILE__ ) );
		}


		/**
		 * Include necessary files
		 *
		 * @access		private
		 * @since		2.1
		 * @return		void
		 */
		private function includes() {
			require_once EDD_BBP_DIR . 'includes/actions.php';
			require_once EDD_BBP_DIR . 'includes/functions.php';
			require_once EDD_BBP_DIR . 'includes/shortcodes.php';
			require_once EDD_BBP_DIR . 'includes/support-functions.php';

			if( is_admin() ) {
				require_once EDD_BBP_DIR . 'includes/admin/functions.php';
				require_once EDD_BBP_DIR . 'includes/admin/bbps-admin.php';
			}
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access		private
		 * @since		2.1
		 * @return		void
		 */
		private function hooks() {
			// Initial activation
			register_activation_hook( __FILE__, array( $this, 'activate' ) );

			// Tweak subforum paging
			add_filter( 'bbp_after_forum_get_subforums_parse_args', array( $this, 'subforum_args' ) );
			add_filter( 'bbp_reply_admin_links', array( $this, 'admin_links' ), 10, 2 );
		}


		/**
		 * Plugin activation
		 *
		 * @access		public
		 * @since		2.1
		 * @return		void
		 */
		function activate() {
			do_action( 'edd_bbp_activation' );
		}


		/**
		 * Tweak args for subforums
		 *
		 * @access		public
		 * @since		2.1
		 * @param		array $args The current arguments
		 * @return		array $args The modified arguments
		 */
		function subforum_args( $args ) {
			$args['nopaging'] = true;

			return $args;
		}

		/**
		 * Remove unused admin links
		 *
		 * @access		public
		 * @since		2.1
		 * @param		array $links The default links
		 * @return		array $id The Topic or Reply ID
		 */
		function admin_links( $links, $id ) {

			unset( $links['reply'] );

			return $links;
		}
	}
}

return EDD_BBP::instance();
