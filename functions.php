<?php

/**
 * Functions.
 */

function fjh_textdomain()
{
    return 'find-junk-html';
}

function fjh_get_all_needles()
{
    $needles = [
        [
            'code' => '<h1',
            'tag' => 'h1',
            'desc' => 'The h1 tag is used by the theme for the page title.',
        ],
        [
            'code' => '<div',
            'tag' => 'div',
            'desc' => 'The div tag is rarely required, and often part of an old page builder. When in doubt, ask the theme developer.',
        ],
        [
            'code' => '<span',
            'tag' => 'span',
            'desc' => 'The span tag is rarely required, and often part of an old page builder. When in doubt, ask the theme developer.',
        ],
        [
            'code' => '<b',
            'tag' => 'b',
            'desc' => 'The b tag has been superseded by the strong tag. Used for strong importance. Not for its bold look.',
        ],
        [
            'code' => '<i',
            'tag' => 'i',
            'desc' => 'The i tag has been superseded by the em tag. Used for emphasis. Not for its italic look.',
        ],
        [
            'code' => ' style=',
            'tag' => 'style',
            'desc' => 'The style tag is almost always a relic from a previous website. The theme should take care of styling.',
        ],
    ];

    return apply_filters( 'fjh_needles', $needles );
}

function fjh_get_active_needles()
{
    $needles = fjh_get_all_needles();

    $junk = get_option( 'fjh_options' )['fjh_junk'] ?? [];

    $active_needles = array_filter( $needles, function ( $item ) use ( $junk ) {
        return in_array( $item['tag'], $junk );
    });

    return $active_needles;
}

function fjh_find_junk_posts()
{
    global $wpdb;

    $needles = fjh_get_active_needles();

    if ( empty( $needles ) )
        return [];

    $junk_posts = [];

    foreach ( $needles as $needle )
    {
        $posts = $wpdb->get_results( "SELECT ID FROM {$wpdb->prefix}posts WHERE ( post_content LIKE '%".$needle['code']."%' OR post_excerpt LIKE '%".$needle['code']."%' ) AND post_status = 'publish' AND post_type NOT IN ( 'revision', 'oembed_cache' )" );

        if ( !empty( $posts ) )
        {
            foreach ( $posts as $post )
            {
                if ( isset( $junk_posts[ $post->ID ] ) && in_array( $needle['tag'], $junk_posts[ $post->ID ] ) )
                    continue;

                $junk_posts[ $post->ID ][] = $needle['tag'];
            }
        }

        $postmetas = $wpdb->get_results( "SELECT post_id FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->prefix}posts p ON pm.post_id = p.ID WHERE pm.meta_value LIKE '%".$needle['code']."%' AND p.post_status = 'publish' AND p.post_type NOT IN ( 'revision', 'oembed_cache' )" );

        if ( !empty( $postmetas ) )
        {
            foreach ( $postmetas as $postmeta )
            {
                if ( isset( $junk_posts[ $postmeta->post_id ] ) && in_array( $needle['tag'], $junk_posts[ $postmeta->post_id ] ) )
                    continue;

                $junk_posts[ $postmeta->post_id ][] = $needle['tag'];
            }
        }
    }

    krsort( $junk_posts );

    return $junk_posts;
}

function fjh_nav()
{
    $pages = [
        [
            'link' => admin_url( 'options-general.php?page=fjh-options' ),
            'title' => __( 'Settings', fjh_textdomain() ),
        ],
        [
            'link' => admin_url( 'tools.php?page=fjh' ),
            'title' => __( 'Find Junk HTML', fjh_textdomain() ),
        ],
    ];
?>
<link rel="stylesheet" href="<?=plugins_url( 'find-junk-html.min.css', __FILE__ );?>" />
<div class="fjh-acf-admin-toolbar">
    <h2><i class="fjh-acf-tab-icon dashicons dashicons-trash"></i> Find Junk HTML</h2>
<?php
    foreach ( $pages as $page )
    {
        $is_active = strpos( $page['link'], $_SERVER['REQUEST_URI'] ) !== false ? 'is-active' : '';
?>
    <a class="fjh-acf-tab <?=$is_active;?>" href="<?=$page['link'];?>"><?=$page['title'];?></a>
<?php
    }
?>
</div>
<?php
}

/**
 * Page: FJH.
 */

function fjh_page_fjh()
{
    $junk_posts = fjh_find_junk_posts();
?>
<div class="wrap fjh-wrapper">

    <h1><?php _e( 'Find Junk HTML', fjh_textdomain() ); ?></h1>

<?php if ( !empty( $junk_posts ) ): ?>

    <table class="wp-list-table widefat fixed striped" style="width: auto; margin-top: 16px;">
        <thead>
            <tr>
                <td><?php _e( 'ID', fjh_textdomain() ); ?></td>
                <td><?php _e( 'Type', fjh_textdomain() ); ?></td>
                <td><?php _e( 'Title', fjh_textdomain() ); ?></td>
                <td><?php _e( 'Junk', fjh_textdomain() ); ?></td>
            </tr>
        </thead>
        <tbody>
<?php foreach ( $junk_posts as $post_id => $tags ): ?>
            <tr>
                <td><?=$post_id;?></td>
                <td><?=get_post_type_object( get_post_type( $post_id ) )->labels->singular_name;?></td>
                <td><a href="<?=get_edit_post_link( $post_id );?>" title="<?php _e( 'Edit this post', fjh_textdomain() ); ?>"><?=get_the_title( $post_id );?></a></td>
                <td><?=implode( ' ', $tags );?></td>
            </tr>
<?php endforeach; // $junk_posts ?>
        </tbody>
    </table>

<?php else: // $junk_posts is empty ?>

    <p><?php _e( 'No HTML junk was found in your posts. Nice!', fjh_textdomain() ); ?></p>

<?php endif; // $junk_posts ?>

</div><!-- wrap -->
<?php
}

/**
 * Page: Options.
 */

function fjh_page_options()
{
?>
<div class="wrap fjh-wrapper">

    <h1><?php _e( 'Settings', fjh_textdomain() ); ?></h1>

    <form method="post" action="options.php">
<?php

settings_fields('fjh_options');
do_settings_sections('fjh_needles');
submit_button();

?>
    </form>

</div><!-- wrap -->
<?php
}

/**
 * Callbacks.
 */

function fjh_needles_cb( $args )
{
    echo '<p>'.__( 'What do you consider junk?', fjh_textdomain() ).'</p>';
}

function fjh_junk_cb()
{
    echo fjh_form_field_checkbox_group( 'fjh_options', 'fjh_junk' );
}

/**
 * Form field HTML.
 */

function fjh_form_field_checkbox_group( $options, $field_name )
{
    if ( !$options || !$field_name )
        return;

    $needles = fjh_get_all_needles();

    if ( empty( $needles ) )
        return;

    $values = get_option( $options )[ $field_name ] ?? [];

    foreach ( $needles as $needle )
    {
        $checked = in_array( $needle['tag'], $values ) ? 'checked' : '';
?>
<div class="checkbox">
    <label for="label-<?=$needle['tag'];?>" title="<?=$needle['desc'];?>">
        <input type="checkbox" name="<?=$options;?>[<?=$field_name;?>][]" id="label-<?=$needle['tag'];?>" value="<?=$needle['tag'];?>" <?=$checked;?>>
        <?=$needle['tag'];?>
    </label>
</div>
<?php
    }
}

