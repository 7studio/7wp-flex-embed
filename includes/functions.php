<?php

/**
 * Retrieves the ID of the current Post.
 *
 * @return int
 */
function swp_fe_get_post_ID() {
	$post_ID = null;

	if ( isset( $GLOBALS['post'] ) ) {
		$post_ID = $GLOBALS['post']->ID;
	} elseif ( isset( $_GET['post'] ) ) {
		$post_ID = (int) $_GET['post'];
	} elseif ( isset( $_POST['post_ID'] ) ) {
		$post_ID = (int) $_POST['post_ID'];
	}

	return $post_ID;
}

/**
 * Retrieves the style needed by the new Embed HTML markup wrapped
 * into an HTML `<style>` element.
 *
 * @return string
 */
function swp_fe_get_inline_style() {
	$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$file_path = SWP_FE_DIR . '/css/style' . $min . '.css';
	$ouptut = '';

	if ( file_exists( $file_path ) && ($style = file_get_contents( $file_path )) !== false ) {
		$ouptut = '<style>' . $style . '</style>';
	}

	return $ouptut;
}

/**
 * Builds the Embed shortcode output.
 *
 * @param string     $html   …
 * @param int|string $width  …
 * @param int        $height …
 * @return string HTML content to display embed.
 */
function swp_fe_get_embed( $html, $width, $height ) {
	global $post_type;

	/**
	 * Filters the markup of an embed shortcode before it is generated.
	 *
	 * Passing a non-empty value will short-circuit thistle_get_embed(),
	 * returning that value instead.
	 */
	$output = apply_filters( 'pre_swp_fe_get_embed', '', $html, $width, $height );
	if ( ! empty( $output ) ) {
		return $output;
	}

	// Because soundcloud returns `width: '100%'`
	if ( $width === '100%' ) {
		$ratio = 0;
		$max_width = $width;
	} else {
		$ratio = round( ((100 * (int) $height) / (int) $width), 4 );
		$max_width = $width . 'px';
	}

	if ( $ratio ) {
        $thistle_gcd = function( $a, $b ) use ( &$thistle_gcd ) { return ( $a % $b ) ? $thistle_gcd( $b, $a % $b ) : $b; };

		$gcd = function_exists( 'gmp_gcd' ) ? gmp_intval( gmp_gcd( $width, $height ) ) : $thistle_gcd( $width, $height );
		$aspect_ratio = ($width / $gcd) . ":" . ($height / $gcd);

		$output  = '<div class="swp-Embed" embed-aspectRatio="' . $aspect_ratio . '" style="width:' . $max_width . '">';
		$output .= '<div class="swp-Embed-ratio" style="padding-bottom:' . $ratio . '%"></div>';
	} else {
		$output  = '<div class="swp-Embed" style="width:' . $max_width . '">';
	}
	$output .= '<div class="swp-Embed-content">' . $html . '</div>';
	$output .= '</div>';

	return $output;
}

/**
 *
 *
 */
function swp_fe_remove_oembed_cache() {
	global $wpdb;

	$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '_oembed_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%_oembed_%'" );
}
