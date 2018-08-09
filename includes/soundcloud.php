<?php

/**
 * Builds the SoundCloud shortcode output.
 *
 * @param array  $atts {
 *     Attributes of the SoundCloud shortcode.
 *
 *     @type string $url    Soundcloud URL for a track, set, group, user.
 *     @type string $params Options for the Soundcloud player widget.
 *     @type int    $width  Width of the embed media.
 *     @type string $height Height of the embed media
 *     @type string $iframe …
 * }
 * @return string HTML content to display the SoundCloud embed.
 */
function swp_fe_soundcloud_shortcode( $atts ) {
	/**
	 * Filters the default SoundCloud shortcode output.
	 *
	 * If the filtered output isn't empty, it will be used instead of generating
	 * the default SoundCloud embed template.
	 *
	 * @param string $output The SoundCloud embed output. Default empty.
	 * @param array  $attr   Attributes of the SoundCloud shortcode.
	 */
	$output = apply_filters( 'swp_fe_soundcloud_shortcode', '', $atts );
	if ( $output != '' ) {
		return $output;
	}

	$atts = shortcode_atts( array(
		'url'	  => '',
		'params'  => '',
		'width'	  => '',
		'height'  => '',
		'iframe'  => '',
	), $atts, 'soundcloud' );

	parse_str( html_entity_decode( $atts['params'] ), $atts['params'] );

	$atts['params']['url'] = $atts['url'];

	// Build URL
	$url = 'https://w.soundcloud.com/player/?' . http_build_query( $atts['params'] );

	return sprintf( '<iframe width="%s" height="%s" scrolling="no" frameborder="no" src="%s"></iframe>', $atts['width'], $atts['height'], $url );
}
add_shortcode( 'soundcloud', 'swp_fe_soundcloud_shortcode');