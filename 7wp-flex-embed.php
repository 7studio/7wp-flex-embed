<?php

/**
 * Plugin Name:       7wp Flex Embed
 * Plugin URI:        https://github.com/7studio/7wp-flex-embed/
 * Description:       Makes embeds responsive and other things.
 * Version:           1.0.0
 * Author:            Xavier Zalawa
 * Author URI:        http://7studio.fr/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       swp-fe
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die( 'Something went wrong.' );
}

define( 'SWP_FE_HANDLE', '7wp-flex-embed' );
define( 'SWP_FE_VERSION', '1.0.0' );

define( 'SWP_FE_BASENAME', plugin_basename( __FILE__ ) );
define( 'SWP_FE_URL', plugins_url( '', __FILE__ ) );
define( 'SWP_FE_DIR', dirname( __FILE__ ) );

$swp_fe_allowed_providers = array( 'Twitter', 'Facebook', 'YouTube', 'Dailymotion', 'Vimeo' );

$swp_fe_provider_endpoints = array(
    'Amazon'       => array(
        'https://read.amazon.com/kp/api/oembed',
        'https://read.amazon.co.uk/kp/api/oembed',
        'https://read.amazon.com.au/kp/api/oembed',
        'https://read.amazon.cn/kp/api/oembed',
        'https://read.amazon.in/kp/api/oembe'
    ),
    'Animoto'      => array( 'https://animoto.com/oembeds/create' ),
    'Cloudup'      => array( 'https://cloudup.com/oembed' ),
    'CollegeHumor' => array( 'https://www.collegehumor.com/oembed.{format}' ),
    'Dailymotion'  => array( 'https://www.dailymotion.com/services/oembed' ),
    'Facebook'     => array(
        'https://www.facebook.com/plugins/post/oembed.json/',
        'https://www.facebook.com/plugins/video/oembed.json/'
    ),
    'Flickr'       => array( 'http://www.flickr.com/services/oembed/' ),
    'Funny or Die' => array( 'http://www.funnyordie.com/oembed' ),
    'Hulu'         => array( 'http://www.hulu.com/api/oembed.{format}' ),
    'Imgur'        => array( 'https://api.imgur.com/oembed' ),
    'Instagram'    => array( 'https://api.instagram.com/oembed' ),
    'Issuu'        => array( 'https://issuu.com/oembed_wp' ),
    'Kickstarter'  => array( 'https://www.kickstarter.com/services/oembed' ),
    'Meetup.com'   => array( 'https://api.meetup.com/oembed' ),
    'Mixcloud'     => array( 'https://www.mixcloud.com/oembed' ),
    'Photobucket'  => array( 'http://api.photobucket.com/oembed' ),
    'Polldaddy'    => array( 'https://polldaddy.com/oembed/' ),
    'Reddit'       => array( 'https://www.reddit.com/oembed' ),
    'ReverbNation' => array( 'https://www.reverbnation.com/oembed' ),
    'Screencast'   => array( 'https://api.screencast.com/external/oembed' ),
    'Scribd'       => array( 'https://www.scribd.com/services/oembed' ),
    'Slideshare'   => array( 'https://www.slideshare.net/api/oembed/2' ),
    'SmugMug'      => array( 'https://api.smugmug.com/services/oembed/' ),
    'Someecards'   => array( 'https://www.someecards.com/v2/oembed/' ),
    'SoundCloud'   => array( 'https://soundcloud.com/oembed' ),
    'Speaker Deck' => array( 'https://speakerdeck.com/oembed.{format}' ),
    'Spotify'      => array( 'https://embed.spotify.com/oembed/' ),
    'TED'          => array( 'https://www.ted.com/services/v1/oembed.{format}' ),
    'Tumblr'       => array( 'https://www.tumblr.com/oembed/1.0' ),
    'Twitter'      => array( 'https://publish.twitter.com/oembed' ),
    'VideoPress'   => array( 'https://public-api.wordpress.com/oembed/?for=' ),
    'Vimeo'        => array( 'https://vimeo.com/api/oembed.{format}' ),
    'WordPress.tv' => array( 'https://wordpress.tv/oembed/' ),
    'YouTube'      => array( 'https://www.youtube.com/oembed' )
);



require_once SWP_FE_DIR . '/includes/functions.php';



/**
 *
 */
register_activation_hook( __FILE__, 'swp_fe_remove_oembed_cache' );
register_deactivation_hook( __FILE__, 'swp_fe_remove_oembed_cache' );

/**
 * Init plugin.
 */
