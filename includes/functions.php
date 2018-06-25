<?php
/**
 * Functions, filters, and actions for PB Portfolio.
 *
 * @package PB_Portfolio
 * @since 1.0
 */
/**
 * Retrieve portfolio gallery.
 *
 * @since 1.0
 */
function pb_portfolio_get_gallery($post_id,$args = array())
{
	$output  = '';
	$args    = wp_parse_args( $args, array('link' => 'none', 'columns' => '1', 'size' => 'full', 'before' => '', 'after' => '') );
	$display = get_post_meta( $post_id, '_pb_portfolio_display_gallery', true );

	// gallery shortcode.
	if( $display ) {
		$image_ids = get_post_meta( $post_id, '_pb_portfolio_images', true );
		if( !empty( $image_ids ) ) {
			$image_ids = esc_attr( rtrim( $image_ids, ',' ) );
			$output = do_shortcode( '[gallery ids="'. $image_ids .'" link="'. $args['link'] .'" columns="'. $args['columns'] .'" size="'. $args['size'] .'"]' );
		}
	}

	// return.
	if( !empty( $output ) ) {
		return $args['before'] . $output . $args['after'];
	}
}


/**
 * Retrieve portfolio video.
 *
 * @since 1.0
 */
function pb_portfolio_get_video($post_id, $args = array())
{
	$output  = '';
	$args    = wp_parse_args( $args, array( 'before' => '', 'after' => '' ) );
	$display = get_post_meta( $post_id, '_pb_portfolio_display_video', true );

	// video html.
	if( $display ) {
		$video = get_post_meta( $post_id, '_pb_portfolio_video', true );
		if( wp_oembed_get( $video ) ) {
			$output = wp_oembed_get( $video );
		}
		else {
			$output = html_entity_decode( esc_html($video) );
		}
	}

	// return.
	if( !empty( $output ) ) {
		return $args['before'] . $output . $args['after'];
	}
}


/**
 * Retrieve portfolio audio.
 *
 * @since 1.0
 */
function pb_portfolio_get_audio($post_id, $args = array())
{
	$output  = '';
	$args    = wp_parse_args( $args, array( 'before' => '', 'after' => '' ) );
	$display = get_post_meta( $post_id, '_pb_portfolio_display_audio', true );

	// audio html.
	if( $display ) {
		$audio = get_post_meta( $post_id, '_pb_portfolio_audio', true );
		if( wp_oembed_get( $audio ) ) {
			$output = wp_oembed_get( $audio );
		}
		else {
			$output = html_entity_decode( esc_html($video) );
		}
	}

	// return.
	if( !empty( $output ) ) {
		return $args['before'] . $output . $args['after'];
	}
}


/**
 * Display portfolio media for the portfolio content.
 *
 * @since 1.0
 */
function pb_portfolio_display_media($content)
{
	global $post;
	$post_id = $post->ID;
	if( 'portfolio' == get_post_type($post) && get_theme_support( 'portfolio-media' ) ) {
		// gallery.
		$content .= pb_portfolio_get_gallery( $post_id, array('before' => '<div class="project-gallery">', 'after' => '</div>') );

		// audio.
		$content .= pb_portfolio_get_audio( $post_id, array('before' => '<div class="project-audio">', 'after' => '</div>') );

		// video.
		$content .= pb_portfolio_get_video( $post_id, array('before' => '<div class="project-video">', 'after' => '</div>') );
	}

	return $content;
}
add_filter( 'the_content', 'pb_portfolio_display_media', 30 );


/**
 * Retrieve portfolio details.
 *
 * @since 1.0
 */
function pb_portfolio_get_details($post_id, $args = array())
{
	$output      = '';
	$args        = wp_parse_args( $args, array( 'before' => '', 'after' => '', 'item_before' => '<li>', 'item_after' => '</li>' ) );
	$details     = (array) get_post_meta( $post_id, '_pb_portfolio_details', true );
	$attr_before = '';
	$attr_after  = '';
	if( !empty( $details ) ) {
		if( '<li>' == $args['item_before'] && '</li>' == $args['item_after'] ) {
			$attr_before = '<ul>';
			$attr_after  = '</ul>';
		}
		$output .= $attr_before;

		// items.
		foreach( $details as $key => $value ) {
			$value = wp_parse_args( (array) $value, array( 'name' => '', 'label' => '', 'url' => '' ) );
			if( !empty( $value['name'] ) && !empty( $value['label'] ) ) {
				$output .= $args['item_before'];
				if( !empty( $value['url'] ) ) {
					$output .= sprintf('<span class="sub-name">%1$s:</span> <span class="sub-label"><a href="%3$s" rel="external nofollow" target="_blank">%2$s</a></span>'
						, esc_attr( $value['name'] )
						, esc_attr( $value['label'] )
						, esc_url( $value['url'] )
					);

				} else {
					$output .= sprintf('<span class="sub-name">%1$s:</span> <span class="sub-label">%2$s</span>'
						, esc_attr( $value['name'] )
						, esc_attr( $value['label'] )
					);
				}
				$output .= $args['item_after'];
			}
		}

		$output .= $attr_after;
	}

	// return.
	if( !empty( $output ) ) {
		return $args['before'] . $output . $args['after'];
	}
}


/**
 * Display portfolio details for the portfolio content.
 *
 * @since 1.0
 */
function pb_portfolio_display_details($content)
{
	global $post;
	$post_id = $post->ID;
	if( 'portfolio' == get_post_type($post) && get_theme_support( 'portfolio-details' ) ) {
		$args = array(
			  'before'      => '<div class="project-details">'
			, 'after'       => '</div>'
			, 'item_before' => '<li>'
			, 'item_after'  => '</li>'
		);
		$content .= pb_portfolio_get_details($post_id, $args);
	}
	return $content;
}
add_filter( 'the_content', 'pb_portfolio_display_details', 20 );

