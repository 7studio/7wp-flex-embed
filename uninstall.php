<?php

// If uninstall.php is not called by WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die( 'Something went wrong.' );
}



require_once SWP_FE_DIR . '/includes/functions.php';


swp_fe_remove_oembed_cache();
