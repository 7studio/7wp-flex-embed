<?php

/**
 * Removes the enqueued script `wp-embed`.
 *
 * This JavaScript is automaticaly added by WordPress all the time
 * to work with other WordPress inline HTML embeds.
 */
function swp_fe_dequeue_wpembed_script() {
    remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}
add_action( 'init', 'swp_fe_dequeue_wpembed_script' );

/**
 * Loads the `wp-embed` script only when needed.
 *
 * It can be needed because `[embed]` is found inside the post content or
 * because you have run manually `$wp_embed->run_shortcode`.
 *
 * You could follow this way/idea to lazyload the Twitter or Facebook
 * scripts for their widgets ;)
 *
 * @param mixed  $html    The cached HTML result, stored in post meta.
 * @param string $url     The attempted embed URL.
 * @param array  $attr    An array of shortcode attributes.
 * @param int    $post_ID Post ID.
 * @return string|false The embed HTML on success, otherwise the original URL.
 *                      `->maybe_make_link()` can return false on failure.
 */
function swp_fe_enqueue_wpembed_script( $html, $url, $attr, $post_ID ) {
    if ( ! is_admin() ) {
        unset( $attr['discover'] );
        $key_suffix = md5( $url . serialize( $attr ) );

        $_oembed_data = get_post_meta( $post_ID, '_oembed_data_' . $key_suffix, true );

        if ( $_oembed_data && $_oembed_data->provider_name == 'WordPress' ) {
            wp_enqueue_script( 'wp-embed' );
        }
    }

    return $html;
}
add_filter( 'embed_oembed_html', 'swp_fe_enqueue_wpembed_script', 10, 4 );
