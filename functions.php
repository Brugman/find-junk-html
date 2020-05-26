<?php

function fjh_get_default_needles()
{
    return [
        [
            'code' => '<h1',
            'tag' => 'h1',
            'desc' => 'The h1 tag is used by the theme for the page title.',
            'active' => true,
        ],
        [
            'code' => '<div',
            'tag' => 'div',
            'desc' => 'The div tag is rarely required, and often part of an old page builder. When in doubt, ask the theme developer.',
            'active' => true,
        ],
        [
            'code' => '<span',
            'tag' => 'span',
            'desc' => 'The span tag is rarely required, and often part of an old page builder. When in doubt, ask the theme developer.',
            'active' => true,
        ],
        [
            'code' => '<b',
            'tag' => 'b',
            'desc' => 'The b tag has been superseded by the strong tag. Used for strong importance. Not for its bold look.',
            'active' => true,
        ],
        [
            'code' => '<i',
            'tag' => 'i',
            'desc' => 'The i tag has been superseded by the em tag. Used for emphasis. Not for its italic look.',
            'active' => true,
        ],
        [
            'code' => ' style=',
            'tag' => 'style',
            'desc' => 'The style tag is almost always a relic from a previous website. The theme should take care of styling.',
            'active' => true,
        ],
    ];
}

function fjh_get_needles()
{
    return fjh_get_default_needles();
}

function fjh_get_active_needles()
{
    $needles = fjh_get_needles();

    $active_needles = array_filter( $needles, function ( $item ) {
        return $item['active'];
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
        $posts = $wpdb->get_results( "SELECT ID FROM {$wpdb->prefix}posts WHERE ( post_content LIKE '%".$needle['code']."%' OR post_excerpt LIKE '%".$needle['code']."%' ) AND post_status = 'publish' AND post_type != 'revision'" );

        if ( !empty( $posts ) )
            foreach ( $posts as $post )
                $junk_posts[ $post->ID ][] = $needle['tag'];

        $postmetas = $wpdb->get_results( "SELECT post_id FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->prefix}posts p ON pm.post_id = p.ID WHERE pm.meta_value LIKE '%".$needle['code']."%' AND p.post_status = 'publish' AND p.post_type != 'revision'" );

        if ( !empty( $postmetas ) )
            foreach ( $postmetas as $postmeta )
                $junk_posts[ $postmeta->post_id ][] = $needle['tag'];
    }

    krsort( $junk_posts );

    return $junk_posts;
}

function fjh_admin_page()
{
    if ( $_GET['fjh-task'] == 'results' )
    {
        admin_page_results();
        return;
    }

    admin_page_settings();
}

function admin_page_settings()
{
?>
<div class="wrap fjh-wrapper">

    <h2><?php _e( 'Find Junk HTML', 'find-junk-html' ); ?></h2>

<?php

$needles = fjh_get_default_needles();

if ( !empty( $needles ) )
{
?>
    <div class="settings">
<?php

    foreach ( $needles as $needle )
    {
        $checked = !$needle['active'] ?: 'checked';
?>
        <div class="checkbox">
            <label for="fjh-needle-<?=$needle['tag'];?>">
                <input type="checkbox" name="fjh-needle-<?=$needle['tag'];?>" id="fjh-needle-<?=$needle['tag'];?>" value="replaceme" <?=$checked;?>>
                <?=$needle['tag'];?>
            </label>
        </div>
<?php
    }

?>
    </div>
<?php
}

?>

    <div class="fjh-buttons">
        <a href="tools.php?page=find-junk-html" class="button button-secondary">Save settings</a>
        <a href="tools.php?page=find-junk-html&fjh-task=results" class="button button-primary">Let's find junk!</a>
    </div>

</div><!-- wrap -->
<style>
.fjh-wrapper .settings { margin-top: 15px; }
.fjh-wrapper .fjh-buttons { margin-top: 30px; font-size: 0; }
.fjh-wrapper .fjh-buttons .button { margin-right: 15px; }
</style>
<?php
}

function admin_page_results()
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
                <td><?php _e( 'Junk', 'find-junk-html' ); ?></td>
            </tr>
        </thead>
        <tbody>
<?php foreach ( $junk_posts as $post_id => $tags ): ?>
            <tr>
                <td><?=$post_id;?></td>
                <td><?=get_post_type_object( get_post_type( $post_id ) )->labels->singular_name;?></td>
                <td><a href="<?=get_edit_post_link( $post_id );?>" title="<?php _e( 'Edit this post', 'find-junk-html' ); ?>"><?=get_the_title( $post_id );?></a></td>
                <td><?=implode( ' + ', $tags );?></td>
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

