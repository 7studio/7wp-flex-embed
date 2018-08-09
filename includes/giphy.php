<?php

/**
 * Adds support for Giphy oEmbed.
 *
 * @link https://github.com/iamcal/oembed/issues/224
 * @link https://github.com/Giphy/GiphyAPI/issues/76#issuecomment-220087715
 * @link https://giphy.com/posts/how-to-embed-giphy-gifs-on-your-website
 */
function swp_fe_oembed_add_giphy_provider() {
    $endpoints = array(
        '#https?://media\.giphy\.com/.*/giphy\.gif#i' => 'https://giphy.com/services/oembed',
        '#https?://(www\.)?giphy\.com/gifs/.*#i'      => 'https://giphy.com/services/oembed',
        '#https?://gph\.is/.*#i'                      => 'https://giphy.com/services/oembed'
    );

    foreach ( $endpoints as $pattern => $endpoint ) {
        wp_oembed_add_provider( $pattern, $endpoint, true );
    }
}
add_action( 'init', 'swp_fe_oembed_add_giphy_provider' );

/**
 *
 * @param array $provider_endpoints An associative array containing providers title and oembed endpoints.
 * @return array
 */
function swp_fe_add_giphy_endpoint( $provider_endpoints ) {
    $provider_endpoints['Giphy'] = 'https://giphy.com/services/oembed';

    return $provider_endpoints;
}
add_filter( 'swp_fe_provider_endpoints', 'swp_fe_add_giphy_endpoint' );

/**
 * Overrides the returned oEmbed HTML for the GIPHY provider.
 *
 * By default, Giphy chooses to handle their GIFs as "photo" instead of
 * "rich" content through their oEmbed API. It's acceptable but it's also
 * a shame when you know that Giphy suggests to share their content via
 * an `<iframe>` on their website.
 *
 * This hook operates before the default Thistle one.
 *
 * @param string $return The returned oEmbed HTML.
 * @param object $data   A data object result from an oEmbed provider.
 * @param string $url    The URL of the content to be embedded.
 * @return string HTML needed to embed.
 */
function swp_fe_giphy_oembed_dataparse( $return, $data, $url ) {
    if ( $return === false ) {
        return $return;
    }

    if ( $data->provider_name == 'GIPHY'
        && preg_match( '/https?:\/\/media\.giphy\.com\/media\/(?<key>[^ \/]+)\/giphy\.gif/i', $data->url, $matches ) )
    {
        $key = $matches['key'];

        $return = <<<GIPHY
<iframe src="//giphy.com/embed/{$key}?html5=true"
    width="{$data->width}"
    height="{$data->height}"
    frameBorder="0"
    class="giphy-embed"
    allowFullScreen
>
</iframe>
<p>
<a href="{$data->url}">via GIPHY</a>
</p>
GIPHY;
    }

    return $return;
}
add_filter( 'oembed_dataparse', 'swp_fe_giphy_oembed_dataparse', 9, 3 );
