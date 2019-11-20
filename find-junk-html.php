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

/**
 * Find junk posts.
 */

function fjh_find_junk_posts()
{
    global $wpdb;
    $posts = $wpdb->get_results("
        SELECT ID
        FROM {$wpdb->prefix}posts
        WHERE ( post_content LIKE '%<div%' OR post_excerpt LIKE '%<div%' )
        AND post_status = 'publish'
        AND post_type != 'revision'
    ");
    $postmetas = $wpdb->get_results("
        SELECT post_id
        FROM {$wpdb->prefix}postmeta
        WHERE meta_value LIKE '%<div%'
    ");

    $junk_posts = [];

    if ( !empty( $posts ) )
        foreach ( $posts as $post )
            $junk_posts[] = $post->ID;

    if ( !empty( $postmetas ) )
        foreach ( $postmetas as $postmeta )
            $junk_posts[] = $postmeta->ID;

    return $junk_posts;
}

/**
 * Admin page: HTML.
 */

function fjh_admin_page()
{
    $junk_posts = fjh_find_junk_posts();
?>
<div class="wrap">

    <h2><?php _e( 'Find Junk HTML', 'find-junk-html' ); ?></h2>

<?php if ( !empty( $junk_posts ) ): ?>

    <table class="wp-list-table widefat fixed striped" style="width: auto; margin-top: 16px;">
        <thead>
            <tr>
                <td><?php _e( 'ID', 'find-junk-html' ); ?></td>
                <td><?php _e( 'Type', 'find-junk-html' ); ?></td>
                <td><?php _e( 'Title', 'find-junk-html' ); ?></td>
            </tr>
        </thead>
        <tbody>
<?php foreach ( $junk_posts as $junk_post ): ?>
            <tr>
                <td><?=$junk_post;?></td>
                <td><?=get_post_type_object( get_post_type( $junk_post ) )->labels->singular_name;?></td>
                <td><a href="<?=get_edit_post_link( $junk_post );?>" title="<?php _e( 'Edit this post', 'find-junk-html' ); ?>"><?=get_the_title( $junk_post );?></a></td>
            </tr>
<?php endforeach; // $junk_posts ?>
        </tbody>
    </table>

<?php else: // $junk_posts is empty ?>

    <p><?php _e( 'No HTML junk was found in your posts. Nice!', 'find-junk-html' ); ?></p>

<?php endif; // $junk_posts ?>

</div><!-- wrap -->
<?php
}

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