function swp_fe_init() {
	// Loads plugin translations.
	load_plugin_textdomain( 'swp-fe', false, dirname( SWP_FE_BASENAME ) . '/languages' );
}
add_action( 'plugins_loaded', 'swp_fe_init' );

/**
 * Processes all shortcodes at the same time (after `wpautop`).
 *
 * By default, WordPress registers the [embed] shortcode twice:
 * 1. With an empty callback hook which will remove the shortcode into the content
 *    when the filter `do_shortcode` will run AFTER `wpautop`.
 * 2. With a specific callback hook BEFORE `wpautop` which will remove all existing shortcodes,
 *    register the [embed] shortcode, call do_shortcode(), and then
 *    re-register the old shortcodes.
 *
 * It seems this is the expected behaviour that the [embed] shortcode needs
 * to be run earlier than other shortcodes but in this case all `<script>`
 * tags from oEmbed are wrapped into `<p>` tag and brake the specific HTML
 * markup for the RWD.
 *
 * @global WP_Embed $wp_embed
 */
function swp_fe_run_shortcode() {
    global $wp_embed;

    // Removes all filters`.
    remove_filter( 'the_content', 'do_shortcode', 11 );
    remove_filter( 'the_content', array( $wp_embed, 'run_shortcode' ), 8 );

    // Adds the [embed] shortcode filter between `wpautop` and `do_shortcode`.
    add_filter( 'the_content', array( $wp_embed, 'run_shortcode' ), 11 );

    /**
     * Restores the filter for all shortcodes after the [embed] one to do not
     * return an empty string for the [embed] shortcodes.
     */
    add_filter( 'the_content', 'do_shortcode', 12 );
}
add_action( 'init', 'swp_fe_run_shortcode' );

/**
 * Enqueues style needed by the new Embed HTML markup.
 *
 * Note:
 * The style is added into the WordPress admin for old versions (below 4.7)
 * which haven't the REST API and don't use an inline version
 * via the REST route `/oembed/1.0/proxy/`.
 */
function swp_fe_enqueue_style() {
    $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    if ( ! is_admin() && ! apply_filters( 'swp_fe_enqueue_style', true ) ) {
    	return;
    }

    wp_enqueue_style( SWP_FE_HANDLE, SWP_FE_URL . '/css/style' . $min . '.css', array(), SWP_FE_VERSION, 'all' );
}
add_action( 'wp_enqueue_scripts', 'swp_fe_enqueue_style' );
add_action( 'admin_enqueue_scripts', 'swp_fe_enqueue_style' );

/**
 * Registers an editor stylesheet for the theme.
 */
function swp_fe_enqueue_editor_style() {
	$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    add_editor_style( SWP_FE_URL . '/css/style' . $min . '.css' );
}
add_action( 'admin_init', 'swp_fe_enqueue_editor_style' );

/**
 * Enables the `media_live_embeds` option in TinyMCE to allow users
 * to see a live preview of `<iframe>` pasted into "Text" tab within the editable area,
 * rather than a placeholder image (1x1 GIF).
 * This means that users can play a cool track, such as Soundcloud, within the editor.
 *
 * @param $mceInit array An array with TinyMCE config.
 * @return array
 */
function swp_fe_enable_media_live_embeds( $mceInit ) {
	$mceInit['media_live_embeds'] = true;

	return $mceInit;
}
add_filter( 'tiny_mce_before_init', 'swp_fe_enable_media_live_embeds', PHP_INT_MAX );

/**
 * Because when you paste an URL, it does not handle like when you insert
 * a media from a URL. The second one, wraps the URL between the `[embed]`
 * shortcode.
 *
 * IMHO, this WordPress feature is not a good idea because it isn't finished.
 *
 * Note:
 * Gutenberg uses `$wp_embed->autoembed` to generate oEmbed contents in front…
 */
function swp_fe_remove_autoembed() {
	if ( ! apply_filters( 'swp_fe_remove_autoembed', true ) ) {
		return;
	}

	remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
}
add_action( 'init', 'swp_fe_remove_autoembed' );
//add_action( 'admin_init', 'swp_fe_remove_autoembed' );

function swp_fe_unregister_mce_view_embedUrl() {
	if ( ! apply_filters( 'swp_fe_remove_autoembed', true ) ) {
		return;
	}
	?>
	<script>
		( function( window, wp, $, undefined ) {
			if ( typeof wp !== 'undefined' && wp.mce ) {
				wp.mce.views.unregister( 'embedURL' );
			}
		} )( window, window.wp, window.jQuery );
	</script>
	<?php
}
add_action( 'admin_print_footer_scripts', 'swp_fe_unregister_mce_view_embedUrl' );

