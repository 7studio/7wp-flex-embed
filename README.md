# 7wp Flex Embed

Transforms your static embeds into shiny responsive ones.

This plugin applies dynamically the [suitcss/components-flex-embed](https://github.com/suitcss/components-flex-embed) methodology to all your embeds (even `<iframe>` pasted manually into Text tab).
<br>Instead of applying a simple `iframe { max-width: 100%; }` in your CSS and having black borders to the top and bottom of your YouTube videos, this plugin keeps the right intrinsic ratio of the embeds.

Comparison of renders on iPhone 7 with a `375px` width:

<table style="table-layout:fixed">
<thead>
<tr><th>Default behaviour</th><th>`iframe { max-width: 100%; }`</th><th>7wp Flex Embed</th></tr>
</thead>
<tbody>
<tr><td align="center">:x:</td><td align="center">:heavy_check_mark:</td><td align="center">:heavy_check_mark::heavy_check_mark::heavy_check_mark:</td></tr>
<tr><td valign="top"><img src="http://7studio.fr/github/7wp-flex-embed/screenshot-1.png"></td><td valign="top"><img src="http://7studio.fr/github/7wp-flex-embed/screenshot-2.png"></td><td valign="top"><img src="http://7studio.fr/github/7wp-flex-embed/screenshot-3.png"></td></tr>
<tr>
  <td>Iframe is cropped or overflowed on the right</td>
  <td>Iframe is well displayed but only its width is reduced</td>
  <td>Iframe is perfectly displayed</td>
</tr>
</tbody>
</table>

Because the Embed part of WordPress is so interesting, the plugin doesn't stop there. It:
 
* turns off `autoembed` behaviour to have just `[embed]` shotcodes (<small style="font-size:.8125em">but you are able to reverse this choice</small>);
* sets the width of oEmbed requests according to your `$content_width`;
* keeps a raw copy of the oEmbed endpoint response into a post meta `_oembed_data_{cache_key}`;
* loads `wp_embed.js` script only when needed and not every time (perf matter);
* supports [`[soundcloud]`](https://en.support.wordpress.com/soundcloud-audio-player/) and [`[slideshare]`](https://en.support.wordpress.com/slideshare/) shortcodes introduced by WordPress.com;
* adds support to insert beautiful GIFs from Giphy (not in Gutenberg yet);
* allows to reduce the embed service list (applied to Gutenberg Embed Blocks as well).

## Usage

### Gutenberg

If you already use Gutenberg (not my case), you may turn on `autoembed` behaviour with this code `add_filter( 'swp_fe_remove_autoembed', '__return_false' )`.
It's a bit weird but Gutenberg uses this way to render `[embed]` shortcodes in front-end. It's a bad news.

### Select your needed embed services

By default, WordPress supports 34 embed services and Gutenberg offers the same number of Embed Blocks. IMHO, especially for Gutenberg, 
it's too much and most customers don't know half of them.
<br>This plugin chooses to reduce this long list to the five most common ones: Twitter, Facebook, YouTube, Dailymotion and Vimeo.
<br>But you are able to select your own:
```php
function my_theme_select_allowed_embed_providers( $allowed_providers ) {
    return array( 'Instagram', 'SoundCloud', 'WordPress.tv' ); 
}
add_filter( 'swp_fe_allowed_providers', 'my_theme_select_allowed_embed_providers' );
```

You can find the complete list there: [https://codex.wordpress.org/Embeds](https://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F)

### Deliver an inline version of the plugin assets

```php
add_filter( 'swp_fe_enqueue_style', '__return_false' );
add_action( 'wp_head', function() { echo swp_fe_get_inline_style(); } );
```

## Installation

1. Manually download the plugin and upload the extracted folder to the plugins directory (e.g.: `/wp-content/plugins/`)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Enjoy

## Attached issues

* https://core.trac.wordpress.org/ticket/44704
* https://core.trac.wordpress.org/ticket/44705
* https://github.com/WordPress/gutenberg/issues/8361
* https://github.com/WordPress/gutenberg/issues/8360

## Changelog

### 1.0.0 (August 9, 2018)
* Initial Release.
