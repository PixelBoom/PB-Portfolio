<?php
/**
 * Adds custom metaboxs to portfolio post.
 *
 * @package PB_Portfolio
 * @since 1.0
 */
// If this file is called directly, bail.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// The PB_Portfolio_Metaboxes classes.
if( ! class_exists('PB_Portfolio_Metaboxes') ) {
	class PB_Portfolio_Metaboxes {
		/**
		 * Constructor.
		 * Contains all hooks & actions needed.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function __construct() {
			// Adds & save meta boxes.
			add_action( 'add_meta_boxes', array( $this, 'adds' ) );
			add_action( 'save_post',      array( $this, 'save' ) );

			// Ajax callback.
			add_action( 'wp_ajax_pb_portfolio_ajax', array( $this, 'ajax_callback' ) );
		}

		
		/**
		 * Adds the meta box container.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function adds() {
			if( get_theme_support( 'portfolio-media' ) ) {
				add_meta_box( 'pb_portfolio_media',   esc_html__( 'Portfolio Project Media', 'pb-portfolio' ),   array( $this, 'media_callback' ),  array( 'portfolio' ), 'normal', 'high' );	
			}
			
			if( get_theme_support( 'portfolio-details' ) ) {
				add_meta_box( 'pb_portfolio_details', esc_html__( 'Portfolio Project Details', 'pb-portfolio' ), array( $this, 'detail_callback' ), array( 'portfolio' ), 'normal', 'high' );
			}
		}

		
		/**
		 * Save the meta when the post is saved.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function save( $post_id ) {
			// Verify that the nonce is valid.
			$nonce = isset( $_POST['_pb_portfolio_nonce'] ) ? (string) $_POST['_pb_portfolio_nonce'] : '';
			if( ! wp_verify_nonce( $nonce, '_pb_portfolio_nonce' ) ) {
				return $post_id;
			}

			// If this is an autosave, our form has not been submitted,
			// so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			// Check the user's permissions.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}

			// Sanitize the user input.
			$image_ids = isset( $_POST['_pb_portfolio_images'] )  ? sanitize_text_field( $_POST['_pb_portfolio_images'] ) : '';
			$video     = isset( $_POST['_pb_portfolio_video'] )   ? $_POST['_pb_portfolio_video']                         : '';
			$audio     = isset( $_POST['_pb_portfolio_audio'] )   ? $_POST['_pb_portfolio_audio']                         : '';
			if( ! current_user_can( 'unfiltered_html' ) ) {
				$video = wp_kses_post( $video );
				$audio = wp_kses_post( $audio );
			}

			$details   = isset( $_POST['_pb_portfolio_details'] ) ? (array) $_POST['_pb_portfolio_details']               : array();
			$updates   = array();
			foreach( $details as $key => $value ) {
				$value = wp_parse_args( (array) $value, array('name' => '', 'label' => '', 'url' => '') );
				if( !empty($value['name']) && !empty($value['label']) ) {
					$value['name']  = sanitize_text_field( $value['name'] );
					$value['label'] = sanitize_text_field( $value['label'] );
					$value['url']   = sanitize_url( $value['url'] );
					$updates[] = $value;
				}
			}

			// Update the meta field.
			if( isset($_POST['_pb_portfolio_display_gallery']) ) { update_post_meta( $post_id, '_pb_portfolio_display_gallery', '1' ); } else { delete_post_meta( $post_id, '_pb_portfolio_display_gallery' ); }
			if( isset($_POST['_pb_portfolio_display_video']) )   { update_post_meta( $post_id, '_pb_portfolio_display_video', '1' ); }   else { delete_post_meta( $post_id, '_pb_portfolio_display_video' ); }
			if( isset($_POST['_pb_portfolio_display_audio']) )   { update_post_meta( $post_id, '_pb_portfolio_display_audio', '1' ); }   else { delete_post_meta( $post_id, '_pb_portfolio_display_audio' ); }
			
			if( !empty($image_ids) ) { update_post_meta( $post_id, '_pb_portfolio_images', $image_ids ); } else { delete_post_meta( $post_id, '_pb_portfolio_images' ); }
			if( !empty($video) )     { update_post_meta( $post_id, '_pb_portfolio_video', $video ); }      else { delete_post_meta( $post_id, '_pb_portfolio_video' ); }
			if( !empty($audio) )     { update_post_meta( $post_id, '_pb_portfolio_audio', $audio ); }      else { delete_post_meta( $post_id, '_pb_portfolio_audio' ); }
			if( !empty($updates) )   { update_post_meta( $post_id, '_pb_portfolio_details', $updates ); }  else { delete_post_meta( $post_id, '_pb_portfolio_details' ); }
		}

		
		/**
		 * Render Meta Box content.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function media_callback( $post ) {
			$post_id = $post->ID;
			// Checked.
			$display_gallery = $this->get_meta( $post_id, '_pb_portfolio_display_gallery', false );
			$display_video   = $this->get_meta( $post_id, '_pb_portfolio_display_video', false );
			$display_audio   = $this->get_meta( $post_id, '_pb_portfolio_display_audio', false );

			// Gallery.
			$image_ids = $this->get_meta( $post_id, '_pb_portfolio_images', false );
			?>

			<div id="pb-portfolio-media" class="pb-portfolio-row">
				<div class="pb-portfolio-left-div">
					<p class="description"><?php esc_html_e( 'Select the media formats that should be displayed.', 'pb-portfolio' ); ?></p>
				</div>
				<!-- .pb-portfolio-left-div -->
				<div class="pb-portfolio-right-div">
					<ul class="pb-portfolio-checkboxes">
						<li>
							<input type="checkbox" name="_pb_portfolio_display_gallery" id="_pb_portfolio_display_gallery"<?php checked( 1, $display_gallery ); ?> data-display-id="pb-portfolio-gallery" />
							<label for="_pb_portfolio_display_gallery"><?php esc_html_e( 'Display Gallery', 'pb-portfolio' ); ?></label>					
						</li>
						<li>
							<input type="checkbox" name="_pb_portfolio_display_video" id="_pb_portfolio_display_video"<?php checked( 1, $display_video ); ?> data-display-id="pb-portfolio-video" />
							<label for="_pb_portfolio_display_video"><?php esc_html_e( 'Display Video', 'pb-portfolio' ); ?></label>					
						</li>
						<li>
							<input type="checkbox" name="_pb_portfolio_display_audio" id="_pb_portfolio_display_audio"<?php checked( 1, $display_audio ); ?> data-display-id="pb-portfolio-audio" />
							<label for="_pb_portfolio_display_audio"><?php esc_html_e( 'Display Audio', 'pb-portfolio' ); ?></label>					
						</li>
					</ul>
				</div>
				<!-- .pb-portfolio-right-div -->
			</div>
			<div class="clear"></div>
			<!-- #pb-portfolio-media -->

			<div id="pb-portfolio-gallery" class="pb-portfolio-row">
				<div class="pb-portfolio-left-div">
					<label for="_pb_portfolio_images"><?php esc_html_e( 'Gallery Images', 'pb-portfolio' ); ?></label>
				</div>
				<!-- .pb-portfolio-left-div -->
				<div class="pb-portfolio-right-div">
					<input type="hidden" id="_pb_portfolio_images" name="_pb_portfolio_images" value="<?php echo esc_attr( $image_ids ); ?>" />
					<button type="button" id="pb-portfolio-gallery-upload" class="button"><?php if( $image_ids ) { esc_html_e( 'Edit Gallery', 'pb-portfolio' ); } else { esc_html_e( 'Upload Images', 'pb-portfolio' ); } ?></button>
					<p class="description"><?php esc_html_e( 'Edit the gallery by clicking to upload or edit the gallery.', 'pb-portfolio' ); ?></p>
					<ul id="pb-portfolio-gallery-images">
						<?php
							if( $image_ids ) {
								$image_args = explode(',', $image_ids);
								$image_output = '';
								foreach( $image_args as $img ) {
									$image_output .= '<li>'. wp_get_attachment_image( $img, array(48, 48) ) .'</li>';
								}
								echo $image_output;
							}
						?>

					</ul>
				</div>
				<!-- .pb-portfolio-right-div -->
			</div>
			<div class="clear"></div>
			<!-- #pb-portfolio-gallery -->

			<div id="pb-portfolio-video" class="pb-portfolio-row">
				<div class="pb-portfolio-left-div">
					<label for="_pb_portfolio_video"><?php esc_html_e( 'Video', 'pb-portfolio' ); ?></label>
					<p class="description"><?php esc_html_e( 'Video URL (oEmbed) or Embed Code', 'pb-portfolio' ); ?></p>
				</div>
				<!-- .pb-portfolio-left-div -->
				<div class="pb-portfolio-right-div">
					<textarea name="_pb_portfolio_video" id="_pb_portfolio_video" rows="4" autocomplete="off"><?php $this->get_meta( $post_id, '_pb_portfolio_video', true ); ?></textarea>
				</div>
				<!-- .pb-portfolio-right-div -->
			</div>
			<div class="clear"></div>
			<!-- #pb-portfolio-video -->

			<div id="pb-portfolio-audio" class="pb-portfolio-row">
				<div class="pb-portfolio-left-div">
					<label for="_pb_portfolio_audio"><?php esc_html_e( 'Audio', 'pb-portfolio' ); ?></label>
					<p class="description"><?php esc_html_e( 'Audio URL (oEmbed) or Embed Code', 'pb-portfolio' ); ?></p>
				</div>
				<!-- .pb-portfolio-left-div -->
				<div class="pb-portfolio-right-div">
					<textarea name="_pb_portfolio_audio" id="_pb_portfolio_audio" rows="4" autocomplete="off"><?php $this->get_meta( $post_id, '_pb_portfolio_audio', true ); ?></textarea>
				</div>
				<!-- .pb-portfolio-right-div -->
			</div>
			<div class="clear"></div>
			<!-- #pb-portfolio-audio -->
			<?php
			wp_nonce_field( '_pb_portfolio_nonce', '_pb_portfolio_nonce', false, true );
		}

		
		/**
		 * Render Meta Box content.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function detail_callback( $post ) {
			$post_id = $post->ID;
			$number  = 1;
			$args    = get_post_meta( $post_id, '_pb_portfolio_details', true );
			$_args   = array();
			if( is_array($args) ) {
				$number = count($args);
				$_args  = $args;
			}
			?>
			<div id="pb-portfolio-details" class="pb-portfolio-row">
				<p class="description"><?php esc_html_e( 'Role, skill, client or other useful links... Required fields are marked *', 'pb-portfolio' ); ?></p>
				<table class="pb-portfolio-details-table widefat">
					<thead>
						<tr>
							<th class="pbpd-table-order"></th>
							<th class="pbpd-table-name" width="30%"><span><?php esc_html_e( 'Name *', 'pb-portfolio' ); ?></span></th>
							<th class="pbpd-table-label" width="30%"><span><?php esc_html_e( 'Label *', 'pb-portfolio' ); ?></span></th>
							<th class="pbpd-table-url" width="40%"><span><?php esc_html_e( 'External URL', 'pb-portfolio' ); ?></span></th>
							<th class="pbpd-table-remove"></th>
						</tr>
					</thead>
					<tbody>
						<?php
							$output = '';
							for ($i=0; $i < $number; $i++) { 

								$value_name  = isset($_args[$i]['name'])  ? esc_attr($_args[$i]['name'])  : '';
								$value_label = isset($_args[$i]['label']) ? esc_attr($_args[$i]['label']) : '';
								$value_url   = isset($_args[$i]['url'])   ? esc_url($_args[$i]['url'])    : '';

								$output .= '<tr>';
									$output .= '<td class="pbpd-table-order"></td>';
									$output .= '<td class="pbpd-table-name"><input type="text" name="_pb_portfolio_details['. $i .'][name]" value="'. $value_name .'" autocomplete="off" /></td>';
									$output .= '<td class="pbpd-table-label"><input type="text" name="_pb_portfolio_details['. $i .'][label]" value="'. $value_label .'" autocomplete="off" /></td>';
									$output .= '<td class="pbpd-table-url"><input type="text" name="_pb_portfolio_details['. $i .'][url]" value="'. $value_url .'" autocomplete="off" /></td>';
									$output .= '<td class="pbpd-table-remove"><a href="#" class="pb-portfolio-detail-remove-button"><span class="dashicons dashicons-no-alt"></span></a></td>';
								$output .= '</tr>';
							}
							echo $output;
						?>

					</tbody>
				</table>
				<!-- .pb-portfolio-details-table -->
				<div class="clear"></div>
				<p><button type="button" id="pb-portfolio-detail-add-button" class="button"><?php esc_html_e( 'Add New', 'pb-portfolio' ); ?></button></p>
			</div>
			<!-- #pb-portfolio-details -->

			<?php
		}

		
		/**
		 * Ajax Callback.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function ajax_callback( $post ) {
			// Verify that the nonce is valid.
			if( ! isset($_POST['nonce']) || ! isset( $_POST['ids']) ) {
				return;
			}
			if( ! wp_verify_nonce( $_POST['nonce'], '_pb_portfolio_nonce' ) ) {
				return;
			}

			// If this is an autosave, our form has not been submitted,
			// so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Check the user's permissions.
			if ( ! current_user_can( 'edit_posts' ) ) {
				return;
			}

			// Sanitize the user input.
			$image_ids  = sanitize_text_field( $_POST['ids'] );
			$image_ids  = rtrim($image_ids, ',');

			$image_args = explode(',', $image_ids);
			$output     = '';

			// Output the meta field.
			foreach( $image_args as $img ) {
				$output .= '<li>'. wp_get_attachment_image( $img, array(48, 48) ) .'</li>';
			}
			echo $output;

			die();
		}

		
		/**
		 * Retrieve meta value.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function get_meta( $post_id , $key = '', $echo = false ) {
			$meta = get_post_meta( $post_id, $key, true );
			$meta = maybe_unserialize( $meta );
			if( $echo ) {
				echo $meta;
			}
			else {
				return $meta;
			}
		}

	}
	// End Class.
}
