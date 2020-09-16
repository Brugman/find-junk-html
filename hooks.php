<?php

/**
 * Register menu items.
 */

add_action( 'admin_menu', function () {
    add_management_page(
        'Find Junk HTML', // page title
        'Find Junk HTML', // menu title
        'manage_options', // capability
        'fjh', // menu slug
        'fjh_page_fjh', // function
        null // position
    );
});

add_action( 'admin_menu', function () {
    add_options_page(
        'Find Junk HTML', // page title
        'Find Junk HTML', // menu title
        'manage_options', // capability
        'fjh-options', // menu slug
        'fjh_page_options', // function
        null // position
    );
});

/**
 * Add settings link.
 */

add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), function ( $links ) {
    $settings_url = admin_url( 'options-general.php?page=find-junk-html' );
    $settings_link = '<a href="'.$settings_url.'">'.__( 'Settings', fjh_textdomain() ).'</a>';
    array_unshift( $links, $settings_link );

    return $links;
});

/**
 * Settings, sections and fields.
 */

add_action( 'admin_init', function () {
    register_setting(
        'fjh_options', // option group
        'fjh_options', // option name
        [] // args
    );
    // sections
    $section = 'needles';
    $page = 'fjh_needles';
    add_settings_section(
        $section, // section
        '', // title
        'fjh_needles_cb', // callback
        $page // page
    );
    // fields
    add_settings_field(
        'fjh_junk', // id
        __( 'Junk', fjh_textdomain() ), // title
        'fjh_junk_cb', // callback
        $page, // page
        $section, // section
        [] // args
    );
});

/**
 * Add admin header navigation.
 */

add_action( 'current_screen', function ( $screen ) {
    if ( strpos( $screen->id, '_page_fjh' ) !== false )
        add_action( 'in_admin_header', 'fjh_nav' );
});

