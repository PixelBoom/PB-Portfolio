<?php
/*
Plugin Name: PB Portfolio
Plugin URI: https://github.com/PixelBoom/PB-Portfolio/
Description: Adds the 'portfolio' custom post type to be used in PixelBoom WordPress Themes.
Author: PixelBoom
Version: 1.0
Author URI: http://www.pixelboom.net/
*/
// If this file is called directly, bail.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// The plugin file.
define('PB_PORTFOLIO_PLUGIN_FILE', __FILE__);

// The plugin path.
define('PB_PORTFOLIO_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// The plugin URL.
define('PB_PORTFOLIO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// The PB_Portfolio Classes.
if( ! class_exists('PB_Portfolio') ) {
	class PB_Portfolio {
		/**
		 * Constructor.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function __construct()
		{
			// Translation.
			load_plugin_textdomain( 'pb-portfolio', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			// Include core files.
			$this->includes();

			// Instantiate the core object.
			new PB_Portfolio_Types();
			new PB_Portfolio_Metaboxes();

			// Enqueue scripts & styles for admin page.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}

		/**
		 * Include other files.
		 *
		 * @since 1.0
		 * @access private
		 */
		private function includes()
		{
			require_once( PB_PORTFOLIO_PLUGIN_PATH . 'includes/class-pb-portfolio-types.php' );
			require_once( PB_PORTFOLIO_PLUGIN_PATH . 'includes/class-pb-portfolio-metaboxes.php' );
			require_once( PB_PORTFOLIO_PLUGIN_PATH . 'includes/functions.php' );
		}

		/**
		 * Enqueue scripts & styles for admin page.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function admin_enqueue_scripts()
		{
			global $post, $pagenow;

			if( !empty($post) && ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) && (get_theme_support( 'portfolio-media' ) || get_theme_support( 'portfolio-details' )) ) {
				// css.
				wp_enqueue_style( 'pb-portfolio-admin-style', PB_PORTFOLIO_PLUGIN_URL . 'assets/admin.css' , array(), '1.0' );

				// js.
				wp_enqueue_media();
				wp_enqueue_script( 'pb-portfolio-admin-script', PB_PORTFOLIO_PLUGIN_URL . 'assets/admin.js', array( 'jquery', 'jquery-ui-sortable' ), '1.0', true );
				$l10n = array(
					  'ajax_url'     => admin_url( 'admin-ajax.php' )
					, 'nonce'        => wp_create_nonce( '_pb_portfolio_nonce' )
					, 'post_id'      => $post->ID
					, 'createText'   => esc_html__( 'Create Featured Gallery', 'pb-portfolio' )
					, 'editText'     => esc_html__( 'Edit Featured Gallery', 'pb-portfolio' )
					, 'saveText'     => esc_html__( 'Save Featured Gallery', 'pb-portfolio' )
					, 'savingText'   => esc_html__( 'Saving...', 'pb-portfolio' )
				);
				wp_localize_script( 'pb-portfolio-admin-script', 'PB_Portfolio_Localize', $l10n );
			}
		}
		// end classes.
	}
}
new PB_Portfolio();
