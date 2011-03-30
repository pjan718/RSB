<?php
if ( !defined( 'ABSPATH' ) && !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
exit();
} else {
delete_option( 'post-styling-initial' );
delete_option( 'jd-post-styling-screen' );
delete_option( 'jd-post-styling-mobile' );
delete_option( 'jd-post-styling-print' );
delete_option( 'jd-post-styling-default' );
delete_option( 'jd-post-styling-boxsize' );
delete_option( 'wp_post_styling_version' );
}
?>