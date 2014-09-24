<?php
/**
 * Plugin Name:		EDD bbPress Support Dashboard
 * Plugin URI:		https://github.com/easydigitaldownloads/EDD-bbPress-Support/
 * Description:		Support dashboard for sites running EDD and bbPress
 * Version:			2.1
 * Author:			Pippin Williamson, Daniel J Griffiths and Sunny Ratilal
 * Author URI:		https://easydigitaldownloads.com/
 * Text Domain:		edd-bbp-dashboard
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
				self::$instance->load_textdomain();
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
			require_once EDD_BBP_DIR . 'includes/option-functions.php';
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

			// Setup options
			add_action( 'edd_bbp_activation', array( $this, 'add_options' ) );

			// Tweak subforum paging
			add_filter( 'bbp_after_forum_get_subforums_parse_args', array( $this, 'subforum_args' ) );
		}


		/**
		 * Internationalization
		 *
		 * @access		public
		 * @since		2.1
		 * @return		void
		 */
		public function load_textdomain() {
			// Set filter for language directory
			$lang_dir = EDD_BBP_DIR . '/languages/';
			$lang_dir = apply_filters( 'edd_bbp_dashboard_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'edd-bbpress-dashboard' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'edd-bbpress-dashboard', $locale );

			// Setup paths to current locale file
			$mofile_local	= $lang_dir . $mofile;
			$mofile_global	= WP_LANG_DIR . '/edd-bbpress-dashboard/' . $mofile;

			if( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-bbpress-dashboard/ folder
				load_textdomain( 'edd-bbpress-dashboard', $mofile_global );
			} elseif( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-bbpress-dashboard/languages/ folder
				load_textdomain( 'edd-bbpress-dashboard', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-bbpress-dashboard', false, $lang_dir );
			}
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
		 * Add options
		 *
		 * @access		public
		 * @since		2.1
		 * @return		void
		 */
		function add_options() {
			$options = apply_filters( 'edd_bbp_options', array(
				'_bbps_default_status' => '1',
				'_bbps_used_status' => '',
				'_bbps_enable_topic_move' => '1',
				'_bbps_status_permissions_urgent' => '',
			) );

			foreach( $options as $key => $value ) {
				add_option( $key, $value );
			}
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
	}
}

return EDD_BBP::instance();
