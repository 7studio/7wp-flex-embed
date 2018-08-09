<?php

/**
 * Builds the SlideShare shortcode output.
 *
 * @global WP_Embed $wp_embed
 *
 * @param array  $atts {
 *     Attributes of the SlideShare shortcode.
 *
 *     @type string $id     ID of the slideshow to be fetched.
 *     @type string $doc    …
 *     @type int    $w      Width of the embed media.
 *     @type string $h      Height of the embed media
 * }
 * @return string HTML content to display the SlideShare embed.
 */
function swp_fe_slideshare_shortcode( $atts ) {
	/**
	 * Transforms the stupid SlideShare shortcode parameter
	 * into an associative array of attributes
	 * as recommended by the Shortcode API.
	 */
	$str = '';

	if ( is_array( $atts ) ) {
		foreach ( array_keys( $atts ) as $key ) {
			if ( ! is_numeric( $key ) ) {
				$str = $key . '=' . $atts[$key];
			}
		}
	}

	parse_str( html_entity_decode( $str ), $atts );

	/**
	 * Filters the default SlideShare shortcode output.
	 *
	 * If the filtered output isn't empty, it will be used instead of generating
	 * the default SlideShare embed template.
	 *
	 * @param string $output The SlideShare embed output. Default empty.
	 * @param array  $attr   Attributes of the SlideShare shortcode.
	 */
	$output = apply_filters( 'swp_fe_slideshare_shortcode', '', $atts );
	if ( $output != '' ) {
		return $output;
	}

	$atts = shortcode_atts( array(
		'id'  => '',
		'doc' => '',
		'w'   => '',
		'h'   => ''
	), $atts, 'slideshare' );

	if ( empty( $atts ) || ! isset( $atts['id'] ) || empty( $atts['id'] ) ) {
		return '';
	}

	// Uses WP_Embed to get the HTML
	$attr = array(
		'width'  => $atts['w'],
		'height' => $atts['h']
	);
	$attr = array_filter( $attr );

	$url = 'https://www.slideshare.net/slideshow/embed_code/' . $atts['id'];

	return $GLOBALS['wp_embed']->shortcode( $attr, $url );
}
add_shortcode( 'slideshare', 'swp_fe_slideshare_shortcode');