<?php

function fjh_get_needles()
{
    return [
        [
            'code' => 'h1',
            'tag' => 'h1',
            'desc' => 'lorem',
            'active' => true,
        ],
        [
            'code' => 'h2',
            'tag' => 'h2',
            'desc' => 'lorem',
            'active' => true,
        ],
        [
            'code' => 'h3',
            'tag' => 'h3',
            'desc' => 'lorem',
            'active' => true,
        ],
        [
            'code' => 'h4',
            'tag' => 'h4',
            'desc' => 'lorem',
            'active' => true,
        ],
        [
            'code' => 'div',
            'tag' => 'div',
            'desc' => 'lorem',
            'active' => true,
        ],
        [
            'code' => 'span',
            'tag' => 'span',
            'desc' => 'lorem',
            'active' => true,
        ],
        [
            'code' => 'b',
            'tag' => 'b',
            'desc' => 'lorem',
            'active' => true,
        ],
        [
            'code' => 'i',
            'tag' => 'i',
            'desc' => 'lorem',
            'active' => true,
        ],
        [
            'code' => ' style=',
            'tag' => 'style',
            'desc' => 'lorem',
            'active' => true,
        ],
    ];
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

    $args = "";
    $args .= "SELECT ID FROM {$wpdb->prefix}posts WHERE ( 0 = 1 ";
    foreach ( $needles as $needle )
        $args .= "OR post_content LIKE '%<".$needle['code']."%' OR post_excerpt LIKE '%<".$needle['code']."%' ";
    $args .= ") AND post_status = 'publish' AND post_type != 'revision'";

    $posts = $wpdb->get_results( $args );

    $args = "";
    $args .= "SELECT post_id FROM {$wpdb->prefix}postmeta pm ";
    $args .= "INNER JOIN {$wpdb->prefix}posts p ON pm.post_id = p.ID ";
    $args .= "WHERE ( 0 = 1 ";
    foreach ( $needles as $needle )
        $args .= "OR pm.meta_value LIKE '%<".$needle['code']."%' ";
    $args .= ") AND p.post_status = 'publish' AND p.post_type != 'revision'";

    $postmetas = $wpdb->get_results( $args );

    $junk_posts = [];

    if ( !empty( $posts ) )
        foreach ( $posts as $post )
            $junk_posts[] = $post->ID;

    if ( !empty( $postmetas ) )
        foreach ( $postmetas as $postmeta )
            $junk_posts[] = $postmeta->post_id;

    $junk_posts = array_unique( $junk_posts );

    rsort( $junk_posts );

    return $junk_posts;
}

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