/**
 * Adjusts the height of embed parameters to be the same as the width.
 * The default height is 1.5 times the width, or 1000px, whichever is smaller.
 *
 * @param array  $size An array of embed width and height values
 *                     in pixels (in that order).
 * @param string $url  The URL that should be embedded.
 * @return array Default embed parameters.
 */
function swp_fe_embed_defaults( $size, $url ) {
	$size['height'] = $size['width'];

	return $size;
}
add_filter( 'embed_defaults', 'swp_fe_embed_defaults', 10, 2 );

/**
 * Sets the allowed maximum width for the oEmbed response
 * with the value of the content width based on the theme's design.
 * By default, maximum width is egal to 600.
 *
 * @param array $min_max_width Minimum and maximum widths for the oEmbed response.
 * @return array
 */
function swp_fe_oembed_min_max_width( $min_max_width ) {
	if ( ! empty( $GLOBALS['content_width'] ) ) {
		$min_max_width['max'] = (int) $GLOBALS['content_width'];
	}

	return $min_max_width;
}
add_filter( 'oembed_min_max_width', 'swp_fe_oembed_min_max_width' );

/**
 * Sets the maxwidth parameter for the oEmbed REST routes
 * with the value of the content width based on the theme's design.
 *
 * @param int $maxwidth Maximum allowed width. Default 600.
 * @return int
 */
function swp_fe_oembed_default_width( $maxwidth ) {
	if ( ! empty( $GLOBALS['content_width'] ) ) {
		$maxwidth = (int) $GLOBALS['content_width'];
	}

	return $maxwidth;
}
add_filter( 'oembed_default_width', 'swp_fe_oembed_default_width' );

/**
 * Changes the parameter priority order for the REST route `/oembed/1.0/proxy`.
 *
 * @see https://core.trac.wordpress.org/ticket/44704
 *
 * @param array           $order {
 *    An array of types to check, in order of priority.
 *
 *    @param string $type The type to check.
 * }
 * @param WP_REST_Request $this The request object.
 * @return array
 */
function swp_fe_change_rest_request_parameter_order( $order, $request ) {
	if ( 'GET' !== $request->get_method() ) {
		return $order;
	}

	// External embeds.
	if ( '/oembed/1.0/proxy' === $request->get_route() ) {
		$order = array( 'defaults', 'GET', 'URL' );
	}

	return $order;
}
add_filter( 'rest_request_parameter_order', 'swp_fe_change_rest_request_parameter_order', PHP_INT_MAX, 2 );

/**
 * Removes size arguments from the oEmbed URL to be fetched.
 * That will allow external services to give us their default widget sizes.
 *
 * There are two exceptions (YouTube and Dailymotion) which need these arguments
 * to don't return very small version of embeds.
 *
 * @param string $provider URL of the oEmbed provider.
 * @return string
 */
function swp_fe_oembed_remove_maxheight( $provider, $url, $args ) {
    if ( ! preg_match( '/http[s]?:\/\/(?:www\.)?(youtube|dailymotion)\.com/i', $provider ) ) {
	  $provider = remove_query_arg( array( 'maxwidth', 'maxheight' ), $provider );
    }

    /*
     * Remove the `maxheight` argument if it's equal to zero.
     *
     * @see https://core.trac.wordpress.org/ticket/44704
     */
    if ( isset( $args['maxheight'] ) && ! $args['maxheight'] ) {
    	$provider = add_query_arg( 'maxheight', $args['maxwidth'], $provider );
    }

	return $provider;
}
add_filter( 'oembed_fetch_url', 'swp_fe_oembed_remove_maxheight', 10, 3 );

/**
 * Removes the trailing slash from the URL which will be fetched.
 *
 * Many websites render webpage with the trailing slash but don't
 * handle it in their oEmbed API (e.g.: Giphy, Twitter, …).
 *
 * @param string $provider URL of the oEmbed provider.
 * @param string $url      URL of the content to be embedded.
 * @param array  $args     Optional arguments, usually passed from a shortcode.
 * @return string The URL to the oEmbed provider.
 */
function swp_fe_oembed_remove_url_trailingslash( $provider, $url, $args ) {
    $url = untrailingslashit( $url );

    $provider = remove_query_arg( 'url', $provider );
    $provider = add_query_arg( 'url', urlencode( $url ), $provider );

    return $provider;
}
add_filter( 'oembed_fetch_url', 'swp_fe_oembed_remove_url_trailingslash', 10, 3 );

