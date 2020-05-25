<?php

/**
 * Plugin Name: Find Junk HTML
 * Description: Find the junk HTML that careless editors copy pasted into your beautiful site.
 * Version: 1.0.0
 * Plugin URI: https://github.com/Brugman/find-junk-html
 * Author: Tim Brugman
 * Author URI: https://timbr.dev/
 * Text Domain: find-junk-html
 */

if ( !defined( 'ABSPATH' ) )
    exit;

include 'functions.php';

/**
 * Admin page: Register menu item.
 */

add_action( 'admin_menu', function () {
    add_management_page(
        'Find Junk HTML',
        'Find Junk HTML',
        'manage_options',
        'find-junk-html',
        'fjh_admin_page'
    );
});

/**
 * Add settings link.
 */

add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), function ( $links ) {
    $settings_url = admin_url( 'tools.php?page=find-junk-html' );
    $settings_link = '<a href="'.$settings_url.'">'.__( 'Settings', 'find-junk-html' ).'</a>';
    array_unshift( $links, $settings_link );

    return $links;
});

