<?php

/**
 *
 * The plugin bootstrap file
 *
 * This file is responsible for starting the plugin using the main plugin class file.
 *
 * @since 0.0.1
 * @package wordpress-dev-challenge
 *
 * @wordpress-plugin
 * Plugin Name:     Citas post wordpress-dev-challenge
 * Description:     Este plugin es creado para consultar citas de un post
 * Version:         0.0.1
 * Author:          Orlando Quintero
 * Author URI:      https://github.com/orlandoquintero45
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     wordpress-dev-challenge
 * Domain Path:     /lang
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

if ( ! class_exists( 'wordpress_dev_challenge' ) ) {

	/*
	 * main wordpress-dev-challenge class
	 *
	 * @class wordpress-dev-challenge
	 * @since 0.0.1
	 */
	class wordpress_dev_challenge {

		/*
		 * wordpress-dev-challenge plugin version
		 *
		 * @var string
		 */
		public $version = '4.7.5';

		/**
		 * The single instance of the class.
		 *
		 * @var wordpress-dev-challenge
		 * @since 0.0.1
		 */
		protected static $instance = null;

		/**
		 * Main wordpress-dev-challenge instance.
		 *
		 * @since 0.0.1
		 * @static
		 * @return wordpress-dev-challenge - main instance.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * wordpress-dev-challenge class constructor.
		 */
		public function __construct() {
			$this->load_plugin_textdomain();
			$this->define_constants();
			$this->includes();
			$this->define_actions();
		}

		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'wordpress-dev-challenge', false, basename( dirname( __FILE__ ) ) . '/lang/' );
		}

		/**
		 * Include required core files
		 */
		public function includes() {
            // Example
			//require_once __DIR__ . '/includes/loader.php';

			// Load custom functions and hooks
			require_once __DIR__ . '/includes/includes.php';
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}


		/**
		 * Define wordpress-dev-challenge constants
		 */
		private function define_constants() {
			define( 'PLUGIN_NAME_PLUGIN_FILE', __FILE__ );
			define( 'PLUGIN_NAME_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			define( 'PLUGIN_NAME_VERSION', $this->version );
			define( 'PLUGIN_NAME_PATH', $this->plugin_path() );
		}

		/**
		 * Define wordpress-dev-challenge actions
		 */
		public function define_actions() {
			//
			//creacion a meta boxes
			add_action( 'add_meta_boxes', 'adding_custom_meta_boxes', 10, 2 );
			//creacion de shortcode
			add_shortcode('mc-citacion', 'shortcode_mostrar_citas');
		}

		/**
		 * Define wordpress-dev-challenge menus
		 */
		public function define_menus() {
            //
		}
	}

	$plugin_name = new wordpress_dev_challenge();
}