/**
 * Overrides the returned oEmbed HTML
 *
 * Note that we don't hook `embed_html` because we can't access to the data
 * returned by the oEmbed provider.
 *
 * @param string $return The returned oEmbed HTML.
 * @param object $data   A data object result from an oEmbed provider.
 * @param string $url    The URL of the content to be embedded.
 * @return string HTML needed to embed.
 */
function swp_fe_oembed_dataparse( $return, $data, $url ) {
	if ( $return === false ) {
		return $return;
	}

	/*
	 * WordPress oEmbed is a particular case because the provider is
	 * the blogname of each site. To have a similar process between
	 * all providers, we will handle all sites powered by WordPress
	 * under an unique provider name: "WordPres".
	 *
	 * The height of the object is deliberately unset because of the
	 * good behaviour of the WordPress widget in RWD mode. It does not
	 * need to be wrapped into extra HTML markup.
	 */
	if ( mb_strpos( $return, 'wp-embedded-content' ) !== false ) {
		$data->provider_name = 'WordPress';
		$data->height = 0;
	}

    /*
     * For unknown reasons, Flickr and maybe other providers
     * return their resources as "photo" but include at the same time
     * extra entries required by other types (e.g.: `html`) :/
     * The documentation about oEmbed API (http://oembed.com)
     * is so simple that it should not be difficult to follow but it's not.
     *
     * In these cases, it's more interesting to include rich third party
     * than the static one.
     */
    if ( $data->type == 'photo'
        && isset( $data->html )
        && ( ! empty( $data->html ) && is_string( $data->html ) ) )
    {
        $return = $data->html;
    }

	return swp_fe_get_embed( $return, $data->width, $data->height );
}
add_filter( 'oembed_dataparse', 'swp_fe_oembed_dataparse', 10, 3 );

/**
 * Wraps `<iframe>` into a specific markup to be responsive :D
 * This hook runs before `run_shortcode()` and `wpautop()` to be sure
 * to handle only `<iframe>` into the content (not inserted by shortcodes).
 *
 * @param string $content Content of the current post.
 * @return string
 */
function swp_fe_embed_content( $content ) {
	preg_match_all( '/<iframe [^>]+>*.<\/iframe>/isU', $content, $matches );

	foreach ( $matches[0] as $iframe ) {
		$width = '100%';
		$height = 0;

		if ( preg_match( '/width=["\']([0-9]+%?)["\']/', $iframe, $matches ) != false ) {
			$width = $matches[1];
		}
		if ( preg_match( '/height=["\']([0-9]+)["\']/', $iframe, $matches ) != false ) {
			$height = (int) $matches[1];
		}

		$wpembed = swp_fe_get_embed( $iframe, $width, $height );

		$content = str_replace( $iframe, $wpembed, $content );
	}

	return $content;
}
add_filter( 'the_content', 'swp_fe_embed_content', 7 );

/**
 * Stores temporarily the data object result from the oEmbed provider
 * into a custom field. This meta will be used only to construct the
 * definitive custom field in another hook `oembed_result`.
 *
 * @param string $return The returned oEmbed HTML.
 * @param object $data   A data object result from an oEmbed provider.
 * @param string $url    The URL of the content to be embedded.
 * @return false|string
 */
function swp_fe_add_oembed_tmp_post_meta( $return, $data, $url ) {
	$post = get_post( swp_fe_get_post_ID() );

    if ( ! $post ) {
        return $return;
    }

    if ( $post && $return ) {
        update_post_meta( $post->ID, '_oembed_tmp_' . md5( $url ), $data );
    }

    return $return;
}
add_filter( 'oembed_dataparse', 'swp_fe_add_oembed_tmp_post_meta', 11, 3 );

/**
 * Stores/Caches the data object result from the oEmbed provider
 * into a custom field like `_oembed_` and `_oembed_time_`. This meta
 * could be used to display provider thumbnail, description and many
 * other things without any additional request.
 *
 * This meta will be deleted and updated at the same time
 * that the other meta about the embed.
 *
 * @param string $data The returned oEmbed HTML.
 * @param string $url  URL of the content to be embedded.
 * @param array  $args Optional arguments, usually passed from a shortcode.
 * @return false|string
 */
function swp_fe_add_oembed_data_post_meta( $data, $url, $args ) {
    $post = get_post( swp_fe_get_post_ID() );

    if ( ! $post ) {
        return $data;
    }

    $_oembed_tmp_data = get_post_meta( $post->ID, '_oembed_tmp_' . md5( $url ), true );
    if ( ! empty( $_oembed_tmp_data ) ) {
        unset( $args['discover'] );
        $key_suffix = md5( $url . serialize( $args ) );

        update_post_meta( $post->ID, '_oembed_data_' . $key_suffix, $_oembed_tmp_data );
        delete_post_meta( $post->ID, '_oembed_tmp_' . md5( $url ) );
    }

    return $data;
}
add_filter( 'oembed_result', 'swp_fe_add_oembed_data_post_meta', 10, 3 );

