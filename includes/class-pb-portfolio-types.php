<?php
/**
 * @package PB_Portfolio
 * @since 1.0
 */
// If this file is called directly, bail.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// The PB_Portfolio_Types classes.
if( ! class_exists('PB_Portfolio_Types') ) {
	class PB_Portfolio_Types{
		/**
		 * Constructor.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function __construct() {

			// Activation Hook.
			register_activation_hook( PB_PORTFOLIO_PLUGIN_FILE, array( $this, 'activation_hook') );

			// Register Portfolio post type.
			add_action( 'init', array( $this, 'portfolio_init') );

			// Adds custom column to admin screen.
			add_filter( 'manage_edit-portfolio_columns', array( $this, 'add_custom_column'), 10, 1 );

			// Display custom column to admin screen.
			add_action( 'manage_posts_custom_column', array( $this, 'display_custom_column'), 10, 1 );
		}

		/**
		 * Activation Hook
		 *
		 * @since 1.0
		 * @access public
		 */
		public function activation_hook() {
			$this->portfolio_init();
			flush_rewrite_rules();
		}

		/**
		 * Register Portfolio post type.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function portfolio_init() {
			// Portfolio post type.
			$portfolio_label = array(
				  'name'                => esc_html__( 'Portfolio', 'pb-portfolio' )
				, 'singular_name'       => esc_html__( 'Portfolio', 'pb-portfolio' )
				, 'add_new'             => esc_html_x( 'Add New', 'Add new text', 'pb-portfolio' )
				, 'add_new_item'        => esc_html__( 'Add New', 'pb-portfolio' )
				, 'edit_item'           => esc_html__( 'Edit Portfolio', 'pb-portfolio' )
				, 'new_item'            => esc_html__( 'New Portfolio', 'pb-portfolio' )
				, 'view_item'           => esc_html__( 'View Portfolio', 'pb-portfolio' )
				, 'search_items'        => esc_html__( 'Search Portfolio', 'pb-portfolio' )
				, 'not_found'           => esc_html__( 'No Portfolio found', 'pb-portfolio' )
				, 'not_found_in_trash'  => esc_html__( 'No Portfolio found in Trash', 'pb-portfolio' )
				, 'parent_item_colon'   => esc_html__( 'Parent Portfolio:', 'pb-portfolio' )
				, 'menu_name'           => esc_html__( 'Portfolio', 'pb-portfolio' )
			);
		
			$portfolio_args = array(
				  'labels'          => $portfolio_label
				, 'public'          => true
				, 'menu_position'   => null
				, 'has_archive'     => true
				, 'menu_icon'       => 'dashicons-portfolio'
				, 'capability_type' => 'post'
				, 'rewrite'         => array('slug' => 'portfolio')
				, 'supports'        => apply_filters( 'pb_portfolio_post_type_supports', array( 'title', 'editor', 'thumbnail' ) )
			);
		
			register_post_type( 'portfolio', apply_filters( 'pb_portfolio_post_type_args', $portfolio_args ) );

			// Portfolio Types taxonomy.
			$portfolio_types_label = array(
				  'name'                  => esc_html_x( 'Portfolio Types', 'Taxonomy plural name', 'pb-portfolio' )
				, 'singular_name'         => esc_html_x( 'Portfolio Type', 'Taxonomy singular name', 'pb-portfolio' )
				, 'search_items'          => esc_html__( 'Search Portfolio Types', 'pb-portfolio' )
				, 'popular_items'         => esc_html__( 'Popular Portfolio Types', 'pb-portfolio' )
				, 'all_items'             => esc_html__( 'All Portfolio Types', 'pb-portfolio' )
				, 'parent_item'           => esc_html__( 'Parent Portfolio Type', 'pb-portfolio' )
				, 'parent_item_colon'     => esc_html__( 'Parent Portfolio Type', 'pb-portfolio' )
				, 'edit_item'             => esc_html__( 'Edit Portfolio Type', 'pb-portfolio' )
				, 'update_item'           => esc_html__( 'Update Portfolio Type', 'pb-portfolio' )
				, 'add_new_item'          => esc_html__( 'Add New Portfolio Type', 'pb-portfolio' )
				, 'new_item_name'         => esc_html__( 'New Portfolio Type Name', 'pb-portfolio' )
				, 'add_or_remove_items'	  => esc_html__( 'Add or remove Portfolio Types', 'pb-portfolio' )
				, 'choose_from_most_used' => esc_html__( 'Choose from most used Portfolio Types', 'pb-portfolio' )
				, 'menu_name'             => esc_html__( 'Portfolio Types', 'pb-portfolio' )
			);
		
			$portfolio_types_args = array(
				  'labels'            => $portfolio_types_label
				, 'public'            => true
				, 'show_in_nav_menus' => true
				, 'show_admin_column' => true
				, 'hierarchical'      => true
				, 'show_tagcloud'     => false
				, 'show_ui'           => true
				, 'rewrite'           => array( 'slug' => 'portfolio-type' )
				, 'query_var'         => true
			);
		
			register_taxonomy( 'portfolio-type', array( 'portfolio' ), $portfolio_types_args );

		}

		/**
		 * Adds custom column to admin screen.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function add_custom_column($columns) {
			$custom_column = array(
				'featured_image' => esc_html__( 'Featured Image', 'pb-portfolio' )
			);
			$columns = array_slice( $columns, 0, 2, true ) + $custom_column + array_slice( $columns, 1, NULL, true );
			return $columns;
		}
		
		/**
		 * Display custom column to admin screen.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function display_custom_column( $column ) {
			global $post;
			switch ( $column ) {
				case 'featured_image':
					echo get_the_post_thumbnail( $post->ID, array(32, 32) );
				break;
			}
		}

		// end classes.
	}
}