/**
 * Makes sure oEmbed REST Requests correctly apply the new behaviour (HTML markup)
 * for Embeds.
 *
 * @param  WP_HTTP_Response|WP_Error $response The REST Request response.
 * @param  WP_REST_Server            $handler  ResponseHandler instance (usually WP_REST_Server).
 * @param  WP_REST_Request           $request  Request used to generate the response.
 * @return WP_HTTP_Response|object|WP_Error    The REST Request response.
 */
function swp_fe_filter_oembed_result( $response, $handler, $request ) {
	if ( 'GET' !== $request->get_method() ) {
		return $response;
	}

	if ( is_wp_error( $response ) ) {
		/*
		 * Unset the `html` property to be sure that Gutenberg will render
		 * the right UI for wrong URL.
		 *
		 * @see https://github.com/WordPress/gutenberg/issues/8361
		 */
		if ( 'oembed_invalid_url' === $response->get_error_code() ) {
			unset( $response->html );
		}

		return $response;
	}

	if ( '/oembed/1.0/proxy' === $request->get_route() ) {
		// Make sure the HTML contains new HTML markup.
		if ( mb_strpos( $response->html, '<div class="swp-Embed"' ) === false ) {
			$response->html = swp_fe_oembed_dataparse( $response->html, $response, '' );
		}

		/*
		 * Include inline style for Gutenberg sandboxed iframe and
		 * classic editor media preview.
		 */
		$inline_style = swp_fe_get_inline_style();
		$response->html = $inline_style . $response->html;
	}

	return $response;
}
add_filter( 'rest_request_after_callbacks', 'swp_fe_filter_oembed_result', 11, 3 );

/**
 *
 */
function swp_fe_filter_oembed_providers() {
    global $swp_fe_provider_endpoints, $swp_fe_allowed_providers;

    $swp_fe_provider_endpoints = apply_filters( 'swp_fe_provider_endpoints', $swp_fe_provider_endpoints );
    $swp_fe_allowed_providers = apply_filters( 'swp_fe_allowed_providers', $swp_fe_allowed_providers );

    ksort( $swp_fe_provider_endpoints );
    ksort( $swp_fe_allowed_providers );
}
add_action( 'init', 'swp_fe_filter_oembed_providers', 0 );

/**
 * Enqueues script needed to disable Gutenberg Blocks for "blacklisted" oEmbed providers.
 *
 * @see https://github.com/WordPress/gutenberg/issues/4848#issuecomment-388174948
 */
function my_plugin_blacklist_blocks() {
    global $swp_fe_allowed_providers;

    $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    wp_enqueue_script( SWP_FE_HANDLE, SWP_FE_URL . '/js/script' . $min . '.js', array( 'wp-blocks', 'wp-edit-post' ), SWP_FE_VERSION );
    wp_localize_script( SWP_FE_HANDLE, 'SWP_FE', array( 'allowedProviders' => $swp_fe_allowed_providers ) );
}
add_action( 'enqueue_block_editor_assets', 'my_plugin_blacklist_blocks' );

/**
 * Reduces the list of whitelisted oEmbed providers.
 *
 * @param array $providers An array of arrays containing data about popular oEmbed providers.
 * @return array
 */
function swp_fe_reduce_whitelisted_oembed_providers( $providers  ) {
    global $swp_fe_provider_endpoints, $swp_fe_allowed_providers;

    $allowed_endpoints = array_intersect_key( $swp_fe_provider_endpoints, array_combine( $swp_fe_allowed_providers, $swp_fe_allowed_providers ) );
    $allowed_endpoints = array_reduce( $allowed_endpoints, 'array_merge', array() );
    $allowed_endpoints = array_map( 'untrailingslashit', $allowed_endpoints );

    return array_filter( $providers, function( $p ) use( $allowed_endpoints ) { return in_array( untrailingslashit( $p[0] ), $allowed_endpoints ); } );
}



require_once SWP_FE_DIR . '/includes/wp-embed.php';
require_once SWP_FE_DIR . '/includes/slideshare.php';
require_once SWP_FE_DIR . '/includes/soundcloud.php';
require_once SWP_FE_DIR . '/includes/giphy.php';
